<?php (function () {
    global $pdo;

    require_once __DIR__ . '/../../models/Unit.php';
    require_once __DIR__ . '/../../models/Section.php';
    require_once __DIR__ . '/../../models/Lesson.php';

    $unitModel = new Unit($pdo);
    $sectionModel = new Section($pdo);
    $lessonModel = new Lesson($pdo);

    $units = $unitModel->all(true);
    foreach ($units as &$unitItem) {
        $unitItem['sections'] = $sectionModel->getByUnit($unitItem['id'], true);
        foreach ($unitItem['sections'] as &$sectionItem) {
            $sectionItem['lessons'] = $lessonModel->getBySection($sectionItem['id'], true);
        }
    }
    ?>
    <!-- Toggle Button -->
    <button id="tocToggleBtn" class="bottom-0 position-fixed m-3 btn-outline-secondary btn start-0" type="button"
        data-bs-toggle="offcanvas" data-bs-target="#tocOffcanvas" aria-controls="tocOffcanvas" style="z-index: 1050;">
        📖
    </button>

    <!-- Offcanvas TOC -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="tocOffcanvas" aria-labelledby="tocOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="tocOffcanvasLabel">📚 Table of Contents</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-unstyled">
                <?php foreach ($units as $unitItem): ?>
                    <li class="mb-2">
                        <strong>📘 <?= htmlspecialchars($unitItem['title']) ?></strong>
                        <ul class="ms-3 list-unstyled">
                            <?php foreach ($unitItem['sections'] as $sectionItem): ?>
                                <li>
                                    <a href="/section?id=<?= $sectionItem['id'] ?>" class="text-decoration-none">
                                        📗 <?= htmlspecialchars($sectionItem['title']) ?>
                                    </a>
                                    <?php if (!empty($sectionItem['lessons'])): ?>
                                        <ul class="ms-3 list-unstyled">
                                            <?php foreach ($sectionItem['lessons'] as $lessonItem): ?>
                                                <li>
                                                    <a href="/lesson?id=<?= $lessonItem['id'] ?>" class="text-decoration-none">
                                                        📙 <?= htmlspecialchars($lessonItem['title']) ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script>
        const offcanvasEl = document.getElementById('tocOffcanvas');
        const toggleBtn = document.getElementById('tocToggleBtn');

        if (offcanvasEl && toggleBtn) {
            offcanvasEl.addEventListener('show.bs.offcanvas', () => {
                toggleBtn.style.display = 'none';
            });
            offcanvasEl.addEventListener('hidden.bs.offcanvas', () => {
                toggleBtn.style.display = 'block';
            });
        }
    </script>
<?php })(); ?>
