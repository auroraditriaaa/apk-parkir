<?php

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['user'])) {
    redirect('/parkir/dashboard.php');
}

$error = '';

/* DATA TOTAL PARKIR */
$kapasitasParkir = (int) $pdo->query(
    "SELECT COALESCE(SUM(kapasitas), 0)
     FROM tb_area_parkir"
)->fetchColumn();

$slotTerisi = (int) $pdo->query(
    "SELECT COALESCE(SUM(terisi), 0)
     FROM tb_area_parkir"
)->fetchColumn();

$slotTersedia = max(0, $kapasitasParkir - $slotTerisi);

$persentaseTersedia = $kapasitasParkir > 0
    ? min(100, round(($slotTersedia / $kapasitasParkir) * 100))
    : 0;

/* DATA PARKIR MOTOR */
$stmtMotor = $pdo->prepare("
    SELECT
        COALESCE(SUM(kapasitas), 0) AS kapasitas,
        COALESCE(SUM(terisi), 0) AS terisi
    FROM tb_area_parkir
    WHERE LOWER(nama_area) LIKE '%motor%'
");
$stmtMotor->execute();
$dataMotor = $stmtMotor->fetch(PDO::FETCH_ASSOC);

$kapasitasMotor = (int) $dataMotor['kapasitas'];
$terisiMotor = (int) $dataMotor['terisi'];
$tersediaMotor = max(0, $kapasitasMotor - $terisiMotor);

$persenMotor = $kapasitasMotor > 0
    ? min(100, round(($tersediaMotor / $kapasitasMotor) * 100))
    : 0;

/* DATA PARKIR MOBIL */
$stmtMobil = $pdo->prepare("
    SELECT
        COALESCE(SUM(kapasitas), 0) AS kapasitas,
        COALESCE(SUM(terisi), 0) AS terisi
    FROM tb_area_parkir
    WHERE LOWER(nama_area) LIKE '%mobil%'
");
$stmtMobil->execute();
$dataMobil = $stmtMobil->fetch(PDO::FETCH_ASSOC);

$kapasitasMobil = (int) $dataMobil['kapasitas'];
$terisiMobil = (int) $dataMobil['terisi'];
$tersediaMobil = max(0, $kapasitasMobil - $terisiMobil);

$persenMobil = $kapasitasMobil > 0
    ? min(100, round(($tersediaMobil / $kapasitasMobil) * 100))
    : 0;

/* PROSES LOGIN ASLI */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    $stmt = $pdo->prepare(
        'SELECT id_user, nama_lengkap, username, password, role, status_aktif
         FROM tb_user
         WHERE username = ?
         LIMIT 1'
    );

    $stmt->execute([$username]);
    $account = $stmt->fetch();

    $valid = false;

    if ($account && (int) $account['status_aktif'] === 1) {
        $hash = (string) $account['password'];

        if (password_verify($password, $hash)) {
            $valid = true;
        } elseif (
            preg_match('/^[a-f0-9]{64}$/i', $hash) &&
            hash('sha256', $password) === strtolower($hash)
        ) {
            $valid = true;

            $newHash = password_hash($password, PASSWORD_DEFAULT);

            $up = $pdo->prepare(
                'UPDATE tb_user SET password = ? WHERE id_user = ?'
            );

            $up->execute([$newHash, $account['id_user']]);
        }
    }

    if ($valid) {
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id_user' => (int) $account['id_user'],
            'nama_lengkap' => $account['nama_lengkap'],
            'username' => $account['username'],
            'role' => $account['role'],
            'status_aktif' => (int) $account['status_aktif']
        ];

        log_activity(
            $pdo,
            (int) $account['id_user'],
            'Login ke aplikasi'
        );

        redirect('/parkir/dashboard.php');
    }

    $error = (
        $account && (int) $account['status_aktif'] !== 1
    )
        ? 'Akun tidak aktif. Hubungi admin.'
        : 'Username atau password salah.';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login | ParkirKu</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: #fff;
            background: linear-gradient(
                135deg,
                #582842 0%,
                #80445f 55%,
                #914c6c 100%
            );
        }

        /* LAYOUT UTAMA */
        .auth-page {
            width: 100%;
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1.7fr) minmax(320px, 0.8fr);
            align-items: start;
            gap: 40px;
            padding: 40px 6%;
            margin: 0 auto;
        }

        .auth-brand {
            width: 100%;
            max-width: none;
            min-width: 0;
            color: #fff;
        }

        .big-icon {
            width: 76px;
            height: 76px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 22px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.25);
        }

        .big-icon i {
            font-size: 40px;
        }

        .auth-brand > h1 {
            margin: 24px 0 12px;
            font-size: clamp(48px, 6vw, 72px);
            font-weight: 800;
            letter-spacing: -2px;
            color: #fff;
        }

        .auth-brand > p {
            max-width: 850px;
            margin-bottom: 36px;
            color: #f8eaf1;
            font-size: 16px;
            line-height: 1.8;
        }

        /* KARTU PARKIR */
        .login-parking-card,
        .vehicle-parking-card {
            background: rgba(52,19,40,.28);
            border: 1px solid rgba(255,255,255,.27);
            border-radius: 24px;
            color: #fff;
        }

        .login-parking-card {
            width: 85%;
            max-width: 850px;
            padding: 24px 26px;
            margin-right: auto;
        }

        .parking-card-top {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 30px;
        }

        .parking-icon,
        .vehicle-icon {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg,#e78bb7,#b84d87);
            color: white;
            border-radius: 16px;
        }

        .parking-icon {
            width: 64px;
            height: 64px;
            font-size: 32px;
        }

        .parking-info {
            flex: 1;
        }

        .parking-label {
            display: block;
            margin-bottom: 6px;
            color: #f2b4d2;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
        }

        .parking-info h3 {
            margin: 0 0 7px;
            color: white;
            font-size: 25px;
            font-weight: 750;
        }

        .parking-info p {
            margin: 0;
            color: #ead4e1;
            font-size: 14px;
        }

        .parking-live {
            padding: 9px 15px;
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 24px;
            white-space: nowrap;
        }

        .live-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            margin-right: 5px;
            border-radius: 50%;
            background: #83f1b2;
        }

        .parking-count,
        .vehicle-count {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 18px;
        }

        .parking-count strong {
            display: block;
            color: #fff;
            font-size: 46px;
            font-weight: 800;
            line-height: 1.1;
        }

        .parking-count span,
        .vehicle-count span {
            display: block;
            margin-top: 5px;
            color: #f0dce7;
            font-size: 14px;
        }

        .parking-total,
        .vehicle-total {
            text-align: right;
        }

        .parking-progress {
            height: 9px;
            overflow: hidden;
            margin-bottom: 18px;
            border-radius: 20px;
            background: rgba(232,173,204,.3);
        }

        .parking-progress-fill {
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg,#f3a3cc,#e976b1);
            transition: width .3s ease;
        }

        .parking-card-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding-top: 17px;
            border-top: 1px solid rgba(255,255,255,.2);
            color: #f5e5ee;
            font-size: 13px;
        }

        .parking-card-bottom i {
            margin-right: 6px;
            color: #f5a9ce;
        }

        /* KARTU MOTOR DAN MOBIL */
        .vehicle-parking-grid {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 18px;
            width: 100%;
            margin-top: 18px;
        }

        .vehicle-parking-card {
            min-width: 0;
            padding: 22px;
        }

        .vehicle-heading {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 24px;
        }

        .vehicle-icon {
            width: 60px;
            height: 60px;
            font-size: 29px;
        }

        .vehicle-heading h3 {
            margin: 0 0 6px;
            color: white;
            font-size: 22px;
            font-weight: 750;
        }

        .vehicle-heading p {
            margin: 0;
            color: #ead4e1;
            font-size: 13px;
            line-height: 1.5;
        }

        .vehicle-count strong {
            display: block;
            color: #fff;
            font-size: 36px;
            font-weight: 800;
            line-height: 1.1;
        }

        .vehicle-total strong {
            font-size: 29px;
        }

        .vehicle-footer {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            padding-top: 16px;
            border-top: 1px solid rgba(255,255,255,.2);
            color: #f5e5ee;
            font-size: 12px;
        }

        .vehicle-footer i {
            margin-right: 5px;
            color: #f5a9ce;
        }

        .vehicle-status {
            color: #f5b8d5;
        }

        /* FORM LOGIN */
        .auth-box {
            width: 100%;
            max-width: 500px;
            justify-self: end;
            align-self: start;
            margin-top: 0;
        }

        .auth-card {
            padding: 30px;
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 24px;
            background: rgba(52,19,40,.35);
            color: white;
            box-shadow: 0 15px 40px rgba(30,8,22,.12);
        }

        .auth-card h2 {
            color: white;
        }

        .auth-card .muted,
        .auth-card .login-help,
        .auth-card .form-label {
            color: #f0dce7;
        }

        .auth-card .form-control {
            min-height: 48px;
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 12px;
            background: rgba(255,255,255,.08);
            color: white;
        }

        .auth-card .form-control::placeholder {
            color: #d8baca;
        }

        .auth-card .form-control:focus {
            border-color: #f3a3cc;
            box-shadow: 0 0 0 .2rem rgba(243,163,204,.18);
        }

        .auth-card .btn-primary {
            border: none;
            border-radius: 12px;
            background: linear-gradient(90deg,#d96da5,#b64e87);
        }

        .auth-card .btn-primary:hover {
            background: linear-gradient(90deg,#e580b4,#c65b96);
        }

        /* LOGIN SEJAJAR DENGAN KARTU SLOT PARKIR */
        @media (min-width: 901px) {
            .auth-box {
                margin-top: 257px;
            }
        }

        /* TAMPILAN HP DAN TABLET */
        @media (max-width: 900px) {
            .auth-page {
                grid-template-columns: 1fr;
                align-items: stretch;
                padding: 25px 16px;
                gap: 25px;
            }

            .auth-brand {
                max-width: 100%;
            }

            .auth-box {
                max-width: 500px;
                justify-self: start;
                margin-top: 0;
            }

            .auth-brand > h1 {
                font-size: 46px;
            }

            .auth-brand > p {
                font-size: 14px;
            }

            .vehicle-parking-grid {
                grid-template-columns: 1fr;
            }

            .login-parking-card {
                padding: 20px;
            }

            .parking-card-top {
                flex-wrap: wrap;
            }

            .parking-info h3 {
                font-size: 20px;
            }

            .parking-count strong {
                font-size: 38px;
            }

            .vehicle-parking-card {
                padding: 20px;
            }

            .parking-card-bottom {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

<div class="auth-page">

    <!-- BAGIAN BRANDING DAN INFORMASI PARKIR -->
    <section class="auth-brand">

        <div class="big-icon">
            <i class="bi bi-p-square-fill"></i>
        </div>

        <h1>ParkirKu</h1>

        <p>
            Sistem manajemen parkir untuk mencatat kendaraan masuk dan
            keluar, mengelola data parkir, serta melihat laporan transaksi.
        </p>

        <!-- KARTU TOTAL PARKIR -->
        <div class="login-parking-card">

            <div class="parking-card-top">

                <div class="parking-icon">
                    <i class="bi bi-p-square-fill"></i>
                </div>

                <div class="parking-info">
                    <span class="parking-label">
                        KETERSEDIAAN PARKIR
                    </span>

                    <h3>Slot Parkir Tersedia</h3>

                    <p>Informasi kapasitas area parkir saat ini</p>
                </div>

                <span class="parking-live">
                    <span class="live-dot"></span>
                    Live
                </span>

            </div>

            <div class="parking-count">
                <div>
                    <strong><?= number_format($slotTersedia) ?></strong>
                    <span>Slot tersedia</span>
                </div>

                <div class="parking-total">
                    <strong><?= number_format($kapasitasParkir) ?></strong>
                    <span>Total kapasitas</span>
                </div>
            </div>

            <div class="parking-progress">
                <div
                    class="parking-progress-fill"
                    style="width: <?= $persentaseTersedia ?>%;"
                ></div>
            </div>

            <div class="parking-card-bottom">
                <span>
                    <i class="bi bi-car-front-fill"></i>
                    <?= number_format($slotTerisi) ?>
                    kendaraan sedang parkir
                </span>

                <span>
                    <?php if ($slotTersedia > 0): ?>
                        <i class="bi bi-check-circle-fill"></i>
                        Masih tersedia
                    <?php else: ?>
                        <i class="bi bi-exclamation-circle-fill"></i>
                        Parkir penuh
                    <?php endif; ?>
                </span>
            </div>

        </div>

        <!-- KARTU MOTOR DAN MOBIL -->
        <div class="vehicle-parking-grid">

            <!-- MOTOR -->
            <div class="vehicle-parking-card">

                <div class="vehicle-heading">
                    <div class="vehicle-icon">
                        <i class="bi bi-bicycle"></i>
                    </div>

                    <div>
                        <h3>Motor</h3>
                        <p>Kapasitas dan ketersediaan slot motor</p>
                    </div>
                </div>

                <div class="vehicle-count">
                    <div>
                        <strong><?= number_format($tersediaMotor) ?></strong>
                        <span>Slot tersedia</span>
                    </div>

                    <div class="vehicle-total">
                        <strong><?= number_format($kapasitasMotor) ?></strong>
                        <span>Total kapasitas</span>
                    </div>
                </div>

                <div class="parking-progress">
                    <div
                        class="parking-progress-fill"
                        style="width: <?= $persenMotor ?>%;"
                    ></div>
                </div>

                <div class="vehicle-footer">
                    <span>
                        <i class="bi bi-bicycle"></i>
                        <?= number_format($terisiMotor) ?>
                        motor sedang parkir
                    </span>

                    <span class="vehicle-status">
                        <?= $tersediaMotor > 0
                            ? '<i class="bi bi-check-circle-fill"></i> Masih tersedia'
                            : '<i class="bi bi-exclamation-circle-fill"></i> Parkir penuh' ?>
                    </span>
                </div>

            </div>

            <!-- MOBIL -->
            <div class="vehicle-parking-card">

                <div class="vehicle-heading">
                    <div class="vehicle-icon">
                        <i class="bi bi-car-front-fill"></i>
                    </div>

                    <div>
                        <h3>Mobil</h3>
                        <p>Kapasitas dan ketersediaan slot mobil</p>
                    </div>
                </div>

                <div class="vehicle-count">
                    <div>
                        <strong><?= number_format($tersediaMobil) ?></strong>
                        <span>Slot tersedia</span>
                    </div>

                    <div class="vehicle-total">
                        <strong><?= number_format($kapasitasMobil) ?></strong>
                        <span>Total kapasitas</span>
                    </div>
                </div>

                <div class="parking-progress">
                    <div
                        class="parking-progress-fill"
                        style="width: <?= $persenMobil ?>%;"
                    ></div>
                </div>

                <div class="vehicle-footer">
                    <span>
                        <i class="bi bi-car-front-fill"></i>
                        <?= number_format($terisiMobil) ?>
                        mobil sedang parkir
                    </span>

                    <span class="vehicle-status">
                        <?= $tersediaMobil > 0
                            ? '<i class="bi bi-check-circle-fill"></i> Masih tersedia'
                            : '<i class="bi bi-exclamation-circle-fill"></i> Parkir penuh' ?>
                    </span>
                </div>

            </div>

        </div>

    </section>

    <!-- FORM LOGIN ASLI -->
    <section class="auth-box">

        <div class="auth-card">

            <h2>Selamat datang!</h2>

            <p class="muted">
                Masuk menggunakan akun yang telah terdaftar.
            </p>

            <?php if (isset($_GET['nonaktif'])): ?>
                <div class="alert alert-warning">
                    Akun Anda tidak aktif.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="post">

                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">Username</label>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Masukkan username"
                    >
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                    >
                </div>

                <button
                    class="btn btn-primary w-100 py-3"
                    type="submit"
                >
                    Masuk ke Dashboard
                    <i class="bi bi-arrow-right ms-1"></i>
                </button>

            </form>

            <div class="login-help mt-4">
                Gunakan username dan password yang sudah terdaftar
                di database.
            </div>

        </div>

    </section>

</div>

</body>
</html>