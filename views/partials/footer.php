<style>
    footer a:hover {
        text-decoration: underline !important;
    }
</style>
<footer class="mt-5 pt-4 pb-3 border-top text-bg-light">
    <div class="container">
        <div class="align-items-center justify-content-between row">
            <!-- Left side: Standard footer links -->
            <div class="mb-3 mb-md-0 col-md-6">
                <ul class="list-inline mb-0">
                    <?php if (site('page_about_enabled', '1') === '1'): ?>
                    <li class="list-inline-item"><a href="/about" class="text-muted text-decoration-none">About</a></li>
                    <?php endif; ?>
                    <?php if (site('page_privacy_enabled', '1') === '1'): ?>
                    <li class="list-inline-item"><a href="/privacy" class="text-muted text-decoration-none">Privacy Policy</a></li>
                    <?php endif; ?>
                    <?php if (site('page_contact_enabled', '1') === '1'): ?>
                    <li class="list-inline-item"><a href="/contact" class="text-muted text-decoration-none">Contact</a></li>
                    <?php endif; ?>
                    <?php if (site('page_welalieg_enabled', '1') === '1'): ?>
                    <li class="list-inline-item"><a href="/welalieg" class="text-muted text-decoration-none">Wela'lieg</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Right side: Admin/editor link -->
            <div class="text-md-end col-md-6">
                <a href="/dashboard" class="text-muted text-decoration-none">
                    Editor Dashboard
                </a>
            </div>
        </div>

        <!-- Bottom line -->
        <div class="mt-3 text-muted text-center small">
            &copy; <?= date('Y') ?> <?= htmlspecialchars(site('site_name', "Learn Mi'gmaq Online")) ?>. All rights reserved.
        </div>
    </div>
</footer>