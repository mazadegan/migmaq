<?php
// models/Unit.php

class Unit
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(bool $publishedOnly = false): array
    {
        $sql = "SELECT * FROM units";
        if ($publishedOnly) {
            $sql .= " WHERE status = 'published'";
        }
        $sql .= " ORDER BY position ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id, bool $publishedOnly = false): ?array
    {
        $sql = "SELECT * FROM units WHERE id = ?";
        if ($publishedOnly) {
            $sql .= " AND status = 'published'";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $unit = $stmt->fetch(PDO::FETCH_ASSOC);
        return $unit ?: null;
    }

    public static function findByTitle($title)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM units WHERE title = ?");
        $stmt->execute([$title]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function updatePosition(int $id, int $position): void
    {
        $stmt = $this->pdo->prepare("UPDATE units SET position = :position WHERE id = :id");
        $stmt->execute([
            ':id' => $id,
            ':position' => $position,
        ]);
    }


    public function save(array $data): void
    {
        if (!empty($data['id'])) {
            // Update
            $stmt = $this->pdo->prepare("
                UPDATE units
                SET title = :title, body = :body, status = :status
                WHERE id = :id
            ");
            $stmt->execute([
                ':title' => $data['title'],
                ':body' => $data['body'],
                ':status' => $data['status'],
                ':id' => $data['id']
            ]);
        } else {
            // Insert
            $stmt = $this->pdo->prepare("
                INSERT INTO units (title, body, status)
                VALUES (:title, :body, :status)
            ");
            $stmt->execute([
                ':title' => $data['title'],
                ':body' => $data['body'],
                ':status' => $data['status']
            ]);
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM units WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updatePositions(array $positions): void
    {
        $stmt = $this->pdo->prepare("UPDATE units SET position = :position WHERE id = :id");

        foreach ($positions as $pos) {
            $stmt->execute([
                ':id' => $pos['id'],
                ':position' => $pos['position']
            ]);
        }
    }
}
