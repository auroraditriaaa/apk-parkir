<?php

require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('transaksi.php');
}

verify_csrf();

$id = (int)($_POST['id_parkir'] ?? 0);
$uid = (int)current_user()['id_user'];

try {
    $pdo->beginTransaction();

    $q = $pdo->prepare(
        "SELECT t.*, k.plat_nomor, a.nama_area
         FROM tb_transaksi t
         JOIN tb_kendaraan k ON k.id_kendaraan=t.id_kendaraan
         JOIN tb_area_parkir a ON a.id_area=t.id_area
         WHERE t.id_parkir=?
         FOR UPDATE"
    );

    $q->execute([$id]);
    $t = $q->fetch();

    if (!$t || $t['status'] !== 'masuk') {
        throw new RuntimeException(
            'Transaksi tidak ditemukan atau sudah diproses keluar.'
        );
    }

    $q = $pdo->prepare(
        'SELECT tarif_per_jam FROM tb_tarif WHERE id_tarif=?'
    );
    $q->execute([$t['id_tarif']]);
    $tarif = $q->fetchColumn();

    if ($tarif === false) {
        throw new RuntimeException('Tarif transaksi tidak ditemukan.');
    }

    $seconds = max(0, time() - strtotime($t['waktu_masuk']));
    $hours = max(1, (int)ceil($seconds / 3600));
    $cost = $hours * (float)$tarif;

    $q = $pdo->prepare(
        "UPDATE tb_transaksi
         SET waktu_keluar=NOW(),
             durasi_jam=?,
             biaya_total=?,
             status='keluar'
         WHERE id_parkir=? AND status='masuk'"
    );

    $q->execute([$hours, $cost, $id]);

    if ($q->rowCount() !== 1) {
        throw new RuntimeException(
            'Transaksi sudah berubah. Muat ulang halaman.'
        );
    }

    $q = $pdo->prepare(
        'UPDATE tb_area_parkir
         SET terisi=GREATEST(terisi-1, 0)
         WHERE id_area=?'
    );
    $q->execute([$t['id_area']]);

    log_activity(
        $pdo,
        $uid,
        'Memproses kendaraan keluar: ' .
        $t['plat_nomor'] . ' biaya ' . $cost
    );

    $pdo->commit();

    flash(
        'success',
        "Kendaraan keluar berhasil. Durasi {$hours} jam, biaya " .
        rupiah($cost) . '.'
    );

    redirect('struk.php?id=' . $id);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    flash('danger', $e->getMessage());
    redirect('transaksi.php');
}