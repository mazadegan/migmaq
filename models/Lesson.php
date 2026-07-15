<?php
// models/Lesson.php

class Lesson
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getBySection(int $sectionId, bool $publishedOnly = false): array
    {
        $sql = "SELECT lessons.* FROM lessons
                JOIN sections ON sections.id = lessons.section_id
                JOIN units ON units.id = sections.unit_id
                WHERE lessons.section_id = ?";
        if ($publishedOnly) {
            $sql .= " AND lessons.status = 'published' AND sections.status = 'published' AND units.status = 'published'";
        }
        $sql .= " ORDER BY lessons.position ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sectionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id, bool $publishedOnly = false): ?array
    {
        $sql = "SELECT lessons.* FROM lessons
                JOIN sections ON sections.id = lessons.section_id
                JOIN units ON units.id = sections.unit_id
                WHERE lessons.id = ?";
        if ($publishedOnly) {
            $sql .= " AND lessons.status = 'published' AND sections.status = 'published' AND units.status = 'published'";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
        return $lesson ?: null;
    }

    public function save(array $data): void
    {
        if (!empty($data['id'])) {
            // Update existing lesson
            $stmt = $this->pdo->prepare("
                UPDATE lessons
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
            // Insert new lesson
            $stmt = $this->pdo->prepare("
                INSERT INTO lessons (section_id, title, body, status)
                VALUES (:section_id, :title, :body, :status)
            ");
            $stmt->execute([
                ':section_id' => $data['section_id'],
                ':title' => $data['title'],
                ':body' => $data['body'],
                ':status' => $data['status']
            ]);
        }
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM lessons WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function updatePosition(int $id, int $position): void
    {
        $stmt = $this->pdo->prepare("UPDATE lessons SET position = :position WHERE id = :id");
        $stmt->execute([
            ':position' => $position,
            ':id' => $id
        ]);
    }

    public function updatePositions(array $positions): void
    {
        $stmt = $this->pdo->prepare("UPDATE lessons SET position = :position WHERE id = :id");
        foreach ($positions as $pos) {
            $stmt->execute([
                ':position' => $pos['position'],
                ':id' => $pos['id']
            ]);
        }
    }

    public function getAdjacentLessons(int $lessonId, bool $publishedOnly = false): array
    {
        $stmt = $this->pdo->prepare("SELECT section_id, position FROM lessons WHERE id = ?");
        $stmt->execute([$lessonId]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current) return ['prev' => null, 'next' => null];

        $statusFilter = $publishedOnly ? " AND status = 'published'" : "";

        // Get previous lesson
        $stmt = $this->pdo->prepare("
        SELECT id, title FROM lessons
        WHERE section_id = ? AND position < ?{$statusFilter}
        ORDER BY position DESC LIMIT 1
    ");
        $stmt->execute([$current['section_id'], $current['position']]);
        $prev = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get next lesson
        $stmt = $this->pdo->prepare("
        SELECT id, title FROM lessons
        WHERE section_id = ? AND position > ?{$statusFilter}
        ORDER BY position ASC LIMIT 1
    ");
        $stmt->execute([$current['section_id'], $current['position']]);
        $next = $stmt->fetch(PDO::FETCH_ASSOC);

        return ['prev' => $prev, 'next' => $next];
    }
}
