<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

$edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $act = $_POST['action'] ?? '';

    if ($act === 'save') {
        $id = (int)($_POST['id_tarif'] ?? 0);
        $jenis = $_POST['jenis_kendaraan'] ?? '';
        $biaya = (float)($_POST['tarif_per_jam'] ?? 0);

        if (
            !in_array($jenis, ['motor', 'mobil', 'lainnya'], true) ||
            $biaya < 0
        ) {
            flash('danger', 'Data tarif tidak valid.');
            redirect('tarif.php');
        }

        try {
            if ($id) {
                $q = $pdo->prepare(
                    'UPDATE tb_tarif
                     SET jenis_kendaraan=?, tarif_per_jam=?
                     WHERE id_tarif=?'
                );
                $q->execute([$jenis, $biaya, $id]);
            } else {
                $q = $pdo->prepare(
                    'INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam)
                     VALUES (?, ?)'
                );
                $q->execute([$jenis, $biaya]);
            }

            log_activity(
                $pdo,
                (int)current_user()['id_user'],
                'Menyimpan tarif ' . $jenis
            );

            flash('success', 'Tarif berhasil disimpan.');
        } catch (Throwable $e) {
            flash('danger', 'Gagal menyimpan tarif. Periksa database.');
        }

        redirect('tarif.php');
    }

    if ($act === 'delete') {
        try {
            $q = $pdo->prepare(
                'DELETE FROM tb_tarif WHERE id_tarif=?'
            );
            $q->execute([(int)$_POST['id_tarif']]);

            flash('success', 'Tarif berhasil dihapus.');
        } catch (Throwable $e) {
            flash(
                'danger',
                'Tarif tidak dapat dihapus karena sudah digunakan transaksi.'
            );
        }

        redirect('tarif.php');
    }
}

if (isset($_GET['edit'])) {
    $q = $pdo->prepare(
        'SELECT * FROM tb_tarif WHERE id_tarif=?'
    );
    $q->execute([(int)$_GET['edit']]);
    $edit = $q->fetch() ?: null;
}

$rows = $pdo->query(
    'SELECT * FROM tb_tarif ORDER BY id_tarif'
)->fetchAll();

$pageTitle = 'Tarif Parkir';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="panel">
            <h2 class="section-heading">
                <?= $edit ? 'Edit Tarif' : 'Tambah Tarif' ?>
            </h2>

            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id_tarif"
                       value="<?= (int)($edit['id_tarif'] ?? 0) ?>">

                <div class="mb-3">
                    <label class="form-label">Jenis Kendaraan</label>
                    <select name="jenis_kendaraan" class="form-select">
                        <?php foreach (['motor', 'mobil', 'lainnya'] as $j): ?>
                            <option value="<?= e($j) ?>"
                                <?= ($edit['jenis_kendaraan'] ?? 'motor') === $j
                                    ? 'selected' : '' ?>>
                                <?= e(ucfirst($j)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tarif per Jam (Rp)</label>
                    <input type="number" min="0" step="1"
                           name="tarif_per_jam" class="form-control"
                           required
                           value="<?= e($edit['tarif_per_jam'] ?? '2000') ?>">
                </div>

                <button class="btn btn-primary">Simpan Tarif</button>

                <?php if ($edit): ?>
                    <a href="tarif.php" class="btn btn-outline-secondary">
                        Batal
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="panel">
            <h2 class="section-heading">Daftar Tarif</h2>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Jenis</th>
                            <th>Tarif/Jam</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= e(ucfirst($r['jenis_kendaraan'])) ?></td>
                                <td><?= rupiah($r['tarif_per_jam']) ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="?edit=<?= (int)$r['id_tarif'] ?>">
                                        Edit
                                    </a>

                                    <form method="post" class="d-inline"
                                          onsubmit="return confirm('Hapus tarif ini?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action"
                                               value="delete">
                                        <input type="hidden" name="id_tarif"
                                               value="<?= (int)$r['id_tarif'] ?>">
                                        <button class="btn btn-sm btn-outline-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>