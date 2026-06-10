<?php

require_once __DIR__ . '/../models/Unit.php';

class UnitController
{
    private Unit $unitModel;

    public function __construct(PDO $pdo)
    {
        $this->unitModel = new Unit($pdo);
    }

    public function fetch(): void
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            exit('Unauthorized');
        }

        if (!isset($_GET['id'])) {
            http_response_code(400);
            exit('Missing ID');
        }

        $unit = $this->unitModel->find($_GET['id']);

        if ($unit) {
            echo json_encode([
                'body' => $unit['body'],
                'status' => $unit['status'],
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
    }


    public function dashboard(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        $units = $this->unitModel->all();
        require __DIR__ . '/../views/dashboard.php';
    }

    public function delete(): void
    {
        verify_csrf_token_or_die();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method');
        }

        $unitId = $_POST['unitId'] ?? null;

        if (!$unitId) {
            throw new Exception('Missing unit ID');
        }

        $this->unitModel->delete($unitId);

        log_activity('unit.delete', ['unit_id' => $unitId]);
        // header('Location: /dashboard?status=deleted');
        header('Location: /dashboard/unit-editor?status=deleted');
        exit;
    }

    public function save(): void
    {
        verify_csrf_token_or_die();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method');
        }

        $title = trim($_POST['unitTitle'] ?? '');
        $body  = $_POST['unitBody'] ?? '';
        $status = $_POST['unitStatus'] ?? 'draft';
        $unitId = $_POST['unitId'] ?? null;

        if ($title === '') {
            throw new Exception('Unit title cannot be empty');
        }

        $this->unitModel->save([
            'id' => $unitId,
            'title' => $title,
            'body' => $body,
            'status' => $status
        ]);

        $action = $unitId ? 'unit.update' : 'unit.create';
        log_activity($action, ['title' => $title, 'status' => $status]);
        header('Location: /dashboard/unit-editor?status=success');
        exit;
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
            $this->unitModel->updatePosition($item['id'], $item['position']);
        }

        echo json_encode(['status' => 'success']);
    }
}
