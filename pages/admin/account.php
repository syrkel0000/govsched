<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireAdmin();

$success = '';
$error = '';

// Change Password
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $user = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $user->execute([$_SESSION['user_id']]);
    $user = $user->fetch();

    if (!password_verify($current, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $hashed = password_hash($new, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $_SESSION['user_id']]);
        header('Location: account.php?msg=password_updated');
        exit();
    }
}

// Add New Admin
if (isset($_POST['add_admin'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Check duplicate email
    $exists = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $exists->execute([$email]);

    if ($exists->fetch()) {
        $error = 'Email already exists.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (empty($full_name) || empty($email)) {
        $error = 'Full name and email are required.';
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)")
            ->execute([$full_name, $email, $hashed, $role]);
        header('Location: account.php?msg=admin_added');
        exit();
    }
}

// Delete Admin
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    if ($del_id === intval($_SESSION['user_id'])) {
        $error = 'You cannot delete your own account.';
    } else {
        $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'")->execute([$del_id]);
        header('Location: account.php?msg=admin_deleted');
        exit();
    }
}

// Get all admins
$admins = $pdo->query("SELECT id, full_name, email, created_at FROM users WHERE role = 'admin' ORDER BY created_at ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | Account Management</title>
    <link rel="stylesheet" href="../../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../../assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="/govsched/includes/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="/govsched/pages/admin/dashboard.php" class="brand-link">
            <span class="brand-text font-weight-light"><b>Gov</b>Sched Admin</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="appointments.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-alt"></i><p>Appointments</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i><p>Users</p>
                        </a>
                    </li>
                    <li class="nav-item">
    <a href="slots.php" class="nav-link">
        <i class="nav-icon fas fa-clock"></i><p>Slot Management</p>
    </a>
</li>

<li class="nav-item">
    <a href="documents.php" class="nav-link">
        <i class="nav-icon fas fa-file-alt"></i><p>Documents</p>
    </a>
</li>

                    <li class="nav-item">
                        <a href="account.php" class="nav-link active">
                            <i class="nav-icon fas fa-user-cog"></i><p>Account</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Account Management</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <!-- Flash Messages -->
                <?php if (isset($_GET['msg'])): ?>
                    <?php
                    $msgs = [
                        'password_updated' => ['success', 'Password updated successfully.'],
                        'admin_added'      => ['success', 'New admin account added.'],
                        'admin_deleted'    => ['warning', 'Admin account deleted.'],
                    ];
                    if (isset($msgs[$_GET['msg']])):
                        [$type, $text] = $msgs[$_GET['msg']];
                    ?>
                    <div class="alert alert-<?= $type ?> alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?= $text ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="row">

                    <!-- Change Password -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-key mr-1"></i> Change Password</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="account.php">
                                    <div class="form-group">
                                        <label>Current Password</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>New Password <small class="text-muted">(min 8 characters)</small></label>
                                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                                    </div>
                                    <button type="submit" name="change_password" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Add New Admin -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-user-plus mr-1"></i> Add Admin Account</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="account.php">
                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <input type="text" name="full_name" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Password <small class="text-muted">(min 8 characters)</small></label>
                                        <input type="password" name="password" class="form-control" minlength="8" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Role</label>
                                        <select name="role" class="form-control" required>
                                            <option value="admin">Admin</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="add_admin" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Add Admin
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Admin List -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users-cog mr-1"></i> Admin Accounts</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $i => $admin): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($admin['full_name']) ?>
                                        <?php if ($admin['id'] == $_SESSION['user_id']): ?>
                                            <span class="badge badge-primary ml-1">You</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($admin['email']) ?></td>
                                    <td><?= date('M d, Y', strtotime($admin['created_at'])) ?></td>
                                    <td>
                                        <?php if ($admin['id'] != $_SESSION['user_id']): ?>
                                            <a href="account.php?delete=<?= $admin['id'] ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Delete this admin account?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <footer class="main-footer">
        <strong>GovSched</strong> &copy; <?= date('Y') ?>
    </footer>
</div>
<script src="../../assets/plugins/jquery/jquery.min.js"></script>
<script src="../../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/dist/js/adminlte.min.js"></script>
</body>
</html>