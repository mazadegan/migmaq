/** @type {import('./src/config').GlnConfig} */
module.exports = {
  "gitignore": true,
  "includePatterns": [
    "**/*.php",
    "!lib/PHPMailer"
  ],
  "blocks": {
    "bones": [
      "lib/sendmail.php",
      "includes/init.php",
      "includes/helpers.php",
      "controllers/UserController.php",
      "controllers/DashboardController.php",
      "controllers/AuthController.php",
      "models/Lesson.php",
      "models/Section.php",
      "models/Unit.php",
      "models/User.php",
      "public/index.php",
      "views/partials/content_navbar.php",
      "views/partials/dashboard_navbar.php",
      "views/partials/footer.php",
      "views/partials/head.php",
      "views/partials/lesson_modal.php",
      "views/partials/section_modal.php",
      "views/partials/toast.php",
      "views/partials/toc_offcanvas.php",
      "views/partials/unit_modal.php",
      "views/404.php",
      "views/account.php",
      "views/dashboard_home.php",
      "views/forgot_password.php",
      "views/landing.php",
      "views/login.php",
      "views/manage_users.php",
      "views/register.php",
      "views/reset_password.php"
    ]
  }
};
