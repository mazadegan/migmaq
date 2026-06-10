<?php
require_once __DIR__ . '/../models/Lesson.php';

class LessonController
{
    private Lesson $lessonModel;

    public function __construct(PDO $pdo)
    {
        $this->lessonModel = new Lesson($pdo);
    }

    public function save(): void
    {
        verify_csrf_token_or_die();

        $data = [
            'id' => $_POST['lessonId'] ?? null,
            'section_id' => $_POST['sectionId'] ?? null,
            'title' => trim($_POST['lessonTitle'] ?? ''),
            'body' => $_POST['lessonBody'] ?? '',
            'status' => $_POST['lessonStatus'] ?? 'draft',
        ];

        if ($data['title'] === '') {
            throw new Exception('Missing lesson title');
        }

        if (empty($data['id']) && !$data['section_id']) {
            throw new Exception('Missing section ID for new lesson');
        }

        $this->lessonModel->save($data);

        $action = empty($data['id']) ? 'lesson.create' : 'lesson.update';
        log_activity($action, ['title' => $data['title'], 'section_id' => $data['section_id']]);
        $unitId = $_POST['unitId'] ?? '';
        $sectionId = $data['section_id'];
        header("Location: /dashboard/lesson-editor?unitId=$unitId&sectionId=$sectionId&status=lesson_saved");
        exit;
    }

    public function delete(): void
    {
        verify_csrf_token_or_die();

        $id = $_POST['lessonId'] ?? null;
        $unitId = $_POST['unitId'] ?? null;
        $sectionId = $_POST['sectionId'] ?? null;

        if ($id && $sectionId && $unitId) {
            $this->lessonModel->delete($id);
            log_activity('lesson.delete', ['lesson_id' => $id, 'section_id' => $sectionId]);
            header("Location: /dashboard/lesson-editor?unitId=$unitId&sectionId=$sectionId&status=lesson_deleted");
            exit;
        } else {
            http_response_code(400);
            exit('Missing lesson ID, unit ID, or section ID');
        }
    }

    public function fetch(): void
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing ID']);
            return;
        }

        $lesson = $this->lessonModel->find((int)$id);

        if (!$lesson) {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
            return;
        }

        echo json_encode($lesson);
    }

    public function updateOrder(): void
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            exit('Unauthorized');
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            http_response_code(400);
            exit('Invalid input');
        }

        foreach ($data as $item) {
            if (!isset($item['id'], $item['position'])) continue;
            $this->lessonModel->updatePosition($item['id'], $item['position']);
        }

        echo json_encode(['status' => 'success']);
    }
}
