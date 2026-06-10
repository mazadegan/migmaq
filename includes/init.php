<?php
require_once __DIR__ . '/../lib/logger.php';

$host = $_SERVER['HTTP_HOST'];
$domain = explode(':', $host)[0]; // remove port if present (for local dev)

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $domain,
        'secure' => true, // requires HTTPS in production
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}


// Load .env file and populate getenv()
$envPath = __DIR__ . '/../.env';

if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        // Set it for getenv(), $_ENV, and $_SERVER
        putenv("$key=$value");
    }
}

$dbFile = __DIR__ . '/data.db';
$pdo = new PDO('sqlite:' . __DIR__ . '/../data/data.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create users table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'contributor' -- can be 'admin' or 'contributor'
    );
");


// Create password resets table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS password_resets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL,
        token TEXT UNIQUE NOT NULL,
        expires_at DATETIME NOT NULL
    );
");


// Create units table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS units (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        title      TEXT NOT NULL,
        body       TEXT NOT NULL,
        status     TEXT NOT NULL DEFAULT 'draft',
        position   INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
");

// Create audios table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS audios (
        id       INTEGER PRIMARY KEY AUTOINCREMENT,
        filename TEXT NOT NULL,
        mime     TEXT NOT NULL,
        data     BLOB NOT NULL
    );
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS sections (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        unit_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        body TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'draft',
        position INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
    );
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS lessons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        section_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        body TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'draft',
        position INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE
    );
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL
    );
");

// Ensure default values are set
$defaultSettings = [
    'registration_enabled'   => '1',
    'site_name'              => "Learn Mi'gmaq Online",
    'site_color'             => '#f04515',
    'site_logo'              => 'logo.png',
    'home_hero'              => 'landscape.png',
    'social_facebook_enabled'=> '1',
    'social_facebook_url'    => 'https://www.facebook.com/groups/learnmigmaq',
    'social_twitter_enabled' => '1',
    'social_twitter_url'     => 'https://x.com/learnmigmaq',
    'social_instagram_enabled'=> '1',
    'social_instagram_url'   => 'https://www.instagram.com/learnmigmaq',
    'page_about_enabled'     => '1',
    'page_privacy_enabled'   => '1',
    'page_contact_enabled'   => '1',
    'page_welalieg_enabled'  => '1',
    'home_col_image_1'       => 'listuguj-logo.png',
    'home_col_image_1_alt'   => 'Listuguj Education Directorate',
    'home_col_image_2'       => 'mcgill-logo.png',
    'home_col_image_2_alt'   => 'McGill University',
    'home_col_image_3'       => 'concordia-logo.png',
    'home_col_image_3_alt'   => 'Concordia University',
    'home_body'              => "<h1>Learn Mi'gmaq Online</h1>\n<p class=\"lead\">This site helps you learn Mi'gmaq on your own or alongside classes.</p>\n<p>Each section includes units with vocabulary, dialogs, and practice exercises. You'll hear real Mi'gmaq speakers to train your ear and pronunciation.</p>\n<p>The lessons come from the Mi'gmaq Partnership between Listuguj Education Directorate, McGill, and Concordia. Many speakers are from Listuguj, so their accent may differ from your community's.</p>",
    'about_body'             => "<p>This website is designed to help you learn Mi'gmaq. You may already know some of the language, but want a chance to practice and improve your knowledge. Or you may have no exposure to the language at all. Because this website is self-guided, you can move through the material at the pace that is comfortable to you.</p>\n<p>Listening and speaking are crucial language skills. While we include some written exercises, this site is primarily meant to help you speak and understand Mi'gmaq, and each lesson includes recordings of Mi'gmaq speakers for you to listen to. Every time you listen to a recording, you should also practice saying it out loud. Gaining confidence in speaking is an important part of learning the language.</p>\n<p>These lessons were developed from class notes taken during Mary Ann Metallic's language classes at the Listuguj Education Directorate with Mary Ann Metallic. These notes were formulated into over one hundred lessons. Joe Wilmot designed and recorded audio files to accompany the lessons, with the help of many Mi'gmaq speakers.</p>\n<p>The material is arranged into sections, units, and lessons. Sections are divided thematically based on typical topics of conversation. Sections become increasingly advanced as more complex grammar is introduced. Units typically contain five to seven lessons, each focusing on a subtopic related to the section's theme. The final unit in each section is a review of previous material.</p>\n<p>A lesson is the smallest amount of material a user should study at one time. Lessons introduce new vocabulary, provide recorded dialogs to help you practice your listening and speaking skills, and test your knowledge with simple exercises.</p>",
    'privacy_body'           => "<p>Your privacy is important to us. This website complies with the General Data Protection Regulation (GDPR).</p>\n<h4 class=\"mt-4\">1. What We Collect</h4>\n<p>We collect only the information necessary for user registration and login, including your email address, username, and password (stored securely in hashed form).</p>\n<h4 class=\"mt-4\">2. Why We Collect This Information</h4>\n<p>We use this information solely to allow you to create an account, log in, and access protected parts of the website.</p>\n<h4 class=\"mt-4\">3. Cookies</h4>\n<p>We use only essential cookies to manage your login session. These are required for the site to function properly and are not used for tracking or advertising.</p>\n<h4 class=\"mt-4\">4. Data Storage</h4>\n<p>Your data is stored securely on our servers and is only accessible to authorized administrators. We do not share or sell your data to third parties.</p>\n<h4 class=\"mt-4\">5. Contact</h4>\n<p>If you have any questions about this policy or your data, please contact us at <a href=\"mailto:info@example.com\">info@example.com</a>.</p>",
    'contact_body'           => "<p>For questions or feedback, please email us at <a href=\"mailto:info@example.com\">info@example.com</a>.</p>",
];

$insertDefault = $pdo->prepare("INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)");
foreach ($defaultSettings as $key => $value) {
    $insertDefault->execute([$key, $value]);
}

// Load all settings into a global array for use in views via site()
$stmt = $pdo->query("SELECT key, value FROM settings");
$siteSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
