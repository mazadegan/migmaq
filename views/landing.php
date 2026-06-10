<!DOCTYPE html>
<html>
<?php require __DIR__ . '/partials/head.php'; ?>

<body class="d-flex flex-column min-vh-100">

    <header>
        <nav class="px-2 py-0 navbar" style="background-color: <?= htmlspecialchars(site('site_color', '#f04515')) ?>;">
            <div class="d-flex align-items-center justify-content-between container-fluid">
                <!-- Brand -->
                <a href="/" class="d-flex align-items-center mb-0 text-white navbar-brand">
                    <img src="/assets/<?= htmlspecialchars(site('site_logo', 'logo.png')) ?>" alt="Logo" height="75" class="me-2 py-0">
                    <?= htmlspecialchars(site('site_name', "Learn Mi'gmaq Online")) ?>
                </a>

                <!-- Social Icons -->
                <div class="d-flex gap-3">
                    <?php if (site('social_facebook_enabled', '1') === '1'): ?>
                    <a href="<?= htmlspecialchars(site('social_facebook_url')) ?>" class="text-white fs-4" title="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (site('social_twitter_enabled', '1') === '1'): ?>
                    <a href="<?= htmlspecialchars(site('social_twitter_url')) ?>" class="text-white fs-4" title="X (Twitter)">
                        <i class="bi bi-twitter-x"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (site('social_instagram_enabled', '1') === '1'): ?>
                    <a href="<?= htmlspecialchars(site('social_instagram_url')) ?>" class="text-white fs-4" title="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main class="flex-grow-1">
        <div class="hero-image" style="
        background-image: url('/assets/<?= htmlspecialchars(site('home_hero', 'landscape.png')) ?>');
        background-size: cover;
        background-position: center 50%;
        height: 250px;">
        </div>

        <div class="mt-5 container">
            <div class="align-items-center row">
                <!-- Text Content -->
                <div class="mb-4 mb-md-0 text-md-start text-center col-md-8">
                    <?= site('home_body') ?>
                    <a href="/contents" class="mt-3 btn btn-secondary btn-lg">
                        Browse Table of Contents
                    </a>
                </div>

                <!-- Column Images -->
                <div class="text-center col-md-4">
                    <div class="d-flex flex-column align-items-center gap-4">
                        <?php for ($i = 1; $i <= 3; $i++):
                            $img = site("home_col_image_{$i}");
                            $alt = site("home_col_image_{$i}_alt");
                            if ($img):
                        ?>
                        <img src="/assets/<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($alt) ?>" style="width: 200px; object-fit: contain;">
                        <?php endif; endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/partials/footer.php'; ?>

</body>
</html>
