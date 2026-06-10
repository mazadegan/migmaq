<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/helpers.php';

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/UnitController.php';
require_once __DIR__ . '/../controllers/AudioController.php';
require_once __DIR__ . '/../controllers/SectionController.php';
require_once __DIR__ . '/../controllers/LessonController.php';

require_once __DIR__ . '/../controllers/ContentsController.php';
require_once __DIR__ . '/../controllers/PageController.php';
require_once __DIR__ . '/../controllers/SiteSettingsController.php';

// Normalize the path
$uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if ($uri === '') $uri = '/';

$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        '/' => fn() => (new PageController($pdo))->landing(),
        '/about' => fn() => require __DIR__ . '/../views/about.php',
        '/privacy' => fn() => require __DIR__ . '/../views/privacy.php',
        '/contact' => fn() => require __DIR__ . '/../views/contact.php',
        '/welalieg' => fn() => require __DIR__ . '/../views/welalieg.php',
        '/contents' => fn() => (new ContentsController($pdo))->show(),
        '/dashboard' => fn() => (new DashboardController($pdo))->index(),
        '/login' => fn() => (new AuthController($pdo))->showLogin(),
        '/register' => fn() => (new AuthController($pdo))->showRegister(),
        '/logout' => fn() => (new AuthController($pdo))->logout(),
        '/forgot-password' => fn() => (new AuthController($pdo))->showForgotPasswordForm(),
        '/reset-password' => fn() => (new AuthController($pdo))->showResetPasswordForm(),
        '/unit/fetch' => fn() => (new UnitController($pdo))->fetch(),
        '/audio' => fn() => (new AudioController($pdo))->stream(),
        '/section/fetch' => fn() => (new SectionController($pdo))->fetch(),
        '/dashboard/account' => fn() => (new UserController($pdo))->showAccount(),
        '/dashboard/manage-users' => fn() => (new UserController($pdo))->index(),
        '/dashboard/site-settings' => fn() => (new SiteSettingsController($pdo))->show(),
        '/dashboard/unit-editor' => fn() => (new DashboardController($pdo))->unitEditor(),
        '/dashboard/section-editor' => fn() => (new DashboardController($pdo))->sectionEditor(),
        '/dashboard/lesson-editor' => fn() => (new DashboardController($pdo))->lessonEditor(),
        '/lesson/fetch' => fn() => (new LessonController($pdo))->fetch(),
        '/unit' => fn() => (new PageController($pdo))->showUnit(),
        '/section' => fn() => (new PageController($pdo))->showSection(),
        '/lesson' => fn() => (new PageController($pdo))->showLesson(),
    ],
    'POST' => [
        '/login' => fn() => (new AuthController($pdo))->login(),
        '/register' => fn() => (new AuthController($pdo))->register(),
        '/forgot-password' => fn() => (new AuthController($pdo))->handleForgotPassword(),
        '/reset-password' => fn() => (new AuthController($pdo))->handleResetPassword(),
        '/user/update-role' => fn() => (new UserController($pdo))->updateRole(),
        '/user/change-password' => fn() => (new UserController($pdo))->changePassword(),
        '/user/self-password' => fn() => (new UserController($pdo))->changeOwnPassword(),
        '/user/create' => fn() => (new UserController($pdo))->createUser(),
        '/user/update' => fn() => (new UserController($pdo))->updateUser(),
        '/user/delete' => fn() => (new UserController($pdo))->deleteUser(),
        '/unit/save' => fn() => (new UnitController($pdo))->save(),
        '/unit/delete' => fn() => (new UnitController($pdo))->delete(),
        '/unit/update-order' => fn() => (new UnitController($pdo))->updateOrder(),
        '/audio/upload' => fn() => (new AudioController($pdo))->upload(),
        '/section/save' => fn() => (new SectionController($pdo))->save(),
        '/section/delete' => fn() => (new SectionController($pdo))->delete(),
        '/section/update-order' => fn() => (new SectionController($pdo))->updateOrder(),
        '/lesson/save' => fn() => (new LessonController($pdo))->save(),
        '/lesson/delete' => fn() => (new LessonController($pdo))->delete(),
        '/lesson/update-order' => fn() => (new LessonController($pdo))->updateOrder(),
        '/settings/registration' => fn() => (new UserController($pdo))->toggleRegistration(),
        '/settings/update' => fn() => (new UserController($pdo))->updateSetting(),
        '/site-settings/save-branding' => fn() => (new SiteSettingsController($pdo))->saveBranding(),
        '/site-settings/save-social' => fn() => (new SiteSettingsController($pdo))->saveSocial(),
        '/site-settings/save-page-visibility' => fn() => (new SiteSettingsController($pdo))->savePageVisibility(),
        '/site-settings/save-page-content' => fn() => (new SiteSettingsController($pdo))->savePageContent(),
        '/site-settings/save-column-images' => fn() => (new SiteSettingsController($pdo))->saveColumnImages(),
    ]
];

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (isset($routes[$method][$path])) {
    $handler = $routes[$method][$path];
    if (is_callable($handler)) {
        $handler();
    } else {
        require $handler;
    }
} else {
    http_response_code(404);
    require __DIR__ . '/../views/404.php';
}
