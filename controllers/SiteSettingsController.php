<?php

class SiteSettingsController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function show(): void
    {
        if (!isAdmin()) {
            http_response_code(403);
            exit('Access denied');
        }

        require __DIR__ . '/../views/site_settings.php';
    }

    public function saveBranding(): void
    {
        verify_csrf_token_or_die();

        if (!isAdmin()) {
            http_response_code(403);
            exit('Access denied');
        }

        $fields = ['site_name', 'site_color'];
        foreach ($fields as $key) {
            if (isset($_POST[$key])) {
                setSetting($this->pdo, $key, trim($_POST[$key]));
            }
        }

        foreach (['site_logo' => 'logo_upload', 'home_hero' => 'hero_upload'] as $settingKey => $fileKey) {
            if (!empty($_FILES[$fileKey]['tmp_name'])) {
                $filename = $this->uploadImage($_FILES[$fileKey], $settingKey);
                if ($filename) {
                    setSetting($this->pdo, $settingKey, $filename);
                }
            }
        }

        log_activity('site_settings.branding_saved');
        header('Location: /dashboard/site-settings?status=saved');
        exit;
    }

    public function saveSocial(): void
    {
        verify_csrf_token_or_die();

        if (!isAdmin()) {
            http_response_code(403);
            exit('Access denied');
        }

        $platforms = ['facebook', 'twitter', 'instagram'];
        foreach ($platforms as $platform) {
            $enabledKey = "social_{$platform}_enabled";
            $urlKey     = "social_{$platform}_url";
            setSetting($this->pdo, $enabledKey, isset($_POST[$enabledKey]) ? '1' : '0');
            if (isset($_POST[$urlKey])) {
                setSetting($this->pdo, $urlKey, trim($_POST[$urlKey]));
            }
        }

        log_activity('site_settings.social_saved');
        header('Location: /dashboard/site-settings?status=saved');
        exit;
    }

    public function savePageVisibility(): void
    {
        verify_csrf_token_or_die();

        if (!isAdmin()) {
            http_response_code(403);
            exit('Access denied');
        }

        $pages = ['about', 'privacy', 'contact', 'welalieg'];
        foreach ($pages as $page) {
            $key = "page_{$page}_enabled";
            setSetting($this->pdo, $key, isset($_POST[$key]) ? '1' : '0');
        }

        log_activity('site_settings.page_visibility_saved');
        header('Location: /dashboard/site-settings?status=saved');
        exit;
    }

    public function savePageContent(): void
    {
        verify_csrf_token_or_die();

        if (!isAdmin()) {
            http_response_code(403);
            exit('Access denied');
        }

        $allowed = ['home_body', 'about_body', 'privacy_body', 'contact_body'];
        $key = $_POST['active_key'] ?? '';

        if (in_array($key, $allowed, true) && isset($_POST[$key])) {
            setSetting($this->pdo, $key, $_POST[$key]);
            log_activity('site_settings.page_content_saved', ['page' => $key]);
        }

        header('Location: /dashboard/site-settings?status=saved');
        exit;
    }

    public function saveColumnImages(): void
    {
        verify_csrf_token_or_die();

        if (!isAdmin()) {
            http_response_code(403);
            exit('Access denied');
        }

        for ($i = 1; $i <= 3; $i++) {
            $altKey  = "home_col_image_{$i}_alt";
            $fileKey = "col_image_{$i}";
            $imgKey  = "home_col_image_{$i}";

            if (isset($_POST[$altKey])) {
                setSetting($this->pdo, $altKey, trim($_POST[$altKey]));
            }

            if (!empty($_FILES[$fileKey]['tmp_name'])) {
                $filename = $this->uploadImage($_FILES[$fileKey], $imgKey);
                if ($filename) {
                    setSetting($this->pdo, $imgKey, $filename);
                }
            }
        }

        log_activity('site_settings.column_images_saved');
        header('Location: /dashboard/site-settings?status=saved');
        exit;
    }

    private function uploadImage(array $file, string $settingKey): ?string
    {
        $allowed = ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/svg+xml'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowed, true)) {
            return null;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $settingKey . '_' . time() . '.' . strtolower($ext);
        $dest = __DIR__ . '/../public/assets/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return $filename;
        }

        return null;
    }
}
