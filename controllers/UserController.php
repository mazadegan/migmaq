<?php

require_once __DIR__ . '/../models/User.php';

class UserController
{
    private PDO $pdo;
    private User $userModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
    }

    public function index(): void
    {
        if (!isAdmin()) {
            http_response_code(403);
            exit("Access denied");
        }

        $users = $this->userModel->all();
        $pdo = $this->pdo;
        $allSettings = getAllSettings($this->pdo);

        require __DIR__ . '/../views/manage_users.php';
    }

    public function updateSetting(): void
    {
        verify_csrf_token_or_die();

        if (!isAdmin()) {
            http_response_code(403);
            exit("Access denied");
        }

        $key = $_POST['key'] ?? null;
        $value = $_POST['value'] ?? null;

        if ($key === null || $value === null) {
            http_response_code(400);
            exit("Missing key or value");
        }

        setSetting($this->pdo, $key, $value);
        header("Location: /dashboard/manage-users?status=setting_updated");
        exit();
    }


    public function updateRole(): void
    {
        verify_csrf_token_or_die();

        if (!isAdmin()) {
            http_response_code(403);
            exit("Access denied");
        }

        $userId = $_POST['userId'] ?? null;
        $role = $_POST['role'] ?? null;

        if (!ctype_digit($userId) || !in_array($role, ['admin', 'contributor'], true)) {
            http_response_code(400);
            exit("Invalid input");
        }

        $user = $this->userModel->find((int)$userId);
        if ($user) {
            $this->userModel->update((int)$userId, $user['username'], $user['email'], $role);
            log_activity('user.role_updated', ['target_user_id' => $userId, 'new_role' => $role]);
            header("Location: /dashboard/manage-users?status=role_updated");
            exit;
        }

        http_response_code(404);
        exit("User not found");
    }

    public function changePassword(): void
    {
        verify_csrf_token_or_die();

        if (!isAdmin()) {
            http_response_code(403);
            exit("Access denied");
        }

        $userId = $_POST['userId'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!ctype_digit($userId) || !$password || strlen($password) < 6) {
            http_response_code(400);
            exit("Invalid input");
        }

        $this->userModel->updatePassword((int)$userId, $password);
        log_activity('user.password_changed', ['target_user_id' => $userId]);
        header("Location: /dashboard/manage-users?status=password_changed");
        exit();
    }

    public function createUser(): void
    {
        verify_csrf_token_or_die();

        if (!isAdmin()) {
            http_response_code(403);
            exit("Access denied");
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'contributor';

        if (!$username || !$email || !$password || strlen($password) < 6) {
            http_response_code(400);
            exit("Invalid input");
        }

        $this->userModel->create($username, $email, $password, $role);

        log_activity('user.create', ['username' => $username, 'email' => $email, 'role' => $role]);
        header("Location: /dashboard/manage-users?status=user_created");
        exit;
    }

    public function updateUser(): void
    {
        verify_csrf_token_or_die();

        if (!isAdmin()) {
            http_response_code(403);
            exit("Access denied");
        }

        $id = $_POST['userId'] ?? null;
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? null;

        if (!ctype_digit($id) || !$username || !$email || !in_array($role, ['admin', 'contributor'], true)) {
            http_response_code(400);
            exit("Invalid input");
        }

        $this->userModel->update((int)$id, $username, $email, $role);
        log_activity('user.update', ['target_user_id' => $id, 'username' => $username]);
        header("Location: /dashboard/manage-users?status=updated");
        exit();
    }

    public function deleteUser(): void
    {
        verify_csrf_token_or_die();

        if (!isAdmin()) {
            http_response_code(403);
            exit("Access denied");
        }

        $id = $_POST['userId'] ?? null;

        if (!ctype_digit($id)) {
            http_response_code(400);
            exit("Missing or invalid ID");
        }

        $this->userModel->delete((int)$id);
        log_activity('user.delete', ['target_user_id' => $id]);
        header("Location: /dashboard/manage-users?status=deleted");
        exit();
    }

    public function toggleRegistration(): void
    {
        verify_csrf_token_or_die();

        if (!isAdmin()) {
            http_response_code(403);
            exit("Access denied");
        }

        $enabled = $_POST['enabled'] ?? '0';
        setSetting($this->pdo, 'registration_enabled', $enabled === '1' ? '1' : '0');
        header("Location: /dashboard/manage-users?status=registration_updated");
        exit;
    }

    public function showAccount(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->find((int)$userId);

        if (!$user) {
            http_response_code(404);
            exit("User not found");
        }

        require __DIR__ . '/../views/account.php';
    }

    public function changeOwnPassword(): void
    {
        verify_csrf_token_or_die();

        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            exit("Unauthorized");
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $userId = $_SESSION['user_id'];

        $errors = [];

        if (!$currentPassword || !$newPassword || !$confirmPassword) {
            $errors[] = "All fields are required.";
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = "New passwords do not match.";
        }

        if (strlen($newPassword) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }

        if (!$this->userModel->verifyPassword((int)$userId, $currentPassword)) {
            $errors[] = "Current password is incorrect.";
        }

        if ($errors) {
            $_SESSION['account_errors'] = $errors;
            header("Location: /dashboard/account");
            exit;
        }

        $this->userModel->updatePassword((int)$userId, $newPassword);

        log_activity('user.change_own_password');
        $_SESSION['account_success'] = true;
        header("Location: /dashboard/account");
        exit;
    }
}
