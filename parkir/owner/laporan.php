<?php

require_once __DIR__ . '/../includes/auth.php';
require_role(['owner']);

$today = date('Y-m-d');
$start = $_GET['mulai'] ?? date('Y-m-01');
$end = $_GET['sampai'] ?? $today;

if (
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end) ||
    $start > $end
) {
    $start = date('Y-m-01');
    $end = $today;
}

$q = $pdo->prepare(
    "SELECT
        t.id_parkir,
        k.plat_nomor,
        k.jenis_kendaraan,
        a.nama_area,
        t.waktu_masuk,
        t.waktu_keluar,
        t.durasi_jam,
        t.biaya_total,
        u.nama_lengkap AS petugas
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON k.id_kendaraan=t.id_kendaraan
     JOIN tb_area_parkir a ON a.id_area=t.id_area
     JOIN tb_user u ON u.id_user=t.id_user
     WHERE t.status='keluar'
       AND DATE(t.waktu_keluar) BETWEEN ? AND ?
     ORDER BY t.waktu_keluar DESC
     LIMIT 2000"
);

$q->execute([$start, $end]);
$rows = $q->fetchAll();

$total = 0;

foreach ($rows as $r) {
    $total += (float)$r['biaya_total'];
}

$pageTitle = 'Rekap Transaksi';

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>

<div class="panel">

    <h2 class="section-heading">Filter Periode Laporan</h2>

    <form class="filter-row mb-3" method="get">

        <div>
            <label class="form-label">Dari tanggal</label>
            <input
                class="form-control"
                type="date"
                name="mulai"
                value="<?= e($start) ?>"
                required
            >
        </div>

        <div>
            <label class="form-label">Sampai tanggal</label>
            <input
                class="form-control"
                type="date"
                name="sampai"
                value="<?= e($end) ?>"
                required
            >
        </div>

        <button class="btn btn-primary">Tampilkan</button>

        <a class="btn btn-outline-secondary" href="laporan.php">
            Bulan Ini
        </a>

        <button
            type="button"
            class="btn btn-outline-secondary"
            onclick="window.print()"
        >
            Cetak / Simpan PDF
        </button>

    </form>

    <div class="row g-3">

        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-receipt"></i>
                </div>

                <div>
                    <div class="stat-label">Transaksi Selesai</div>
                    <div class="stat-value"><?= count($rows) ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-8">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-cash-coin"></i>
                </div>

                <div>
                    <div class="stat-label">Total Pendapatan Periode</div>
                    <div class="stat-value"><?= rupiah($total) ?></div>
                </div>
            </div>
        </div>

    </div>

</div>

<div class="panel">

    <div class="panel-head">
        <h2 class="section-heading mb-0">Detail Transaksi</h2>

        <span class="badge text-bg-light">
            <?= e($start) ?> s.d. <?= e($end) ?>
        </span>
    </div>

    <div class="table-responsive">
        <table class="table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Plat</th>
                    <th>Jenis</th>
                    <th>Area</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Durasi</th>
                    <th>Biaya</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>#<?= (int)$r['id_parkir'] ?></td>
                        <td><?= e($r['plat_nomor']) ?></td>
                        <td><?= e(ucfirst($r['jenis_kendaraan'])) ?></td>
                        <td><?= e($r['nama_area']) ?></td>
                        <td><?= e($r['waktu_masuk']) ?></td>
                        <td><?= e($r['waktu_keluar']) ?></td>
                        <td><?= (int)$r['durasi_jam'] ?> jam</td>
                        <td><?= rupiah($r['biaya_total']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="8" class="empty-state">
                            Tidak ada transaksi selesai pada periode ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>

            <tfoot>
                <tr>
                    <th colspan="7" class="text-end">
                        TOTAL PENDAPATAN
                    </th>
                    <th><?= rupiah($total) ?></th>
                </tr>
            </tfoot>

        </table>
    </div>

    <small class="text-secondary">
        Maksimal 2.000 transaksi ditampilkan dalam satu laporan.
    </small>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>