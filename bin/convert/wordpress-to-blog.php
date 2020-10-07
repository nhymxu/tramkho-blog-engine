<?php

use League\HTMLToMarkdown\HtmlConverter;

require 'vendor/autoload.php';

class ExportWordPress {

    private $db;
    private $sqlite;

    public function __construct() {
        $this->connect_db();
        $this->connect_sqlite();
    }

    private function connect_db(): void
    {
        $db_name = '========REPLACE_HERE========';
        $username = '========REPLACE_HERE========';
        $password = '========REPLACE_HERE========';

        try {
            $flags = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ];

            $this->db = new PDO('mysql:host=localhost;dbname='. $db_name, $username, $password, $flags);
            echo "Connected successfully" . PHP_EOL;
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage() . PHP_EOL;
        }
    }

    private function connect_sqlite(): void
    {
        $db_path = __DIR__ . '/database.sqlite';
        $create_table = false;

        if (!file_exists($db_path)) {
            $create_table = true;
        }

        try {
            $flags = [
                // Turn off persistent connections
                PDO::ATTR_PERSISTENT => false,
                // Enable exceptions
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                // Emulate prepared statements. Disable emulation of prepared statements, use REAL prepared statements instead
                PDO::ATTR_EMULATE_PREPARES => false, // mysql true
                // Set default fetch mode to array
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            $this->sqlite = new PDO('sqlite:' . $db_path, null, null, $flags);
            echo "Connected successfully" . PHP_EOL;
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage() . PHP_EOL;
        }

        if ($create_table) {
            $this->prepare_sqlite();
        }
    }

    private function prepare_sqlite(): void
    {
        $sql = <<<HERE
create table tag
(
    id      integer not null,
    slug    text    not null,
    name    text    not null,
    constraint tag_pk
        primary key (id autoincrement)
);

create table post_tag
(
    post_id int not null,
    tag_id int not null,
    constraint post_tag_pk
        unique (post_id, tag_id)
);

create table comment
(
    id              integer,
    post_id         integer not null,
    author_name     text,
    author_email    text,
    author_ip       text,
    author_url      text,
    author_ua       text,
    content         text,
    created_at      text    not null,
    updated_at      text    not null,
    constraint comment_pk
        primary key (id autoincrement)
);

create table post
(
    id         integer not null,
    slug       text default '' not null,
    title      text,
    content    text,
    wp_content text,
    status     text not null,
    created_at text    not null,
    updated_at text    not null,
    constraint post_pk
        primary key (id autoincrement)
);

create unique index post_slug_uindex
    on post (slug);
HERE;

        $this->sqlite->exec($sql);
    }

    public function get_wp_posts($post_type)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM wp_posts
            WHERE
                post_type = '{$post_type}'
                AND post_status IN ('publish', 'draft', 'private')
            ORDER BY post_date ASC
            ");
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function get_wp_terms($post_id): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM wp_terms t
            LEFT JOIN wp_term_taxonomy tt
                ON t.term_id = tt.term_id
            LEFT JOIN wp_term_relationships tr
                ON tt.term_taxonomy_id = tr.term_taxonomy_id
            WHERE tr.object_id = {$post_id}");

        $stmt->execute();

        $results = $stmt->fetchAll();

        $output = [];

        foreach($results as $row) {
            if (!in_array($row['taxonomy'], ['category', 'post_tag'])) {
                continue;
            }

            $output[] = [
                'name' => $row['name'],
                'slug'  => $row['slug'],
            ];
        }

        return $output;
    }

    public function get_wp_comments($post_id): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM wp_comments
            WHERE comment_post_ID = {$post_id}
            ORDER BY comment_date ASC
        ");
        $stmt->execute();

        $results = $stmt->fetchAll();

        $output = [];

        foreach($results as $row) {
            $comment = [
                'author_name'   => $row['comment_author'],
                'author_email'  => $row['comment_author_email'],
                'author_ip'     => $row['comment_author_IP'],
                'author_url'    => $row['comment_author_url'],
                'author_ua'     => $row['comment_agent'],
                'content'       => $row['comment_content'],
                'created_at'    => $row['comment_date'],
                'created_at_gmt'=> $row['comment_date_gmt'],
                'child'        => [],
            ];

            if ($row['comment_parent'] != 0 && in_array($row['comment_parent'], $output)) {
                $output[$row['comment_parent']]['child'][] = $comment;
            } else {
                $output[$row['comment_ID']] = $comment;
            }
        }

        return $output;
    }

    public function cleanup_wp_post($record): array
    {
        $converter = new HtmlConverter();
        $converter->getConfig()->setOption('strip_tags', true);
        $converter->getConfig()->setOption('hard_break', true);

        $post_content = $record['post_content'];

        $post_content = preg_replace('/\[code (lang|language)="(.*)"\]/', '<pre data-enlighter-language="$1" data-enlighter-linenumbers="">', $post_content);
        $post_content = str_replace(
            array('[code]', '[/code]',"https://dungnt.net/blog/wp-content/uploads/"),
            array('<pre data-enlighter-language="" data-enlighter-linenumbers="">', '</pre>', "static/uploads/"),
            $post_content
        );

        if (preg_match("/(<!-- wp:(.*) -->)/", $post_content)) {
            $post_content = preg_replace("/(<!-- wp:(.*) -->)/", "", $post_content);
            $post_content = preg_replace("/(<!-- \/wp:(.*) -->)/", "", $post_content);
        } else {
            $post_content = nl2br($post_content);
        }

        $markdown = $converter->convert($post_content);

        $markdown = preg_replace('/```\n<pre(.*)data-enlighter-language="(.*)" data-enlighter-linenumbers(.*)>/', "```$2\n", $markdown);

        $slug = $record['post_name'];
        if ($slug === '') {
            if (trim($record['post_title']) === "") {
                $slug = uniqid('', true);
            } else {
                $slug = $this->sanitize($record['post_title']);
            }
        }

        return [
            'slug'              => $slug,
            'title'             => $record['post_title'],
            'content'           => $markdown,
            'raw_content'       => $record['post_content'],
            'created_at'        => $record['post_date'],
            'created_at_gmt'    => $record['post_date_gmt'],
            'updated_at'        => $record['post_modified'],
            'updated_at_gmt'    => $record['post_modified_gmt'],
            'status'            => $record['post_status'],
        ];
    }

    public function run(): void
    {
        $posts = $this->get_wp_posts('post');

        $count = 0;

        foreach($posts as $row) {
            $count++;
            echo $count . ' | ' . $row['ID'] . ' - ' . $row['post_status'] . ' / ' . $row['post_title'] . PHP_EOL;

            $comments = $this->get_wp_comments($row['ID']);
            $terms = $this->get_wp_terms($row['ID']);
            $post = $this->cleanup_wp_post($row);

            $post_id = $this->insert_post($post);
            $this->insert_comment($post_id, $comments);
            $this->insert_terms($post_id, $terms);
        }
    }

    public function __destruct() {
        $this->db = null;
        $this->sqlite = null;
    }

    private function insert_post($data)
    {
        $stmt = $this->sqlite->prepare("
            INSERT INTO post(slug, title, content, wp_content, status, created_at, updated_at)
            VALUES(?, ?, ?, ?, ?, ?, ?);
            ");
        $stmt->execute([
            $data['slug'],
            $data['title'],
            $data['content'],
            $data['raw_content'],
            $data['status'],
            $data['created_at'],
            $data['updated_at']
        ]);

        return $this->sqlite->lastInsertId();
    }

    private function insert_each_comment($data)
    {
        $stmt = $this->sqlite->prepare("
            INSERT INTO comment(post_id, author_name, author_email, author_ip, author_url, author_ua, content, created_at, updated_at)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?);
            ");
        $stmt->execute($data);
    }

    private function insert_comment($post_id, $comments): void
    {
        foreach($comments as $record)
        {
            $this->insert_each_comment([
                $post_id,
                $record['author_name'],
                $record['author_email'],
                $record['author_ip'],
                $record['author_url'],
                $record['author_ua'],
                $record['content'],
                $record['created_at'],
                $record['created_at'],
            ]);

            if (empty($record['child'])) {
                continue;
            }

            foreach($record['child'] as $child)
            {
                $this->insert_each_comment([
                    $post_id,
                    $child['author_name'],
                    $child['author_email'],
                    $child['author_ip'],
                    $child['author_url'],
                    $child['author_ua'],
                    $child['content'],
                    $child['created_at'],
                    $child['created_at'],
                ]);
            }
        }
    }

    private function get_term_id($term): int
    {
        $slug = $term['slug'];

        $stmt = $this->sqlite->prepare("SELECT * FROM tag WHERE slug = '{$slug}'");
        $stmt->execute();

        $result = $stmt->fetch();
        if ($result) {
            return $result['id'];
        }

        $stmt = $this->sqlite->prepare("INSERT INTO tag(slug, name) VALUES(?, ?);");
        $stmt->execute([$term['slug'], $term['name']]);

        return $this->sqlite->lastInsertId();
    }

    private function insert_terms($post_id, $terms): void
    {
        $sql = "INSERT INTO post_tag(post_id, tag_id) VALUES(?, ?);";

        $exist_terms = [];

        foreach($terms as $term) {
            if (in_array($term['slug'], $exist_terms)) {
                continue;
            }

            $term_id = $this->get_term_id($term);

            $stmt = $this->sqlite->prepare($sql);
            $stmt->execute([
                $post_id,
                $term_id,
            ]);

            $exist_terms[] = $term['slug'];
        }
    }

    private function sanitize($title): string
    {
        $replacement = '-';
        $map = array();
        $quotedReplacement = preg_quote($replacement, '/');
        $default = array(
            '/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ|À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ|å/' => 'a',
            '/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ|È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ|ë/' => 'e',
            '/ì|í|ị|ỉ|ĩ|Ì|Í|Ị|Ỉ|Ĩ|î/' => 'i',
            '/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ|Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ|ø/' => 'o',
            '/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ|Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ|ů|û/' => 'u',
            '/ỳ|ý|ỵ|ỷ|ỹ|Ỳ|Ý|Ỵ|Ỷ|Ỹ/' => 'y',
            '/đ|Đ/' => 'd',
            '/ç/' => 'c',
            '/ñ/' => 'n',
            '/ä|æ/' => 'ae',
            '/ö/' => 'oe',
            '/ü/' => 'ue',
            '/Ä/' => 'Ae',
            '/Ü/' => 'Ue',
            '/Ö/' => 'Oe',
            '/ß/' => 'ss',
            '/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
            '/\\s+/' => $replacement,
            sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
        );
        //Some URL was encode, decode first
        $title = urldecode($title);
        $map = array_merge($map, $default);
        return strtolower(preg_replace(array_keys($map), array_values($map), $title));
    }
}

$cli = new ExportWordPress();
$cli->run();
