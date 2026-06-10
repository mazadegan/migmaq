<?php
require_once __DIR__ . '/../lib/sendmail.php';


class AuthController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function showLogin()
    {
        $errors = [];
        require __DIR__ . '/../views/login.php';
    }

    public function login()
    {
        verify_csrf_token_or_die();

        $errors = [];

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $errors[] = "Email and password are required.";
        } else {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    log_activity('auth.login', ['email' => $email]);
                    header("Location: /dashboard");
                    exit();
                } else {
                    log_activity('auth.login_failed', ['email' => $email]);
                    $errors[] = "Invalid email or password.";
                }
            } catch (PDOException $e) {
                $errors[] = "Login error: " . $e->getMessage();
            }
        }

        require __DIR__ . '/../views/login.php';
    }

    public function showRegister()
    {
        $errors = [];
        $success = isset($_GET['success']);

        if (getSetting($this->pdo, 'registration_enabled', '1') !== '1') {
            $errors[] = "Registration has been disabled by the administrator. Please contact them to create an account.";
            require __DIR__ . '/../views/register.php';
            return;
        }

        require __DIR__ . '/../views/register.php';
    }


    public function register()
    {
        verify_csrf_token_or_die();

        $errors = [];
        $success = false;

        // ✅ FIRST: Check if registration is disabled
        if (getSetting($this->pdo, 'registration_enabled', '1') !== '1') {
            $errors[] = "Registration has been disabled by the administrator. Please contact them to create an account.";
            require __DIR__ . '/../views/register.php';
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$email || !$password) {
            $errors[] = "All fields are required.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
                $userCount = $stmt->fetchColumn();
                $role = $userCount == 0 ? 'admin' : 'contributor';

                $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashedPassword, $role]);

                log_activity('auth.register', ['username' => $username, 'email' => $email, 'role' => $role]);
                header("Location: /register?success=1");
                exit();
            } catch (PDOException $e) {
                if (str_contains($e->getMessage(), 'UNIQUE')) {
                    $errors[] = "Username or email already exists.";
                } else {
                    $errors[] = "Registration failed: " . $e->getMessage();
                }
            }
        }

        require __DIR__ . '/../views/register.php';
    }

    public function showForgotPasswordForm()
    {
        $errors = [];
        $success = false;
        require __DIR__ . '/../views/forgot_password.php';
    }

    public function handleForgotPassword()
    {
        verify_csrf_token_or_die();

        $email = trim($_POST['email'] ?? '');
        $errors = [];
        $success = false;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        } else {
            $stmt = $this->pdo->prepare("SELECT username FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $errors[] = "No account found with that email.";
            } else {
                // Remove any existing tokens for this email
                $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$email]);

                // Now insert the new token
                $rawToken = bin2hex(random_bytes(32)); // sent to user
                $hashedToken = hash('sha256', $rawToken); // stored in DB
                $expiresAt = date('Y-m-d H:i:s', time() + 3600);

                $stmt = $this->pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$email, $hashedToken, $expiresAt]);

                log_activity('auth.password_reset_requested', ['email' => $email]);
                $resetLink = "https://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=$rawToken";

                $body = "<p>Hi {$user['username']},</p>
                     <p>Click the link below to reset your password:</p>
                     <p><a href=\"$resetLink\">Reset Password</a></p>
                     <p>This link will expire in 1 hour.</p>";

                sendMail($email, $user['username'], "Password Reset", $body, true);

                $success = true;
            }
        }

        require __DIR__ . '/../views/forgot_password.php';
    }

    public function showResetPasswordForm()
    {
        $token = $_GET['token'] ?? '';
        $errors = [];

        if (!$token) {
            $errors[] = "Invalid or missing token.";
            require __DIR__ . '/../views/reset_password.php';
            return;
        }

        $hashedToken = hash('sha256', $token);

        $stmt = $this->pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > datetime('now')");
        $stmt->execute([$hashedToken]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$entry) {
            // Check if token exists, but expired
            $stmt = $this->pdo->prepare("SELECT 1 FROM password_resets WHERE token = ?");
            $stmt->execute([$hashedToken]);
            if ($stmt->fetch()) {
                $errors[] = "This token has expired. Please request a new password reset link.";
            } else {
                $errors[] = "Invalid reset link.";
            }
        }

        require __DIR__ . '/../views/reset_password.php';
    }

    public function handleResetPassword()
    {
        verify_csrf_token_or_die();

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $errors = [];

        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }

        $hashedToken = hash('sha256', $token);
        $stmt = $this->pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > datetime('now')");
        $stmt->execute([$hashedToken]);

        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$entry) {
            $errors[] = "Invalid or expired token.";
        }

        if (empty($errors)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed, $entry['email']]);

            $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$entry['email']]);

            log_activity('auth.password_reset_completed', ['email' => $entry['email']]);
            header("Location: /login?reset=success");
            exit();
        }

        require __DIR__ . '/../views/reset_password.php';
    }


    public function logout()
    {
        log_activity('auth.logout');
        session_destroy();
        header("Location: /login");
        unset($_SESSION['csrf_token']);
        exit();
    }
}
