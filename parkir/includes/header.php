<?php

require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Dashboard';
$me = current_user();
$notice = take_flash();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($pageTitle) ?> | ParkirKu</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <link href="<?= e(base_url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<div class="app-shell">