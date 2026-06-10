# Learn Mi'gmaq Online

This is a redesign of the original Learn Mi'gmaq website using PHP. It includes user registration and login, content creation and editing (units, sections, lessons) with a WYSIWYG editor, audio uploads, and administrative user management.

This guide walks you through how to work on the project locally.

---

## Getting Started

### Prerequisites

Before you start, make sure you have:

* **PHP 8.1+** installed (check with `php -v`)
* **SQLite**
* An email account with SMTP access (like a Gmail account with an App Password) to test password recovery emails.

---

## Running the Project Locally

1. Clone the repository or download the project folder.

2. Open a terminal and navigate to the project root.

3. Start the built-in PHP server:

```bash
php -S localhost:8000 -t public
```

4. Open your browser and go to:

```
http://localhost:8000
```

---

## Project Structure

```
├── controllers/       # Handles user requests (e.g. login, dashboard, contents)
├── models/            # Database abstraction for Units, Sections, Lessons, etc.
├── includes/          # Initialization and helper functions
├── views/             # HTML templates (split into partials and pages)
├── public/            # Entry point (index.php) and static assets
├── lib/               # Email logic, logging, and third-party libraries
├── data/              # SQLite database file is stored here
```

---

## Features Overview

### Public Pages

* `/` — Landing page
* `/contents` — Browse all Units, Sections, and Lessons
* `/unit?id=...` — View a specific Unit and its Sections
* `/section?id=...` — View a Section and its Lessons
* `/lesson?id=...` — View a single Lesson (with next/previous nav)

### Authentication

* `/register` — Sign up
* `/login` — Log in
* `/logout` — Log out
* `/forgot-password` and `/reset-password` — Password recovery

### Dashboard (requires login)

* `/dashboard` — Overview for logged-in users
* `/dashboard/unit-editor` — Add/edit/delete Units
* `/dashboard/section-editor?unitId=...` — Manage Sections inside a Unit
* `/dashboard/lesson-editor?unitId=...&sectionId=...` — Manage Lessons inside a Section
* `/dashboard/manage-users` — Admin-only user management
* `/dashboard/account` — Change your own password

### Audio Uploads

* Lessons and Sections use a WYSIWYG editor (SunEditor) that lets you upload audio directly. Audio is stored in the database as BLOBs. Images are converted to base64 and embedded in the HTML in the database.

---

## Admin Notes

* The Users table is empty on initialization. The first registered user is automatically an administrator. Every account registered after that is a contributor, but can be promoted by an admin.
* Admins can create/edit/delete users, change passwords, change user roles, and toggle public registration.
* User roles: `admin` and `contributor`

---

## Developer Notes

* Routing is handled manually in `public/index.php`
* Session setup is in `includes/init.php`
* The app uses `PHPMailer` (via `lib/sendmail.php`) for sending password reset emails
* SQLite is used locally — database schema is automatically initialized in `init.php`
* `views/partials/` contains reusable components like navbars and modals

### Logging

Activity logging is handled by `lib/logger.php`. It logs significant events (logins, content changes, user management actions) to `/var/log/migmaq.log`.

To set up the log file on the server:

```bash
sudo touch /var/log/migmaq.log && sudo chown www-data:www-data /var/log/migmaq.log
```

Log entries follow this format:

```
[2026-06-10 14:32:01] [unit.update] user:malek (id:1) title:Lesson 1 status:published
```

---

## Environment Variables

Create a `.env` file in the root with:

```
SMTP_USERNAME=your@email.com
SMTP_PASSWORD=your_smtp_app_password
```

## XML Import Script

There is a Python script in the root directory that imports and transforms content from the legacy `master.xml` file into the new SQLite database format. It:

- Parses the XML structure (sections > units > lessons)
- Converts `<note>` elements to HTML paragraphs
- Converts `<dialog>` and `<vocab>` blocks to HTML tables with embedded audio players
- Converts `<activity>` blocks to interactive HTML tables
- Inserts audio files as binary blobs in the audios table
- Reports any missing audio files
- Inserts the final structured content into units, sections, and lessons tables

To use it, run:

```sh
python migration.py
```

Make sure your `master.xml` and `audio/` folder are both present.

---

## Troubleshooting

If you run into issues:

* Check your PHP error logs
* Make sure the `data/` folder is writable (for SQLite)
* Ensure your SMTP credentials are valid, with no spaces in the app password
