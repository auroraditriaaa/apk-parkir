<?php

require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'petugas']);

$id = (int)($_GET['id'] ?? 0);

$q = $pdo->prepare(
    'SELECT
        t.*,
        k.plat_nomor,
        k.jenis_kendaraan,
        k.warna,
        k.pemilik,
        a.nama_area,
        tar.tarif_per_jam,
        u.nama_lengkap AS petugas
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON k.id_kendaraan=t.id_kendaraan
     JOIN tb_area_parkir a ON a.id_area=t.id_area
     JOIN tb_tarif tar ON tar.id_tarif=t.id_tarif
     JOIN tb_user u ON u.id_user=t.id_user
     WHERE t.id_parkir=?'
);

$q->execute([$id]);
$t = $q->fetch();

if (!$t) {
    http_response_code(404);
    exit('Struk tidak ditemukan.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Struk Parkir #<?= $id ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body style="padding:25px">

    <div class="no-print mb-3 text-center">
        <button class="btn btn-primary" onclick="window.print()">
            Cetak Struk
        </button>

        <a class="btn btn-outline-secondary" href="transaksi.php">
            Kembali
        </a>
    </div>

    <div class="ticket">

        <h2>
            <i class="bi bi-p-square-fill"></i>
            ParkirKu
        </h2>

        <p>Bukti Transaksi Parkir</p>

        <hr>

        <div class="d-flex justify-content-between">
            <span>No. Transaksi</span>
            <strong>#<?= (int)$t['id_parkir'] ?></strong>
        </div>

        <div class="d-flex justify-content-between">
            <span>Status</span>
            <strong><?= e(strtoupper($t['status'])) ?></strong>
        </div>

        <hr>

        <div class="d-flex justify-content-between">
            <span>Plat Nomor</span>
            <strong><?= e($t['plat_nomor']) ?></strong>
        </div>

        <div class="d-flex justify-content-between">
            <span>Jenis</span>
            <strong><?= e(ucfirst($t['jenis_kendaraan'])) ?></strong>
        </div>

        <div class="d-flex justify-content-between">
            <span>Area</span>
            <strong><?= e($t['nama_area']) ?></strong>
        </div>

        <div class="d-flex justify-content-between">
            <span>Waktu Masuk</span>
            <strong><?= e($t['waktu_masuk']) ?></strong>
        </div>

        <div class="d-flex justify-content-between">
            <span>Waktu Keluar</span>
            <strong><?= e($t['waktu_keluar'] ?? 'Belum keluar') ?></strong>
        </div>

        <div class="d-flex justify-content-between">
            <span>Tarif/Jam</span>
            <strong><?= rupiah($t['tarif_per_jam']) ?></strong>
        </div>

        <div class="d-flex justify-content-between">
            <span>Durasi</span>
            <strong>
                <?= $t['durasi_jam'] === null
                    ? '-'
                    : (int)$t['durasi_jam'] . ' jam' ?>
            </strong>
        </div>

        <hr>

        <div class="d-flex justify-content-between fs-5">
            <strong>TOTAL</strong>

            <strong>
                <?= $t['biaya_total'] === null
                    ? 'Belum dihitung'
                    : rupiah($t['biaya_total']) ?>
            </strong>
        </div>

        <hr>

        <p>
            Petugas: <?= e($t['petugas']) ?><br>
            Terima kasih telah menggunakan layanan parkir kami.
        </p>

    </div>

</body>
</html>