<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

$edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id_user'] ?? 0);
        $nama = trim($_POST['nama_lengkap'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $role = $_POST['role'] ?? 'petugas';
        $aktif = isset($_POST['status_aktif']) ? 1 : 0;

        if (
            $nama === '' || $username === '' ||
            !in_array($role, ['admin', 'petugas', 'owner'], true)
        ) {
            flash('danger', 'Lengkapi data user dengan benar.');
            redirect('users.php');
        }

        if (
            $id === (int)current_user()['id_user'] &&
            ($aktif !== 1 || $role !== 'admin')
        ) {
            flash('danger', 'Akun admin yang sedang digunakan tidak boleh dinonaktifkan atau diubah role-nya.');
            redirect('users.php');
        }

        try {
            if ($id > 0) {
                if ($password !== '') {
                    $q = $pdo->prepare(
                        'UPDATE tb_user
                         SET nama_lengkap=?, username=?, password=?, role=?, status_aktif=?
                         WHERE id_user=?'
                    );

                    $q->execute([
                        $nama, $username,
                        password_hash($password, PASSWORD_DEFAULT),
                        $role, $aktif, $id
                    ]);
                } else {
                    $q = $pdo->prepare(
                        'UPDATE tb_user
                         SET nama_lengkap=?, username=?, role=?, status_aktif=?
                         WHERE id_user=?'
                    );

                    $q->execute([
                        $nama, $username, $role, $aktif, $id
                    ]);
                }

                log_activity(
                    $pdo,
                    (int)current_user()['id_user'],
                    "Mengubah user {$username}"
                );
            } else {
                if ($password === '') {
                    throw new RuntimeException(
                        'Password wajib diisi untuk user baru.'
                    );
                }

                $q = $pdo->prepare(
                    'INSERT INTO tb_user
                     (nama_lengkap, username, password, role, status_aktif)
                     VALUES (?, ?, ?, ?, ?)'
                );

                $q->execute([
                    $nama,
                    $username,
                    password_hash($password, PASSWORD_DEFAULT),
                    $role,
                    $aktif
                ]);

                log_activity(
                    $pdo,
                    (int)current_user()['id_user'],
                    "Menambah user {$username}"
                );
            }

            flash('success', 'Data user berhasil disimpan.');
        } catch (Throwable $e) {
            flash(
                'danger',
                $e instanceof PDOException
                    ? 'Username mungkin sudah digunakan.'
                    : $e->getMessage()
            );
        }

        redirect('users.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id_user'] ?? 0);

        if ($id === (int)current_user()['id_user']) {
            flash('danger', 'Akun yang sedang digunakan tidak dapat dihapus.');
        } else {
            try {
                $q = $pdo->prepare(
                    'DELETE FROM tb_user WHERE id_user=?'
                );
                $q->execute([$id]);

                log_activity(
                    $pdo,
                    (int)current_user()['id_user'],
                    'Menghapus user ID ' . $id
                );

                flash('success', 'User berhasil dihapus.');
            } catch (Throwable $e) {
                flash(
                    'danger',
                    'User tidak dapat dihapus karena masih memiliki data terkait.'
                );
            }
        }

        redirect('users.php');
    }
}

if (isset($_GET['edit'])) {
    $q = $pdo->prepare(
        'SELECT * FROM tb_user WHERE id_user=?'
    );
    $q->execute([(int)$_GET['edit']]);
    $edit = $q->fetch() ?: null;
}

$rows = $pdo->query(
    'SELECT id_user, nama_lengkap, username, role, status_aktif
     FROM tb_user
     ORDER BY id_user DESC
     LIMIT 500'
)->fetchAll();

$pageTitle = 'Manajemen User';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>

<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="panel">
            <h2 class="section-heading">
                <?= $edit ? 'Edit User' : 'Tambah User' ?>
            </h2>

            <form method="post">
                <?= csrf_field() ?>

                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id_user"
                       value="<?= (int)($edit['id_user'] ?? 0) ?>">

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input class="form-control" name="nama_lengkap"
                           required value="<?= e($edit['nama_lengkap'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input class="form-control" name="username"
                           required value="<?= e($edit['username'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Password
                        <?= $edit ? '(kosongkan jika tidak diubah)' : '' ?>
                    </label>
                    <input class="form-control" type="password"
                           name="password" <?= $edit ? '' : 'required' ?>
                           minlength="6">
                </div>

                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role" required>
                        <?php foreach (['admin', 'petugas', 'owner'] as $r): ?>
                            <option value="<?= e($r) ?>"
                                <?= ($edit['role'] ?? 'petugas') === $r
                                    ? 'selected' : '' ?>>
                                <?= e(ucfirst($r)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox"
                           name="status_aktif" id="aktif"
                        <?= !isset($edit['status_aktif']) ||
                            (int)$edit['status_aktif'] === 1
                            ? 'checked' : '' ?>>

                    <label class="form-check-label" for="aktif">
                        Akun aktif
                    </label>
                </div>

                <button class="btn btn-primary">Simpan User</button>

                <?php if ($edit): ?>
                    <a class="btn btn-outline-secondary" href="users.php">
                        Batal
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="panel">
            <div class="panel-head">
                <h2 class="section-heading mb-0">Daftar User</h2>
                <span class="badge text-bg-light">
                    <?= count($rows) ?> akun
                </span>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= e($r['nama_lengkap']) ?></td>
                                <td><?= e($r['username']) ?></td>
                                <td><?= e(ucfirst($r['role'])) ?></td>
                                <td>
                                    <span class="badge text-bg-<?= $r['status_aktif'] ? 'success' : 'secondary' ?>">
                                        <?= $r['status_aktif'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="?edit=<?= (int)$r['id_user'] ?>">
                                        Edit
                                    </a>

                                    <form method="post" class="d-inline"
                                          onsubmit="return confirm('Hapus user ini?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action"
                                               value="delete">
                                        <input type="hidden" name="id_user"
                                               value="<?= (int)$r['id_user'] ?>">
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