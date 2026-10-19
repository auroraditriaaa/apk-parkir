<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin', 'petugas']);

$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? 'semua';

$sql = "SELECT
            t.*,
            k.plat_nomor,
            k.jenis_kendaraan,
            k.pemilik,
            a.nama_area,
            u.nama_lengkap AS petugas
        FROM tb_transaksi t
        JOIN tb_kendaraan k ON k.id_kendaraan=t.id_kendaraan
        JOIN tb_area_parkir a ON a.id_area=t.id_area
        JOIN tb_user u ON u.id_user=t.id_user
        WHERE (k.plat_nomor LIKE ? OR COALESCE(k.pemilik, '') LIKE ?)";

$params = ['%' . $search . '%', '%' . $search . '%'];

if (in_array($status, ['masuk', 'keluar'], true)) {
    $sql .= ' AND t.status=?';
    $params[] = $status;
}

$sql .= ' ORDER BY t.id_parkir DESC LIMIT 300';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$vehicles = $pdo->query(
    'SELECT id_kendaraan, plat_nomor, jenis_kendaraan, pemilik
     FROM tb_kendaraan
     ORDER BY plat_nomor'
)->fetchAll();

$areas = $pdo->query(
    'SELECT * FROM tb_area_parkir ORDER BY nama_area'
)->fetchAll();

$tariffs = $pdo->query(
    'SELECT * FROM tb_tarif ORDER BY jenis_kendaraan'
)->fetchAll();

$pageTitle = 'Transaksi Parkir';

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>

<div class="row g-4">

    <div class="col-12 col-xl-4">

        <div class="panel">
            <h2 class="section-heading">Catat Kendaraan Masuk</h2>

            <form method="post" action="masuk.php">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">Kendaraan</label>

                    <select class="form-select" name="id_kendaraan" required>
                        <option value="">Pilih plat nomor</option>

                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= (int)$v['id_kendaraan'] ?>">
                                <?= e(
                                    $v['plat_nomor'] . ' — ' .
                                    ucfirst($v['jenis_kendaraan'])
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="form-text">
                        Jika kendaraan belum terdaftar, minta admin
                        menambahkannya terlebih dahulu.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Area Parkir</label>

                    <select class="form-select" name="id_area" required>
                        <option value="">Pilih area</option>

                        <?php foreach ($areas as $a): ?>
                            <option value="<?= (int)$a['id_area'] ?>"
                                <?= (int)$a['terisi'] >= (int)$a['kapasitas']
                                    ? 'disabled' : '' ?>>
                                <?= e($a['nama_area']) ?>
                                (<?= (int)$a['terisi'] ?>/<?= (int)$a['kapasitas'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button class="btn btn-primary w-100">
                    Simpan Kendaraan Masuk
                </button>
            </form>
        </div>

        <div class="panel">
            <h2 class="section-heading">Tarif Saat Ini</h2>

            <?php foreach ($tariffs as $t): ?>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span><?= e(ucfirst($t['jenis_kendaraan'])) ?></span>
                    <strong><?= rupiah($t['tarif_per_jam']) ?>/jam</strong>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <div class="col-12 col-xl-8">
        <div class="panel">

            <div class="panel-head">
                <h2 class="section-heading mb-0">Riwayat Transaksi</h2>
            </div>

            <form class="filter-row mb-3" method="get">
                <div>
                    <label class="form-label">Cari plat/pemilik</label>
                    <input class="form-control" name="q"
                           value="<?= e($search) ?>">
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="semua">Semua</option>
                        <option value="masuk"
                            <?= $status === 'masuk' ? 'selected' : '' ?>>
                            Masuk
                        </option>
                        <option value="keluar"
                            <?= $status === 'keluar' ? 'selected' : '' ?>>
                            Keluar
                        </option>
                    </select>
                </div>

                <button class="btn btn-outline-secondary">Filter</button>
            </form>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Plat</th>
                            <th>Area</th>
                            <th>Masuk</th>
                            <th>Status</th>
                            <th>Biaya</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= e($r['plat_nomor']) ?></strong>
                                    <small class="d-block text-secondary">
                                        <?= e(ucfirst($r['jenis_kendaraan'])) ?>
                                    </small>
                                </td>

                                <td><?= e($r['nama_area']) ?></td>
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

                                <td class="text-nowrap">
                                    <?php if ($r['status'] === 'masuk'): ?>
                                        <form method="post" action="keluar.php"
                                              onsubmit="return confirm('Proses kendaraan keluar dan hitung biaya?')">
                                            <?= csrf_field() ?>

                                            <input type="hidden" name="id_parkir"
                                                   value="<?= (int)$r['id_parkir'] ?>">

                                            <button class="btn btn-sm btn-primary">
                                                Keluar
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a class="btn btn-sm btn-outline-secondary"
                                           target="_blank"
                                           href="struk.php?id=<?= (int)$r['id_parkir'] ?>">
                                            Struk
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$rows): ?>
                            <tr>
                                <td colspan="6" class="empty-state">
                                    Belum ada transaksi yang cocok.
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