<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireApplicant();

$appointments = $pdo->prepare("
    SELECT a.*, d.name as document_name, t.slot_time
    FROM appointments a
    JOIN documents d ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id = t.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");
$appointments->execute([$_SESSION['user_id']]);
$appointments = $appointments->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | Dashboard</title>
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
                <span class="nav-link">Welcome, <?= $_SESSION['full_name'] ?></span>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/govsched/includes/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="/govsched/pages/user/dashboard.php" class="brand-link">
            <span class="brand-text font-weight-light"><b>Gov</b>Sched</span>
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
                        <a href="book.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-plus"></i>
                            <p>Book Appointment</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="my_appointments.php" class="nav-link">
                            <i class="nav-icon fas fa-list"></i>
                            <p>My Appointments</p>
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
                <h1 class="m-0">My Dashboard</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">My Appointments</h3>
                                <div class="card-tools">
                                    <a href="book.php" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Book Appointment
                                    </a>
                                </div>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Reference No</th>
                                            <th>Document</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $row): ?>
                                        <tr>
                                            <td><?= $row['reference_no'] ?></td>
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
                                            <td>
                                                <a href="receipt.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($appointments)): ?>
                                        <tr><td colspan="6" class="text-center">No appointments yet. <a href="book.php">Book one now</a>.</td></tr>
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