<?php

namespace App\Domain\Blog;

use App\PostNotFoundException;
use PDO;

class BlogRepository
{
    /**
     * @var PDO
     */
    protected $connection;

    /**
     * Constructor
     *
     * @param PDO $connection
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get list post per page
     *
     * @param int $page
     * @param array $filter
     * @param int $per_page
     * @return array
     */
    public function getByPage(int $page = 1, array $filter = [], int $per_page = 20): array
    {
        $offset = ($page - 1) * $per_page;

        $sql = 'SELECT p.id, p.title, p.slug, p.status, p.created_at, p.updated_at FROM post p';
        $bind_value = [
            'offset'    => $offset,
            'row_count' => $per_page,
        ];

        if (isset($filter['tag_id']) && $filter['tag_id'] !== '') {
            $sql .= ' LEFT JOIN post_tag pt ON pt.post_id = p.id';
        }

        $result_condition = $this->buildCondition($filter);

        if (!empty($result_condition['condition'])) {
            $sql .= ' WHERE ' . implode(" AND ", $result_condition['condition']);
        }

        $bind_value = array_merge($bind_value, $result_condition['bind_value']);

        $sql .= ' ORDER BY p.created_at DESC LIMIT :offset, :row_count';

        $statement = $this->connection->prepare($sql);
        $statement->execute($bind_value);

        $records = $statement->fetchAll();

        $statement = null;

        if(!$records) {
            throw new PostNotFoundException(sprintf("Page not found: %s", $page));
        }

        return $records;
    }

    /**
     * Build pagination
     *
     * @param int $page
     * @param array $filter
     * @param int $per_page
     * @return array
     */
    public function getPagination(int $page = 1, array $filter = [], int $per_page = 20): array
    {
        $sql = 'SELECT COUNT(1) FROM post p';
        $bind_value = [];

        $result_condition = $this->buildCondition($filter);

        if (isset($filter['tag_id']) && $filter['tag_id'] !== '') {
            $sql .= ' LEFT JOIN post_tag pt ON pt.post_id = p.id';
        }

        if (!empty($result_condition['condition'])) {
            $sql .= ' WHERE ' . implode(" AND ", $result_condition['condition']);
        }

        $bind_value = array_merge($bind_value, $result_condition['bind_value']);

        try {
            $statement = $this->connection->prepare($sql);
            $result = $statement->execute($bind_value);

            if (!$result) {
                throw new \RuntimeException("Not Found");
            }

            $total = $statement->fetchColumn();
            $total = (int) $total;

            $statement = null;
        } catch (\Exception $e) {
            return [
                'prev'      => '',
                'next'      => '',
                'current'   => 1,
                'total'     => 1,
            ];
        }

        // How many pages will there be
        $pages = ceil($total / $per_page);

        // Calculate the offset for the query
        $offset = ($page - 1)  * $per_page;

        // Some information to display to the user
        $start = $offset + 1;
        $end = min(($offset + $per_page), $total);

        // Display the paging information
        // echo 'Page ', $page, ' of ', $pages, ' pages, displaying ', $start, '-', $end, ' of ', $total, ' results';

        return [
            'prev'      => ($page > 1) ? ($page - 1) : '',
            'next'      => ($page < $pages) ? ($page + 1) : '',
            'current'   => $page,
            'total'     => $pages,
        ];
    }

    /**
     * Get single post by slug
     *
     * @param string $slug
     * @param string $status
     * @return mixed
     */
    public function getBySlug(string $slug, string $status = 'publish')
    {
        $sql = 'SELECT * FROM post WHERE slug = :slug';
        $bind_value = ['slug' => $slug];

        if ($status !== '') {
            $sql .= ' AND status = :status';
            $bind_value['status'] = $status;
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($bind_value);

        $row = $statement->fetch();

        $statement = null;

        if(!$row) {
            throw new PostNotFoundException(sprintf("Post not found: %s", $slug));
        }

        return $row;
    }

    /**
     * Get single post by id
     *
     * @param string $post_id
     * @param string $status
     * @return mixed
     */
    public function getById(string $post_id, string $status = '')
    {
        $sql = 'SELECT * FROM post WHERE id = :id';
        $bind_value = ['id' => $post_id];

        if ($status !== '') {
            $sql .= ' AND status = :status';
            $bind_value['status'] = $status;
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($bind_value);

        $row = $statement->fetch();

        $statement = null;

        if(!$row) {
            throw new PostNotFoundException(sprintf("Post not found: %s", $post_id));
        }

        return $row;
    }

    /**
     * Return all tag of post via post ID
     *
     * @param int $post_id
     * @return array
     */
    public function getPostTag(int $post_id): array
    {
        $sql = 'SELECT t.*
          FROM post_tag pt
          LEFT JOIN tag t ON pt.tag_id = t.id
          WHERE pt.post_id = :post_id';

        $statement = $this->connection->prepare($sql);
        $statement->execute(['post_id' => $post_id]);

        $records = $statement->fetchAll();

        $statement = null;

        if(!$records) {
            return [];
        }

        return $records;
    }

    /**
     * @param string $slug
     * @return array
     */
    public function getTag(string $slug): array
    {
        $sql = 'SELECT * FROM tag WHERE slug = :slug';

        $statement = $this->connection->prepare($sql);
        $statement->execute(['slug' => $slug]);

        $row = $statement->fetch();

        $statement = null;

        if(!$row) {
            throw new PostNotFoundException(sprintf("Post not found: %s", $slug));
        }

        return $row;
    }

    /**
     * Get tag cloud data
     *
     * @return array
     */
    public function getTagCloud(): array
    {
        $sql = "SELECT pt.tag_id, t.name, t.slug, COUNT(1) AS cnt
                FROM post_tag pt
                LEFT JOIN tag t ON pt.tag_id = t.id
                LEFT JOIN post p ON pt.post_id = p.id
                WHERE p.status = 'publish'
                GROUP BY pt.tag_id;
                ";

        $statement = $this->connection->query($sql);

        if (!$statement) {
            return [];
        }

        $records = $statement->fetchAll();

        $statement = null;

        if(!$records) {
            return [];
        }

        return $records;
    }

    private function buildCondition(array $filter): array
    {
        $where_conditions = [];
        $bind_value = [];

        $status = $filter['status'] ?? 'publish';
        $tag_id = $filter['tag_id'] ?? '';
        $ignore_tag = $filter['ignore_tag'] ?? false;

        if ($tag_id !== '') {
            $where_conditions[] = 'pt.tag_id = :tag_id';
            $where_conditions[] = 'pt.post_id NOT NULL';
            $bind_value['tag_id'] = $tag_id;
        }

        if ($status !== '') {
            $where_conditions[] = 'p.status = :status';
            $bind_value['status'] = $status;
        }

        if ($ignore_tag) {
            $where_conditions[] = 'p.id NOT IN (SELECT DISTINCT pt.post_id FROM post_tag pt WHERE pt.tag_id IN (:tag_ids))';
            $bind_value['tag_ids'] = implode(', ', $ignore_tag);
        }

        return [
            'condition'     => $where_conditions,
            'bind_value'    => $bind_value,
        ];
    }
}
