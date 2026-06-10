<?php
require_once __DIR__ . '/../models/Section.php';

class SectionController
{
    private Section $sectionModel;

    public function __construct(PDO $pdo)
    {
        $this->sectionModel = new Section($pdo);
    }

    public function fetchForUnit(): void
    {
        $unitId = $_GET['unit_id'] ?? null;
        if (!$unitId) {
            http_response_code(400);
            exit('Missing unit ID');
        }

        $sections = $this->sectionModel->getByUnit($unitId);
        echo json_encode($sections);
    }

    public function save(): void
    {
        verify_csrf_token_or_die();

        $data = [
            'id' => $_POST['sectionId'] ?? null,
            'unit_id' => $_POST['unitId'] ?? null,
            'title' => trim($_POST['sectionTitle'] ?? ''),
            'body' => $_POST['sectionBody'] ?? '',
            'status' => $_POST['sectionStatus'] ?? 'draft',
        ];

        // If we're creating a new section
        if (empty($data['id']) && !$data['unit_id']) {
            throw new Exception('Missing unit ID for new section');
        }

        if ($data['title'] === '') {
            throw new Exception('Missing section title');
        }

        $this->sectionModel->save($data);

        $action = empty($data['id']) ? 'section.create' : 'section.update';
        log_activity($action, ['title' => $data['title'], 'unit_id' => $data['unit_id']]);
        $unitId = $data['unit_id'];
        header("Location: /dashboard/section-editor?unitId=$unitId&status=section_saved");
        exit;
    }

    public function delete(): void
    {
        verify_csrf_token_or_die();

        $id = $_POST['sectionId'] ?? null;
        $unitId = $_POST['unitId'] ?? null;

        if ($id && $unitId) {
            $this->sectionModel->delete($id);
            log_activity('section.delete', ['section_id' => $id, 'unit_id' => $unitId]);
            header("Location: /dashboard/section-editor?unitId=$unitId&status=section_deleted");
            exit;
        } else {
            http_response_code(400);
            exit('Missing section ID or unit ID');
        }
    }


    public function fetch(): void
    {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing ID']);
            return;
        }

        $section = $this->sectionModel->find((int)$_GET['id']);

        if (!$section) {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
            return;
        }

        echo json_encode($section);
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
            if (!isset($item['id'], $item['position'])) {
                continue;
            }
            // Assuming you add a method "updatePosition" in your Section model:
            $this->sectionModel->updatePosition($item['id'], $item['position']);
        }
        echo json_encode(['status' => 'success']);
    }
}
