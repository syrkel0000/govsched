<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    // Don't allow deleting own account
    if ($del_id !== $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM appointments WHERE user_id = ?")->execute([$del_id]);
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$del_id]);
    }
    header('Location: users.php');
    exit();
}

$users = $pdo->query("
    SELECT u.*, COUNT(a.id) as total_appointments
    FROM users u
    LEFT JOIN appointments a ON u.id = a.user_id
    WHERE u.role = 'applicant'
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | Users</title>
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
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="appointments.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Appointments</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link active">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Users</p>
                        </a>
                    </li>
                    <li class="nav-item">
    <a href="account.php" class="nav-link">
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
                <h1 class="m-0">Users</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Registered Applicants</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Total Appointments</th>
                                    <th>Registered</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $i => $row): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= $row['full_name'] ?></td>
                                    <td><?= $row['email'] ?></td>
                                    <td><?= $row['total_appointments'] ?></td>
                                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <a href="users.php?delete=<?= $row['id'] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this user and all their appointments?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($users)): ?>
                                <tr><td colspan="6" class="text-center">No users yet.</td></tr>
                                <?php endif; ?>
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