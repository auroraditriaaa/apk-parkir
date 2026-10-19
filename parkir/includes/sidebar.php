<?php

$role = $me['role'] ?? '';
?>

<aside class="sidebar">

    <a class="brand" href="<?= e(base_url('dashboard.php')) ?>">
        <span class="brand-icon">
            <i class="bi bi-p-square-fill"></i>
        </span>

        <span>
            ParkirKu
            <small>SMART PARKING</small>
        </span>
    </a>

    <div class="profile-mini">
        <div class="avatar">
            <?= e(strtoupper(substr($me['nama_lengkap'] ?? 'U', 0, 1))) ?>
        </div>

        <div>
            <strong><?= e($me['nama_lengkap'] ?? '') ?></strong>
            <small><?= e(ucfirst($role)) ?></small>
        </div>
    </div>

    <nav class="nav-menu">

        <a href="<?= e(base_url('dashboard.php')) ?>">
            <i class="bi bi-grid-1x2"></i>
            Dashboard
        </a>

        <?php if ($role === 'admin'): ?>

            <div class="nav-label">PENGELOLAAN</div>

            <a href="<?= e(base_url('admin/users.php')) ?>">
                <i class="bi bi-people"></i>
                Data User
            </a>

            <a href="<?= e(base_url('admin/tarif.php')) ?>">
                <i class="bi bi-cash-stack"></i>
                Tarif Parkir
            </a>

            <a href="<?= e(base_url('admin/area.php')) ?>">
                <i class="bi bi-p-square"></i>
                Area Parkir
            </a>

            <a href="<?= e(base_url('admin/kendaraan.php')) ?>">
                <i class="bi bi-car-front"></i>
                Kendaraan
            </a>

            <a href="<?= e(base_url('admin/log.php')) ?>">
                <i class="bi bi-clock-history"></i>
                Log Aktivitas
            </a>

        <?php endif; ?>

        <?php if (in_array($role, ['admin', 'petugas'], true)): ?>

            <div class="nav-label">OPERASIONAL</div>

            <a href="<?= e(base_url('petugas/transaksi.php')) ?>">
                <i class="bi bi-receipt"></i>
                Transaksi Parkir
            </a>

        <?php endif; ?>

        <?php if ($role === 'owner'): ?>

            <div class="nav-label">ANALISIS</div>

            <a href="<?= e(base_url('owner/laporan.php')) ?>">
                <i class="bi bi-bar-chart-line"></i>
                Rekap Transaksi
            </a>

        <?php endif; ?>

    </nav>

    <div class="sidebar-bottom">
        <a href="<?= e(base_url('auth/logout.php')) ?>">
            <i class="bi bi-box-arrow-left"></i>
            Keluar
        </a>
    </div>

</aside>

<main class="main-area">

    <header class="topbar">
        <div>
            <span class="topbar-kicker">SISTEM MANAJEMEN PARKIR</span>
            <h1><?= e($pageTitle) ?></h1>
        </div>

        <div class="top-user">
            <span class="online-dot"></span>
            <?= e($me['nama_lengkap'] ?? '') ?>
        </div>
    </header>

    <div class="page-content">

        <?php if ($notice): ?>
            <div class="alert alert-<?= e($notice['type']) ?> alert-dismissible fade show">
                <?= e($notice['message']) ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>
            </div>
        <?php endif; ?>