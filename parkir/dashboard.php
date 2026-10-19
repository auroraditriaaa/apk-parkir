<?php

require_once __DIR__ . '/includes/auth.php';

$role = current_user()['role'];

if ($role === 'admin') {
    $totalUser = (int)$pdo->query(
        'SELECT COUNT(*) FROM tb_user'
    )->fetchColumn();

    $totalKendaraan = (int)$pdo->query(
        'SELECT COUNT(*) FROM tb_kendaraan'
    )->fetchColumn();

    $aktif = (int)$pdo->query(
        "SELECT COUNT(*) FROM tb_transaksi WHERE status = 'masuk'"
    )->fetchColumn();

    $pendapatan = (float)$pdo->query(
        "SELECT COALESCE(SUM(biaya_total), 0)
         FROM tb_transaksi
         WHERE status = 'keluar'
         AND DATE(waktu_keluar) = CURDATE()"
    )->fetchColumn();

} elseif ($role === 'petugas') {
    $totalUser = 0;

    $totalKendaraan = (int)$pdo->query(
        'SELECT COUNT(*) FROM tb_kendaraan'
    )->fetchColumn();

    $aktif = (int)$pdo->query(
        "SELECT COUNT(*) FROM tb_transaksi WHERE status = 'masuk'"
    )->fetchColumn();

    $pendapatan = (float)$pdo->query(
        "SELECT COALESCE(SUM(biaya_total), 0)
         FROM tb_transaksi
         WHERE status = 'keluar'
         AND DATE(waktu_keluar) = CURDATE()"
    )->fetchColumn();

} else {
    $totalUser = 0;

    $totalKendaraan = (int)$pdo->query(
        'SELECT COUNT(*) FROM tb_kendaraan'
    )->fetchColumn();

    $aktif = (int)$pdo->query(
        "SELECT COUNT(*) FROM tb_transaksi WHERE status = 'masuk'"
    )->fetchColumn();

    $pendapatan = (float)$pdo->query(
        "SELECT COALESCE(SUM(biaya_total), 0)
         FROM tb_transaksi
         WHERE status = 'keluar'"
    )->fetchColumn();
}

$recent = $pdo->query(
    "SELECT
        t.id_parkir,
        k.plat_nomor,
        k.jenis_kendaraan,
        t.waktu_masuk,
        t.waktu_keluar,
        t.biaya_total,
        t.status
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON k.id_kendaraan = t.id_kendaraan
     ORDER BY t.id_parkir DESC
     LIMIT 8"
)->fetchAll();

$pageTitle = 'Dashboard';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<div class="welcome">
    <h2>
        Halo, <?= e(current_user()['nama_lengkap']) ?>!
    </h2>

    <p>
        Selamat bekerja. Berikut ringkasan aktivitas sistem parkir.
    </p>
</div>

<div class="row g-3 mb-4">

    <div class="col-12 col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-car-front"></i>
            </div>

            <div>
                <div class="stat-label">Total Kendaraan</div>
                <div class="stat-value">
                    <?= number_format($totalKendaraan) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-p-square"></i>
            </div>

            <div>
                <div class="stat-label">Sedang Parkir</div>
                <div class="stat-value">
                    <?= number_format($aktif) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-cash-stack"></i>
            </div>

            <div>
                <div class="stat-label">
                    <?= $role === 'owner' ? 'Total Pendapatan' : 'Pendapatan Hari Ini' ?>
                </div>

                <div class="stat-value" style="font-size:19px">
                    <?= rupiah($pendapatan) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-person-badge"></i>
            </div>

            <div>
                <div class="stat-label">Level Akun</div>
                <div class="stat-value">
                    <?= e(ucfirst($role)) ?>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="panel">

    <div class="panel-head">
        <h2 class="section-heading mb-0">Transaksi Terbaru</h2>

        <?php if (in_array($role, ['admin', 'petugas'], true)): ?>
            <a
                class="btn btn-primary"
                href="<?= e(base_url('petugas/transaksi.php')) ?>"
            >
                Kelola Transaksi
            </a>
        <?php else: ?>
            <a
                class="btn btn-primary"
                href="<?= e(base_url('owner/laporan.php')) ?>"
            >
                Lihat Laporan
            </a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Plat Nomor</th>
                    <th>Jenis</th>
                    <th>Waktu Masuk</th>
                    <th>Status</th>
                    <th>Biaya</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($recent as $r): ?>
                    <tr>
                        <td>#<?= (int)$r['id_parkir'] ?></td>

                        <td>
                            <strong><?= e($r['plat_nomor']) ?></strong>
                        </td>

                        <td><?= e(ucfirst($r['jenis_kendaraan'])) ?></td>

                        <td><?= e($r['waktu_masuk']) ?></td>

                        <td>
                            <span class="badge text-bg-<?= $r['status'] === 'masuk' ? 'primary' : 'success' ?>">
                                <?= e(ucfirst($r['status'])) ?>
                            </span>
                        </td>

                        <td>
                            <?= $r['biaya_total'] === null
                                ? '-'
                                : rupiah($r['biaya_total']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$recent): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            Belum ada transaksi.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>