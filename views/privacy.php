<!DOCTYPE html>
<html>
<?php require __DIR__ . '/partials/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <?php require __DIR__ . '/partials/content_navbar.php'; ?>

    <main class="flex-grow-1 mt-5 container">
        <h1 class="mb-4">Privacy Policy</h1>
        <?= site('privacy_body') ?>
    </main>

    <?php require __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
