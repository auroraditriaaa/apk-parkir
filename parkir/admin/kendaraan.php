<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

$edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $act = $_POST['action'] ?? '';

    if ($act === 'save') {
        $id = (int)($_POST['id_kendaraan'] ?? 0);
        $plat = strtoupper(trim($_POST['plat_nomor'] ?? ''));
        $jenis = $_POST['jenis_kendaraan'] ?? '';
        $warna = trim($_POST['warna'] ?? '');
        $pemilik = trim($_POST['pemilik'] ?? '');
        $idUser = (int)($_POST['id_user'] ?? 0);

        if (
            $plat === '' ||
            !in_array($jenis, ['motor', 'mobil', 'lainnya'], true) ||
            $idUser < 1
        ) {
            flash('danger', 'Lengkapi data kendaraan dengan benar.');
            redirect('kendaraan.php');
        }

        try {
            if ($id) {
                $q = $pdo->prepare(
                    'UPDATE tb_kendaraan
                     SET plat_nomor=?, jenis_kendaraan=?, warna=?,
                         pemilik=?, id_user=?
                     WHERE id_kendaraan=?'
                );

                $q->execute([
                    $plat,
                    $jenis,
                    $warna ?: null,
                    $pemilik ?: null,
                    $idUser,
                    $id
                ]);
            } else {
                $q = $pdo->prepare(
                    'INSERT INTO tb_kendaraan
                     (plat_nomor, jenis_kendaraan, warna, pemilik, id_user)
                     VALUES (?, ?, ?, ?, ?)'
                );

                $q->execute([
                    $plat,
                    $jenis,
                    $warna ?: null,
                    $pemilik ?: null,
                    $idUser
                ]);
            }

            log_activity(
                $pdo,
                (int)current_user()['id_user'],
                'Menyimpan kendaraan ' . $plat
            );

            flash('success', 'Data kendaraan berhasil disimpan.');
        } catch (Throwable $e) {
            flash(
                'danger',
                'Gagal menyimpan. Periksa plat nomor dan akun pengguna.'
            );
        }

        redirect('kendaraan.php');
    }

    if ($act === 'delete') {
        try {
            $q = $pdo->prepare(
                'DELETE FROM tb_kendaraan WHERE id_kendaraan=?'
            );

            $q->execute([(int)$_POST['id_kendaraan']]);

            flash('success', 'Kendaraan berhasil dihapus.');
        } catch (Throwable $e) {
            flash(
                'danger',
                'Kendaraan tidak dapat dihapus karena sudah memiliki transaksi.'
            );
        }

        redirect('kendaraan.php');
    }
}

if (isset($_GET['edit'])) {
    $q = $pdo->prepare(
        'SELECT * FROM tb_kendaraan WHERE id_kendaraan=?'
    );

    $q->execute([(int)$_GET['edit']]);
    $edit = $q->fetch() ?: null;
}

$users = $pdo->query(
    "SELECT id_user, nama_lengkap
     FROM tb_user
     WHERE status_aktif=1
     ORDER BY nama_lengkap"
)->fetchAll();

$search = trim($_GET['q'] ?? '');

$stmt = $pdo->prepare(
    'SELECT k.*, u.nama_lengkap
     FROM tb_kendaraan k
     JOIN tb_user u ON u.id_user=k.id_user
     WHERE k.plat_nomor LIKE ?
        OR COALESCE(k.pemilik, "") LIKE ?
     ORDER BY k.id_kendaraan DESC
     LIMIT 300'
);

$stmt->execute([
    '%' . $search . '%',
    '%' . $search . '%'
]);

$rows = $stmt->fetchAll();

$pageTitle = 'Data Kendaraan';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>

<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="panel">
            <h2 class="section-heading">
                <?= $edit ? 'Edit Kendaraan' : 'Tambah Kendaraan' ?>
            </h2>

            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id_kendaraan"
                       value="<?= (int)($edit['id_kendaraan'] ?? 0) ?>">

                <div class="mb-3">
                    <label class="form-label">Plat Nomor</label>
                    <input class="form-control" name="plat_nomor" required
                           value="<?= e($edit['plat_nomor'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Kendaraan</label>
                    <select class="form-select" name="jenis_kendaraan">
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
                    <label class="form-label">Warna</label>
                    <input class="form-control" name="warna"
                           value="<?= e($edit['warna'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Pemilik</label>
                    <input class="form-control" name="pemilik"
                           value="<?= e($edit['pemilik'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Akun Pencatat/Pemilik</label>
                    <select class="form-select" name="id_user" required>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= (int)$u['id_user'] ?>"
                                <?= (int)($edit['id_user'] ?? current_user()['id_user'])
                                    === (int)$u['id_user'] ? 'selected' : '' ?>>
                                <?= e($u['nama_lengkap']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button class="btn btn-primary">Simpan Kendaraan</button>

                <?php if ($edit): ?>
                    <a class="btn btn-outline-secondary" href="kendaraan.php">
                        Batal
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="panel">
            <div class="panel-head">
                <h2 class="section-heading mb-0">Daftar Kendaraan</h2>

                <form class="d-flex gap-2" method="get">
                    <input class="form-control" name="q"
                           value="<?= e($search) ?>"
                           placeholder="Cari plat/pemilik">

                    <button class="btn btn-outline-secondary">Cari</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Plat</th>
                            <th>Jenis</th>
                            <th>Warna</th>
                            <th>Pemilik</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= e($r['plat_nomor']) ?></strong>
                                </td>

                                <td><?= e(ucfirst($r['jenis_kendaraan'])) ?></td>
                                <td><?= e($r['warna']) ?></td>

                                <td>
                                    <?= e($r['pemilik'] ?: $r['nama_lengkap']) ?>
                                </td>

                                <td class="text-nowrap">
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="?edit=<?= (int)$r['id_kendaraan'] ?>">
                                        Edit
                                    </a>

                                    <form method="post" class="d-inline"
                                          onsubmit="return confirm('Hapus kendaraan ini?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action"
                                               value="delete">
                                        <input type="hidden" name="id_kendaraan"
                                               value="<?= (int)$r['id_kendaraan'] ?>">

                                        <button class="btn btn-sm btn-outline-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$rows): ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    Data tidak ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>