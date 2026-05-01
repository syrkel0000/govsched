<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireApplicant();

$id = intval($_GET['id']);
$stmt = $pdo->prepare("
    SELECT a.*, d.name as document_name, t.slot_time
    FROM appointments a
    JOIN documents d ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id = t.id
    WHERE a.id = ? AND a.user_id = ?
");
$stmt->execute([$id, $_SESSION['user_id']]);
$appt = $stmt->fetch();

if (!$appt) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | Receipt</title>
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
        <a href="/govsched/pages/user/dashboard.php" class="brand-link">
            <span class="brand-text font-weight-light"><b>Gov</b>Sched</span>
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
                    <li class="nav-item">
    <a href="profile.php" class="nav-link">
        <i class="nav-icon fas fa-user"></i><p>My Profile</p>
    </a>
</li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Appointment Receipt</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h3 class="card-title">
                                    <i class="fas fa-check-circle"></i> Appointment Confirmed
                                </h3>
                            </div>
                            <div class="card-body">

                                <div class="text-center mb-4">
                                    <h4>Reference Number</h4>
                                    <h2 class="text-primary"><b><?= $appt['reference_no'] ?></b></h2>
                                    <small class="text-muted">Keep this for your records</small>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Appointment Details</h5>
                                        <table class="table table-borderless">
                                            <tr>
                                                <th>Document:</th>
                                                <td><?= $appt['document_name'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Date:</th>
                                                <td><?= date('F d, Y', strtotime($appt['appointment_date'])) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Time:</th>
                                                <td><?= $appt['slot_time'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Request Type:</th>
                                                <td><?= ucfirst($appt['request_type']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Status:</th>
                                                <td><span class="badge badge-warning">Pending</span></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Personal Information</h5>
                                        <table class="table table-borderless">
                                            <tr>
                                                <th>Name:</th>
                                                <td><?= $appt['full_name'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Age:</th>
                                                <td><?= $appt['age'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Contact:</th>
                                                <td><?= $appt['contact'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td><?= $appt['email'] ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <?php if ($appt['request_type'] === 'other'): ?>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Requested For</h5>
                                        <table class="table table-borderless">
                                            <tr>
                                                <th>Name:</th>
                                                <td><?= $appt['for_name'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Relationship:</th>
                                                <td><?= $appt['for_relationship'] ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <?php if ($appt['is_minor']): ?>
                                    <div class="col-md-6">
                                        <h5>Guardian Information</h5>
                                        <table class="table table-borderless">
                                            <tr>
                                                <th>Guardian:</th>
                                                <td><?= $appt['guardian_name'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Contact:</th>
                                                <td><?= $appt['guardian_contact'] ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                            </div>
                            <div class="card-footer">
                                <a href="dashboard.php" class="btn btn-default">
                                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                                </a>
                                <button onclick="window.print()" class="btn btn-primary float-right">
                                    <i class="fas fa-print"></i> Print Receipt
                                </button>
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