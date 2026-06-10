<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>

<nav class="bg-body-tertiary border-bottom navbar navbar-expand-lg">
    <div class="d-flex align-items-center justify-content-between py-2 container-fluid">

        <!-- Left: Brand (links to homepage) + Breadcrumbs -->
        <div class="d-flex flex-wrap align-items-center gap-3">
            <a class="text-dark navbar-brand fw-bold" href="/">
                <img src="/assets/logo.png" alt="Logo" height="30" class="me-2">
                Learn Mi'gmaq
            </a>
            <a href="/dashboard" class="text-dark small">
                🏠 Dashboard Home
            </a>
            <?php if (isAdmin()): ?>
            <a href="/dashboard/site-settings" class="text-dark small">
                ⚙️ Site Settings
            </a>
            <?php endif; ?>

            <div class="text-muted small">
                <?php if (str_starts_with($currentPath, '/dashboard/section-editor') && isset($unit)): ?>
                    <a href="/dashboard/unit-editor">All Units</a> →
                    Unit: <a href="/dashboard/section-editor?unitId=<?= $unit['id'] ?>"><?= htmlspecialchars($unit['title']) ?></a> →
                    <strong>Sections</strong>
                <?php elseif (str_starts_with($currentPath, '/dashboard/lesson-editor') && isset($unit, $section)): ?>
                    <a href="/dashboard/unit-editor">All Units</a> →
                    Unit: <a href="/dashboard/section-editor?unitId=<?= $unit['id'] ?>"><?= htmlspecialchars($unit['title']) ?></a> →
                    Section: <a href="/dashboard/lesson-editor?unitId=<?= $unit['id'] ?>&sectionId=<?= $section['id'] ?>"><?= htmlspecialchars($section['title']) ?></a> →
                    <strong>Lessons</strong>
                <?php elseif (str_starts_with($currentPath, '/dashboard/unit-editor')): ?>
                    <!-- Optional: Viewing all Units -->
                <?php else: ?>
                    <!-- Optional: Dashboard -->
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Dashboard link + user info + logout -->
        <div class="d-flex align-items-center gap-3">
            <!-- Dashboard menu as a plain nav-style link -->

            <span class="text-muted small">
                Signed in as <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
            </span>

            <a href="/logout" class="btn-outline-danger btn btn-sm">
                <i class="bi-box-arrow-right me-1"></i> Log Out
            </a>
        </div>
    </div>
</nav>
