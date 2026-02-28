<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class KnowledgeArticle
{
    public function create(array $data): int
    {
        $stmt = Database::connection()->prepare('INSERT INTO knowledge_articles (category_id,title,slug,body,tags,source_url,is_published,created_by,created_at) VALUES (:category_id,:title,:slug,:body,:tags,:source_url,:is_published,:created_by,NOW())');
        $stmt->execute([
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'slug' => $data['slug'],
            'body' => $data['body'],
            'tags' => $data['tags'] ?? null,
            'source_url' => $data['source_url'] ?? null,
            'is_published' => $data['is_published'] ?? 1,
            'created_by' => $data['created_by'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function searchByKeywords(array $keywords, int $limit = 8): array
    {
        if (count($keywords) === 0) {
            return [];
        }

        $parts = [];
        $params = [];
        foreach ($keywords as $i => $kw) {
            $key = 'kw' . $i;
            $parts[] = '(title LIKE :' . $key . ' OR body LIKE :' . $key . ' OR tags LIKE :' . $key . ')';
            $params[$key] = '%' . $kw . '%';
        }

        $sql = 'SELECT id, title, tags, source_url, body FROM knowledge_articles WHERE is_published = 1 AND (' . implode(' OR ', $parts) . ') ORDER BY updated_at DESC, created_at DESC LIMIT ' . (int) $limit;
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function search(string $q, int $limit = 30): array
    {
        $q = trim($q);
        if ($q === '') {
            $stmt = Database::connection()->prepare('SELECT id,title,tags,source_url,created_at FROM knowledge_articles ORDER BY id DESC LIMIT :limit');
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $stmt = Database::connection()->prepare('SELECT id,title,tags,source_url,created_at FROM knowledge_articles WHERE title LIKE :q OR body LIKE :q OR tags LIKE :q ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue(':q', '%' . $q . '%');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
