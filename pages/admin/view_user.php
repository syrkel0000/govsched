<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireAdmin();

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'applicant'");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: users.php');
    exit();
}

// Appointment stats
$stats = $pdo->prepare("
    SELECT
        COUNT(*) as total,
        SUM(status = 'pending')   as pending,
        SUM(status = 'confirmed') as confirmed,
        SUM(status = 'cancelled') as cancelled
    FROM appointments WHERE user_id = ?
");
$stats->execute([$id]);
$stats = $stats->fetch();

// All appointments
$appointments = $pdo->prepare("
    SELECT a.*, d.name AS document_name, d.agency,
           t.slot_time, b.name AS branch_name, b.city
    FROM appointments a
    JOIN documents d  ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id     = t.id
    JOIN branches b   ON a.branch_id   = b.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");
$appointments->execute([$id]);
$appointments = $appointments->fetchAll();

$badge = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | View User</title>
    <link rel="stylesheet" href="../../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../../assets/dist/css/adminlte.min.css">
    <style>
        .profile-avatar {
            width: 80px; height: 80px; border-radius: 50%;
            background: linear-gradient(135deg, #1a3a6b, #2563b0);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 12px;
        }
        .profile-avatar i { font-size: 36px; color: #fff; }
        .stat-box {
            border-radius: 10px; padding: 16px;
            text-align: center; color: #fff;
            margin-bottom: 10px;
        }
        .stat-box h3 { font-size: 2rem; font-weight: 700; margin: 0; }
        .stat-box p  { font-size: .8rem; margin: 0; opacity: .9; }
    </style>
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
                        <a href="users.php" class="nav-link active">
                            <i class="nav-icon fas fa-users"></i><p>Users</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="slots.php" class="nav-link">
                            <i class="nav-icon fas fa-clock"></i><p>Slot Management</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="branches.php" class="nav-link">
                            <i class="nav-icon fas fa-map-marker-alt"></i><p>Branches</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="documents.php" class="nav-link">
                            <i class="nav-icon fas fa-file-alt"></i><p>Documents</p>
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
                <h1 class="m-0">User Profile</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <div class="row">

                    <!-- Left: Profile Card + Stats -->
                    <div class="col-md-3">

                        <!-- Profile Info -->
                        <div class="card" style="border-radius:10px;">
                            <div class="card-body text-center pt-4">
                                <div class="profile-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h5 class="mb-1 font-weight-bold"><?= htmlspecialchars($user['full_name']) ?></h5>
                                <p class="text-muted small mb-2"><?= htmlspecialchars($user['email']) ?></p>
                                <span class="badge badge-primary mb-3">Applicant</span>
                                <hr>
                                <div class="text-left">
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-calendar-alt mr-2 text-primary"></i>
                                        Registered: <strong><?= date('M d, Y', strtotime($user['created_at'])) ?></strong>
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-id-badge mr-2 text-primary"></i>
                                        User ID: <strong>#<?= $user['id'] ?></strong>
                                    </p>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <a href="users.php" class="btn btn-sm btn-default">
                                    <i class="fas fa-arrow-left mr-1"></i> Back to Users
                                </a>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="row">
                            <div class="col-6 pr-1">
                                <div class="stat-box" style="background:linear-gradient(135deg,#1a6fad,#2563b0);">
                                    <h3><?= $stats['total'] ?></h3>
                                    <p>Total</p>
                                </div>
                            </div>
                            <div class="col-6 pl-1">
                                <div class="stat-box" style="background:linear-gradient(135deg,#d97706,#f59e0b);">
                                    <h3><?= $stats['pending'] ?></h3>
                                    <p>Pending</p>
                                </div>
                            </div>
                            <div class="col-6 pr-1">
                                <div class="stat-box" style="background:linear-gradient(135deg,#15803d,#22c55e);">
                                    <h3><?= $stats['confirmed'] ?></h3>
                                    <p>Confirmed</p>
                                </div>
                            </div>
                            <div class="col-6 pl-1">
                                <div class="stat-box" style="background:linear-gradient(135deg,#b91c1c,#ef4444);">
                                    <h3><?= $stats['cancelled'] ?></h3>
                                    <p>Cancelled</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right: Appointment History -->
                    <div class="col-md-9">
                        <div class="card" style="border-radius:10px;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Appointment History
                                    <span class="badge badge-primary ml-1"><?= count($appointments) ?></span>
                                </h3>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Document</th>
                                            <th>Branch</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>View</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $row):
                                            $s = $row['status'];
                                        ?>
                                        <tr>
                                            <td><code style="font-size:.75rem;"><?= htmlspecialchars($row['reference_no']) ?></code></td>
                                            <td>
                                                <?= htmlspecialchars($row['document_name']) ?>
                                                <small class="text-muted d-block"><?= htmlspecialchars($row['agency']) ?></small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($row['branch_name']) ?>
                                                <small class="text-muted d-block"><?= htmlspecialchars($row['city']) ?></small>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                                            <td><?= htmlspecialchars($row['slot_time']) ?></td>
                                            <td><?= ucfirst($row['request_type']) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $badge[$s] ?>"><?= ucfirst($s) ?></span>
                                            </td>
                                            <td>
                                                <a href="view_appointment.php?id=<?= $row['id'] ?>"
                                                   class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($appointments)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                                No appointments yet.
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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