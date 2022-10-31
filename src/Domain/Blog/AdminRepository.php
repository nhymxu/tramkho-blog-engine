<?php

namespace App\Domain\Blog;

use App\PostNotFoundException;
use PDO;

class AdminRepository extends BlogRepository
{
    public function createPost(array $payload): string
    {
        $sql = 'INSERT INTO post(slug, title, content, status, created_at, updated_at)
                VALUES (:slug, :title, :content, :status, :create_time, :update_time)';

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($payload);

        return $this->connection->lastInsertId();

    }

    public function updatePost(array $payload): void
    {
        $sql = 'UPDATE post
                SET
                    slug = :slug,
                    title = :title,
                    content = :content,
                    status = :status,
                    updated_at = :update_time
                WHERE id = :post_id';

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($payload);
    }

    public function updatePostTag(int $post_id, int $tag_id): void
    {
        $sql = 'INSERT INTO post_tag(post_id, tag_id) VALUES (:post_id, :tag_id)';
        $payload = [
            'post_id'  => $post_id,
            'tag_id'  => $tag_id,
        ];

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($payload);
    }

    public function getTags(): array
    {
        $stmt = $this->connection->query('SELECT * FROM tag');

        if (!$stmt) {
            $stmt = null;
            return [];
        }

        $records = $stmt->fetchAll();

        $statement = null;

        if(!$records) {
            return [];
        }

        return $records;
    }

    public function createTag(string $tag_name, string $tag_slug): string
    {
        $sql = 'INSERT INTO tag(slug, name) VALUES (:slug, :name)';
        $payload = [
            'slug'  => $tag_slug,
            'name'  => $tag_name,
        ];

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($payload);

        return $this->connection->lastInsertId();
    }

    public function removePostTag(int $post_id, int $tag_id): void
    {
        $sql = 'DELETE FROM post_tag WHERE post_id = :post_id AND tag_id = :tag_id';
        $payload = [
            'post_id'  => $post_id,
            'tag_id'  => $tag_id,
        ];

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($payload);
    }

    public function updatePostStatus(array $payload): void
    {
        $sql = 'UPDATE post
                SET
                    status = :status,
                    updated_at = :update_time
                WHERE id = :post_id';

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($payload);
    }

    public function updatePostPublishTime(int $post_id, string $publish_time): void
    {
        $sql = "UPDATE post
                SET published_at = :publish_time
                WHERE id = :post_id AND published_at = ''";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            'post_id' => $post_id,
            'publish_time' => $publish_time
        ]);
    }

    public function updatePostDateTime(int $post_id, string $field, string $value): void
    {
        $sql = "UPDATE post
                SET ${field} = :value
                WHERE id = :post_id";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            'post_id'   => $post_id,
            'value'     => $value,
        ]);
    }

    public function get_post_statistic(): array
    {
        $stmt = $this->connection->query('SELECT status, COUNT(1) AS cnt FROM post GROUP BY status');

        if (!$stmt) {
            $stmt = null;
            return [];
        }

        $records = $stmt->fetchAll();

        $statement = null;

        if(!$records) {
            return [];
        }

        $statistic = [];

        foreach($records as $row) {
            $statistic[$row['status']] = (int)$row['cnt'];
        }

        $statistic = array_merge(
            [
                'publish'   => 0,
                'draft'     => 0,
                'trash'     => 0,
                'private'   => 0,
            ],
            $statistic
        );

        $statistic['total'] = array_sum($statistic);

        return $statistic;
    }
}
