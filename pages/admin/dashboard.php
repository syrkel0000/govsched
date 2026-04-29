<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireAdmin();

$total_appointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$pending = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn();
$confirmed = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='confirmed'")->fetchColumn();
$cancelled = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='cancelled'")->fetchColumn();

$recent = $pdo->query("
    SELECT a.*, u.full_name as applicant_name, d.name as document_name, t.slot_time
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN documents d ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id = t.id
    ORDER BY a.created_at DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../../assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
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

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="/govsched/pages/admin/dashboard.php" class="brand-link">
            <span class="brand-text font-weight-light"><b>Gov</b>Sched Admin</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link active">
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
                        <a href="users.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Users</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Dashboard</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <!-- Stats -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= $total_appointments ?></h3>
                                <p>Total Appointments</p>
                            </div>
                            <div class="icon"><i class="fas fa-calendar"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $pending ?></h3>
                                <p>Pending</p>
                            </div>
                            <div class="icon"><i class="fas fa-clock"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $confirmed ?></h3>
                                <p>Confirmed</p>
                            </div>
                            <div class="icon"><i class="fas fa-check"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= $cancelled ?></h3>
                                <p>Cancelled</p>
                            </div>
                            <div class="icon"><i class="fas fa-times"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Recent Appointments -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Appointments</h3>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Reference No</th>
                                            <th>Applicant</th>
                                            <th>Document</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent as $row): ?>
                                        <tr>
                                            <td><?= $row['reference_no'] ?></td>
                                            <td><?= $row['applicant_name'] ?></td>
                                            <td><?= $row['document_name'] ?></td>
                                            <td><?= $row['appointment_date'] ?></td>
                                            <td><?= $row['slot_time'] ?></td>
                                            <td>
                                                <?php
                                                $badge = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger'];
                                                $s = $row['status'];
                                                ?>
                                                <span class="badge badge-<?= $badge[$s] ?>"><?= ucfirst($s) ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($recent)): ?>
                                        <tr><td colspan="6" class="text-center">No appointments yet.</td></tr>
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