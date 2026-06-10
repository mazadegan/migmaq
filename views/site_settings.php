<!DOCTYPE html>
<html>
<?php require __DIR__ . '/partials/head.php'; ?>

<body>
    <?php require __DIR__ . '/partials/dashboard_navbar.php'; ?>

    <?php
    $contentPages = [
        'home_body'    => 'Homepage Body',
        'about_body'   => 'About Page',
        'privacy_body' => 'Privacy Policy',
        'contact_body' => 'Contact Page',
    ];
    ?>

    <div class="mt-5 container">
        <h1>Site Settings</h1>
        <p class="text-muted">Configure branding, social links, page visibility, and content for this site.</p>

        <!-- Branding -->
        <div class="mt-4 card">
            <div class="card-header fw-semibold">Branding</div>
            <div class="card-body">
                <form action="/site-settings/save-branding" method="POST" enctype="multipart/form-data">
                    <?= csrf_input() ?>

                    <div class="mb-3 row align-items-center">
                        <label class="col-sm-3 col-form-label">Site Name</label>
                        <div class="col-sm-9">
                            <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars(site('site_name')) ?>">
                        </div>
                    </div>

                    <div class="mb-3 row align-items-center">
                        <label class="col-sm-3 col-form-label">Accent Color</label>
                        <div class="col-sm-9 d-flex align-items-center gap-2">
                            <input type="color" name="site_color" class="form-control form-control-color" value="<?= htmlspecialchars(site('site_color')) ?>">
                            <span class="text-muted small">Used for the navbar background.</span>
                        </div>
                    </div>

                    <div class="mb-3 row align-items-center">
                        <label class="col-sm-3 col-form-label">Logo</label>
                        <div class="col-sm-9 d-flex align-items-center gap-3">
                            <img src="/assets/<?= htmlspecialchars(site('site_logo')) ?>" alt="Logo" height="50">
                            <input type="file" name="logo_upload" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="mb-3 row align-items-center">
                        <label class="col-sm-3 col-form-label">Hero Banner</label>
                        <div class="col-sm-9">
                            <img src="/assets/<?= htmlspecialchars(site('home_hero')) ?>" alt="Hero" class="mb-2 img-fluid rounded" style="max-height:100px; object-fit:cover;">
                            <input type="file" name="hero_upload" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Branding</button>
                </form>
            </div>
        </div>

        <!-- Social Media -->
        <div class="mt-4 card">
            <div class="card-header fw-semibold">Social Media</div>
            <div class="card-body">
                <form action="/site-settings/save-social" method="POST">
                    <?= csrf_input() ?>

                    <?php
                    $socials = [
                        'facebook'  => ['Facebook',   'bi-facebook'],
                        'twitter'   => ['X (Twitter)', 'bi-twitter-x'],
                        'instagram' => ['Instagram',  'bi-instagram'],
                    ];
                    foreach ($socials as $platform => [$label, $icon]):
                        $enabledKey = "social_{$platform}_enabled";
                        $urlKey     = "social_{$platform}_url";
                    ?>
                    <div class="mb-3 row align-items-center">
                        <label class="col-sm-3 col-form-label">
                            <i class="bi <?= $icon ?> me-1"></i><?= $label ?>
                        </label>
                        <div class="col-sm-9 d-flex align-items-center gap-3">
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" name="<?= $enabledKey ?>" value="1" class="form-check-input"
                                    <?= site($enabledKey, '1') === '1' ? 'checked' : '' ?>>
                            </div>
                            <input type="url" name="<?= $urlKey ?>" class="form-control form-control-sm"
                                value="<?= htmlspecialchars(site($urlKey)) ?>" placeholder="https://...">
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn btn-primary">Save Social Links</button>
                </form>
            </div>
        </div>

        <!-- Column Images -->
        <div class="mt-4 card">
            <div class="card-header fw-semibold">Homepage Column Images</div>
            <div class="card-body">
                <p class="text-muted small">The three images shown in the right column on the homepage (e.g. partner logos).</p>
                <form action="/site-settings/save-column-images" method="POST" enctype="multipart/form-data">
                    <?= csrf_input() ?>

                    <?php for ($i = 1; $i <= 3; $i++):
                        $imgKey = "home_col_image_{$i}";
                        $altKey = "home_col_image_{$i}_alt";
                    ?>
                    <div class="mb-4 pb-3 border-bottom">
                        <div class="mb-2 fw-semibold text-muted small">Image <?= $i ?></div>
                        <div class="mb-2 row align-items-center">
                            <label class="col-sm-3 col-form-label">Current</label>
                            <div class="col-sm-9">
                                <img src="/assets/<?= htmlspecialchars(site($imgKey)) ?>"
                                    alt="<?= htmlspecialchars(site($altKey)) ?>"
                                    style="height:60px; object-fit:contain;">
                            </div>
                        </div>
                        <div class="mb-2 row align-items-center">
                            <label class="col-sm-3 col-form-label">Replace</label>
                            <div class="col-sm-9">
                                <input type="file" name="col_image_<?= $i ?>" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <label class="col-sm-3 col-form-label">Alt Text</label>
                            <div class="col-sm-9">
                                <input type="text" name="<?= $altKey ?>" class="form-control"
                                    value="<?= htmlspecialchars(site($altKey)) ?>">
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>

                    <button type="submit" class="btn btn-primary">Save Column Images</button>
                </form>
            </div>
        </div>

        <!-- Page Visibility -->
        <div class="mt-4 card">
            <div class="card-header fw-semibold">Page Visibility</div>
            <div class="card-body">
                <p class="text-muted small">Controls which pages appear in the footer navigation.</p>
                <form action="/site-settings/save-page-visibility" method="POST">
                    <?= csrf_input() ?>

                    <?php
                    $visibilityPages = [
                        'about'    => 'About',
                        'privacy'  => 'Privacy Policy',
                        'contact'  => 'Contact',
                        'welalieg' => "Wela'lieg",
                    ];
                    foreach ($visibilityPages as $key => $label):
                        $settingKey = "page_{$key}_enabled";
                    ?>
                    <div class="mb-2 d-flex align-items-center gap-3">
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" name="<?= $settingKey ?>" value="1" class="form-check-input"
                                <?= site($settingKey, '1') === '1' ? 'checked' : '' ?>>
                        </div>
                        <span><?= $label ?></span>
                    </div>
                    <?php endforeach; ?>

                    <button type="submit" class="mt-3 btn btn-primary">Save Visibility</button>
                </form>
            </div>
        </div>

        <!-- Page Content -->
        <div class="mt-4 card">
            <div class="card-header fw-semibold">Page Content</div>
            <div class="card-body">
                <?php foreach ($contentPages as $key => $label): ?>
                <div class="mb-3 d-flex align-items-center justify-content-between border-bottom pb-3">
                    <span class="fw-semibold"><?= $label ?></span>
                    <button type="button" class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#pageContentModal"
                        data-key="<?= $key ?>"
                        data-label="<?= htmlspecialchars($label) ?>">
                        <i class="bi bi-pen"></i> Edit
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- Page Content Modal -->
    <div class="modal modal-xl fade" id="pageContentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pageContentModalLabel">Edit Page</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="/site-settings/save-page-content" method="POST" id="pageContentForm">
                        <?= csrf_input() ?>
                        <input type="hidden" name="active_key" id="activeKey">
                        <?php foreach ($contentPages as $key => $label): ?>
                        <div class="page-editor-field" data-key="<?= $key ?>" style="display:none;">
                            <textarea name="<?= $key ?>" id="editor_<?= $key ?>"><?= htmlspecialchars(site($key)) ?></textarea>
                        </div>
                        <?php endforeach; ?>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="pageContentForm" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="top-0 position-fixed p-3 translate-middle-x start-50" style="z-index: 1100">
        <div id="toastMessage" class="shadow border-0 toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastBody"></div>
                <button type="button" class="m-auto me-2 btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        const editors = {};

        document.addEventListener('DOMContentLoaded', () => {
            <?php foreach (array_keys($contentPages) as $key): ?>
            editors['<?= $key ?>'] = SUNEDITOR.create(document.getElementById('editor_<?= $key ?>'), {
                height: '400px',
                buttonList: [
                    ['undo', 'redo'],
                    ['formatBlock'],
                    ['bold', 'underline', 'italic'],
                    ['list', 'align', 'horizontalRule'],
                    ['link'],
                    ['removeFormat'],
                    ['codeView'],
                ],
            });
            <?php endforeach; ?>

            const modal = document.getElementById('pageContentModal');

            modal.addEventListener('show.bs.modal', event => {
                const btn = event.relatedTarget;
                const key = btn.dataset.key;
                const label = btn.dataset.label;

                document.getElementById('pageContentModalLabel').textContent = 'Edit: ' + label;
                document.getElementById('activeKey').value = key;

                document.querySelectorAll('.page-editor-field').forEach(el => {
                    el.style.display = el.dataset.key === key ? '' : 'none';
                });

                // Refresh the editor so it renders correctly after being shown
                if (editors[key]) {
                    editors[key].refresh();
                }
            });

            document.getElementById('pageContentForm').addEventListener('submit', () => {
                const key = document.getElementById('activeKey').value;
                if (editors[key]) {
                    document.getElementById('editor_' + key).value = editors[key].getContents(false);
                }
            });
        });
    </script>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'saved'): ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const toastEl = document.getElementById('toastMessage');
            const toastBody = document.getElementById('toastBody');
            toastEl.classList.add('bg-white', 'text-dark');
            toastBody.textContent = 'Settings saved!';
            new bootstrap.Toast(toastEl, { delay: 3000 }).show();
        });
    </script>
    <?php endif; ?>

</body>
</html>
