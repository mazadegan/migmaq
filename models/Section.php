<?php
class Section
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByUnit(int $unitId, bool $publishedOnly = false): array
    {
        $sql = "SELECT sections.* FROM sections
                JOIN units ON units.id = sections.unit_id
                WHERE sections.unit_id = ?";
        if ($publishedOnly) {
            $sql .= " AND sections.status = 'published' AND units.status = 'published'";
        }
        $sql .= " ORDER BY sections.position ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$unitId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save(array $data): void
    {
        if (!empty($data['id'])) {
            $stmt = $this->pdo->prepare("
            UPDATE sections
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
            $stmt = $this->pdo->prepare("
            INSERT INTO sections (unit_id, title, body, status)
            VALUES (:unit_id, :title, :body, :status)
        ");
            $stmt->execute([
                ':unit_id' => $data['unit_id'],
                ':title' => $data['title'],
                ':body' => $data['body'],
                ':status' => $data['status']
            ]);
        }
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM sections WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function find(int $id, bool $publishedOnly = false): ?array
    {
        $sql = "SELECT sections.* FROM sections
                JOIN units ON units.id = sections.unit_id
                WHERE sections.id = ?";
        if ($publishedOnly) {
            $sql .= " AND sections.status = 'published' AND units.status = 'published'";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $section = $stmt->fetch(PDO::FETCH_ASSOC);
        return $section ?: null;
    }

    public function updatePosition(int $id, int $position): void
    {
        $stmt = $this->pdo->prepare("UPDATE sections SET position = :position WHERE id = :id");
        $stmt->execute([
            ':position' => $position,
            ':id' => $id,
        ]);
    }
}
