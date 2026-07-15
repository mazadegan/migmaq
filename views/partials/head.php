<?php // views/partials/head.php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(site('site_name', "Learn Mi'gmaq Online")) ?></title>
    <link rel="stylesheet" href="/src/suneditor.min.css">
    <link rel="stylesheet" href="/src/github-markdown.css">

    <script src="/src/suneditor.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <link rel="icon" href="/assets/<?= htmlspecialchars(site('site_logo', 'logo.png')) ?>">
</head>
<body>