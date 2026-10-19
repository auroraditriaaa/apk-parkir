<?php

require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

$rows = $pdo->query(
    'SELECT
        l.id_log,
        l.aktivitas,
        l.waktu_aktivitas,
        u.nama_lengkap,
        u.username,
        u.role
     FROM tb_log_aktivitas l
     JOIN tb_user u ON u.id_user=l.id_user
     ORDER BY l.id_log DESC
     LIMIT 500'
)->fetchAll();

$pageTitle = 'Log Aktivitas';

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>

<div class="panel">

    <div class="panel-head">
        <h2 class="section-heading mb-0">
            Riwayat Aktivitas Pengguna
        </h2>

        <span class="badge text-bg-light">
            Maksimal 500 data terbaru
        </span>
    </div>

    <div class="table-responsive">
        <table class="table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pengguna</th>
                    <th>Role</th>
                    <th>Aktivitas</th>
                    <th>Waktu</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>#<?= (int)$r['id_log'] ?></td>

                        <td>
                            <?= e($r['nama_lengkap']) ?>
                            <small class="d-block text-secondary">
                                <?= e($r['username']) ?>
                            </small>
                        </td>

                        <td><?= e(ucfirst($r['role'])) ?></td>
                        <td><?= e($r['aktivitas']) ?></td>
                        <td><?= e($r['waktu_aktivitas']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="5" class="empty-state">
                            Belum ada aktivitas.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>