<?php

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['user']['id_user'])) {
    try {
        log_activity(
            $pdo,
            (int)$_SESSION['user']['id_user'],
            'Logout dari aplikasi'
        );
    } catch (Throwable $e) {
        // Proses logout tetap dilanjutkan jika pencatatan gagal.
    }
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

redirect('/parkir/auth/login.php');