<?php

function e($value): string
{
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function rupiah($angka): string
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function take_flash(): ?array
{
    $data = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $data;
}

function csrf_field(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return '<input type="hidden" name="csrf_token" value="' .
        e($_SESSION['csrf_token']) . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            http_response_code(419);
            exit('Permintaan tidak valid. Silakan kembali dan coba lagi.');
        }
    }
}

function log_activity(
    PDO $pdo,
    int $userId,
    string $activity
): void {
    $stmt = $pdo->prepare(
        'INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES (?, ?)'
    );

    $stmt->execute([
        $userId,
        mb_substr($activity, 0, 100)
    ]);
}

function current_user(): array
{
    return $_SESSION['user'] ?? [];
}

function require_login(): void
{
    if (empty($_SESSION['user'])) {
        redirect('/parkir/auth/login.php');
    }

    if ((int)($_SESSION['user']['status_aktif'] ?? 0) !== 1) {
        session_destroy();
        redirect('/parkir/auth/login.php?nonaktif=1');
    }
}

function require_role(array $roles): void
{
    require_login();

    if (!in_array($_SESSION['user']['role'] ?? '', $roles, true)) {
        http_response_code(403);
        exit('Akses ditolak. Akun tidak memiliki izin.');
    }
}

function base_url(string $path = ''): string
{
    return '/parkir/' . ltrim($path, '/');
}