<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

$edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $act = $_POST['action'] ?? '';

    if ($act === 'save') {
        $id = (int)($_POST['id_area'] ?? 0);
        $nama = trim($_POST['nama_area'] ?? '');
        $kap = (int)($_POST['kapasitas'] ?? 0);

        if ($nama === '' || $kap < 1) {
            flash('danger', 'Nama dan kapasitas area wajib diisi.');
            redirect('area.php');
        }

        try {
            if ($id) {
                $q = $pdo->prepare(
                    'SELECT terisi FROM tb_area_parkir WHERE id_area=?'
                );
                $q->execute([$id]);
                $terisi = (int)$q->fetchColumn();

                if ($kap < $terisi) {
                    throw new RuntimeException(
                        'Kapasitas tidak boleh lebih kecil dari jumlah kendaraan yang sedang terparkir.'
                    );
                }

                $q = $pdo->prepare(
                    'UPDATE tb_area_parkir
                     SET nama_area=?, kapasitas=?
                     WHERE id_area=?'
                );
                $q->execute([$nama, $kap, $id]);
            } else {
                $q = $pdo->prepare(
                    'INSERT INTO tb_area_parkir
                     (nama_area, kapasitas, terisi)
                     VALUES (?, ?, 0)'
                );
                $q->execute([$nama, $kap]);
            }

            log_activity(
                $pdo,
                (int)current_user()['id_user'],
                'Menyimpan area ' . $nama
            );

            flash('success', 'Area parkir berhasil disimpan.');
        } catch (Throwable $e) {
            flash('danger', $e->getMessage());
        }

        redirect('area.php');
    }

    if ($act === 'delete') {
        try {
            $q = $pdo->prepare(
                'DELETE FROM tb_area_parkir WHERE id_area=?'
            );
            $q->execute([(int)$_POST['id_area']]);

            flash('success', 'Area berhasil dihapus.');
        } catch (Throwable $e) {
            flash(
                'danger',
                'Area tidak dapat dihapus karena memiliki riwayat transaksi.'
            );
        }

        redirect('area.php');
    }
}

if (isset($_GET['edit'])) {
    $q = $pdo->prepare(
        'SELECT * FROM tb_area_parkir WHERE id_area=?'
    );
    $q->execute([(int)$_GET['edit']]);
    $edit = $q->fetch() ?: null;
}

$rows = $pdo->query(
    'SELECT * FROM tb_area_parkir ORDER BY id_area'
)->fetchAll();

$pageTitle = 'Area Parkir';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="panel">
            <h2 class="section-heading">
                <?= $edit ? 'Edit Area' : 'Tambah Area' ?>
            </h2>

            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id_area"
                       value="<?= (int)($edit['id_area'] ?? 0) ?>">

                <div class="mb-3">
                    <label class="form-label">Nama Area</label>
                    <input class="form-control" name="nama_area" required
                           value="<?= e($edit['nama_area'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Kapasitas Maksimum</label>
                    <input class="form-control" type="number" min="1"
                           name="kapasitas" required
                           value="<?= e($edit['kapasitas'] ?? '50') ?>">
                </div>

                <button class="btn btn-primary">Simpan Area</button>

                <?php if ($edit): ?>
                    <a href="area.php" class="btn btn-outline-secondary">
                        Batal
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="panel">
            <h2 class="section-heading">Daftar Area</h2>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Area</th>
                            <th>Terisi</th>
                            <th>Kapasitas</th>
                            <th>Sisa</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= e($r['nama_area']) ?></td>
                                <td><?= (int)$r['terisi'] ?></td>
                                <td><?= (int)$r['kapasitas'] ?></td>
                                <td>
                                    <?= max(
                                        0,
                                        (int)$r['kapasitas'] - (int)$r['terisi']
                                    ) ?>
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="?edit=<?= (int)$r['id_area'] ?>">
                                        Edit
                                    </a>

                                    <form method="post" class="d-inline"
                                          onsubmit="return confirm('Hapus area ini?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action"
                                               value="delete">
                                        <input type="hidden" name="id_area"
                                               value="<?= (int)$r['id_area'] ?>">
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