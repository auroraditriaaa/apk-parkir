<?php

require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('transaksi.php');
}

verify_csrf();

$idK = (int)($_POST['id_kendaraan'] ?? 0);
$idA = (int)($_POST['id_area'] ?? 0);
$uid = (int)current_user()['id_user'];

try {
    $pdo->beginTransaction();

    $q = $pdo->prepare(
        'SELECT * FROM tb_kendaraan WHERE id_kendaraan=?'
    );
    $q->execute([$idK]);
    $k = $q->fetch();

    if (!$k) {
        throw new RuntimeException('Kendaraan tidak ditemukan.');
    }

    $q = $pdo->prepare(
        "SELECT id_parkir
         FROM tb_transaksi
         WHERE id_kendaraan=? AND status='masuk'
         LIMIT 1"
    );
    $q->execute([$idK]);

    if ($q->fetch()) {
        throw new RuntimeException(
            'Kendaraan ini masih memiliki transaksi parkir aktif.'
        );
    }

    $q = $pdo->prepare(
        'SELECT * FROM tb_area_parkir WHERE id_area=? FOR UPDATE'
    );
    $q->execute([$idA]);
    $a = $q->fetch();

    if (!$a) {
        throw new RuntimeException('Area parkir tidak ditemukan.');
    }

    if ((int)$a['terisi'] >= (int)$a['kapasitas']) {
        throw new RuntimeException('Area parkir sudah penuh.');
    }

    $q = $pdo->prepare(
        'SELECT id_tarif FROM tb_tarif
         WHERE jenis_kendaraan=? LIMIT 1'
    );
    $q->execute([$k['jenis_kendaraan']]);
    $idTarif = $q->fetchColumn();

    if (!$idTarif) {
        throw new RuntimeException(
            'Tarif untuk jenis kendaraan ini belum tersedia.'
        );
    }

    $q = $pdo->prepare(
        "INSERT INTO tb_transaksi
         (id_kendaraan, waktu_masuk, id_tarif, status, id_user, id_area)
         VALUES (?, NOW(), ?, 'masuk', ?, ?)"
    );

    $q->execute([$idK, $idTarif, $uid, $idA]);

    $q = $pdo->prepare(
        'UPDATE tb_area_parkir SET terisi=terisi+1 WHERE id_area=?'
    );
    $q->execute([$idA]);

    log_activity(
        $pdo,
        $uid,
        'Mencatat kendaraan masuk: ' . $k['plat_nomor']
    );

    $pdo->commit();

    flash('success', 'Kendaraan masuk berhasil dicatat.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    flash('danger', $e->getMessage());
}

redirect('transaksi.php');