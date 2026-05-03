<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireApplicant();

// Support both ?id= and ?ref=
if (!empty($_GET['ref'])) {
    $stmt = $pdo->prepare("
        SELECT a.*, d.name AS document_name, d.agency,
               t.slot_time, b.name AS branch_name,
               b.address AS branch_address, b.contact AS branch_contact, b.city
        FROM appointments a
        JOIN documents d  ON a.document_id = d.id
        JOIN time_slots t ON a.slot_id     = t.id
        JOIN branches b   ON a.branch_id   = b.id
        WHERE a.reference_no = ? AND a.user_id = ?
    ");
    $stmt->execute([$_GET['ref'], $_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("
        SELECT a.*, d.name AS document_name, d.agency,
               t.slot_time, b.name AS branch_name,
               b.address AS branch_address, b.contact AS branch_contact, b.city
        FROM appointments a
        JOIN documents d  ON a.document_id = d.id
        JOIN time_slots t ON a.slot_id     = t.id
        JOIN branches b   ON a.branch_id   = b.id
        WHERE a.id = ? AND a.user_id = ?
    ");
    $stmt->execute([(int)$_GET['id'], $_SESSION['user_id']]);
}

$appt = $stmt->fetch();
if (!$appt) {
    header('Location: dashboard.php');
    exit();
}

$badge = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger'];
$s = $appt['status'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | Receipt</title>
    <link rel="stylesheet" href="../../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../../assets/dist/css/adminlte.min.css">
    <style>
        @media print {
            .main-header, .main-sidebar, .main-footer,
            .card-footer, .no-print { display: none !important; }
            .content-wrapper { margin-left: 0 !important; }
        }
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
                <span class="nav-link">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
            </li>
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
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="book.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-plus"></i><p>Book Appointment</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="my_appointments.php" class="nav-link active">
                            <i class="nav-icon fas fa-list"></i><p>My Appointments</p>
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
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h3 class="card-title">
                                    <i class="fas fa-check-circle mr-1"></i> Booking Submitted
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-<?= $badge[$s] ?>" style="font-size:13px;padding:6px 12px;">
                                        <?= ucfirst($s) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">

                                <div class="text-center mb-4">
                                    <h5 class="text-muted">Reference Number</h5>
                                    <h2 class="text-primary"><b><?= htmlspecialchars($appt['reference_no']) ?></b></h2>
                                    <small class="text-muted">Keep this for your records</small>
                                </div>

                                <hr>

                                <div class="row">
                                    <!-- Appointment Details -->
                                    <div class="col-md-6">
                                        <h5><i class="fas fa-calendar-alt mr-1 text-primary"></i> Appointment Details</h5>
                                        <table class="table table-borderless table-sm">
                                            <tr><th width="130">Document:</th><td><?= htmlspecialchars($appt['document_name']) ?></td></tr>
                                            <tr><th>Agency:</th><td><?= htmlspecialchars($appt['agency']) ?></td></tr>
                                            <tr><th>Date:</th><td><?= date('F d, Y', strtotime($appt['appointment_date'])) ?></td></tr>
                                            <tr><th>Time:</th><td><?= htmlspecialchars($appt['slot_time']) ?></td></tr>
                                            <tr><th>Request Type:</th><td><?= ucfirst($appt['request_type']) ?></td></tr>
                                            <tr><th>Status:</th><td><span class="badge badge-<?= $badge[$s] ?>"><?= ucfirst($s) ?></span></td></tr>
                                        </table>
                                    </div>

                                    <!-- Branch Info -->
                                    <div class="col-md-6">
                                        <h5><i class="fas fa-building mr-1 text-primary"></i> Office Location</h5>
                                        <table class="table table-borderless table-sm">
                                            <tr><th width="100">Branch:</th><td><?= htmlspecialchars($appt['branch_name']) ?></td></tr>
                                            <tr><th>City:</th><td><?= htmlspecialchars($appt['city']) ?></td></tr>
                                            <tr><th>Address:</th><td><?= htmlspecialchars($appt['branch_address']) ?></td></tr>
                                            <?php if ($appt['branch_contact']): ?>
                                            <tr><th>Contact:</th><td><?= htmlspecialchars($appt['branch_contact']) ?></td></tr>
                                            <?php endif; ?>
                                        </table>
                                    </div>
                                </div>

                                <hr>

                                <!-- Personal Info -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5><i class="fas fa-user mr-1 text-primary"></i> Personal Information</h5>
                                        <table class="table table-borderless table-sm">
                                            <tr><th width="120">Name:</th><td><?= htmlspecialchars($appt['full_name']) ?></td></tr>
                                            <tr><th>Age:</th><td><?= $appt['age'] ?></td></tr>
                                            <tr><th>Contact:</th><td><?= htmlspecialchars($appt['contact']) ?></td></tr>
                                            <tr><th>Email:</th><td><?= htmlspecialchars($appt['email']) ?></td></tr>
                                            <tr><th>Address:</th><td><?= htmlspecialchars($appt['address']) ?></td></tr>
                                        </table>
                                    </div>

                                    <?php if ($appt['request_type'] === 'other'): ?>
                                    <div class="col-md-6">
                                        <h5><i class="fas fa-user-friends mr-1 text-warning"></i> Requested For</h5>
                                        <table class="table table-borderless table-sm">
                                            <tr><th width="120">Name:</th><td><?= htmlspecialchars($appt['for_name']) ?></td></tr>
                                            <tr><th>Relationship:</th><td><?= htmlspecialchars($appt['for_relationship']) ?></td></tr>
                                            <tr><th>Minor:</th><td><?= $appt['is_minor'] ? '<span class="badge badge-warning">Yes</span>' : 'No' ?></td></tr>
                                        </table>
                                        <?php if ($appt['is_minor']): ?>
                                        <h5 class="mt-2"><i class="fas fa-shield-alt mr-1 text-danger"></i> Guardian Info</h5>
                                        <table class="table table-borderless table-sm">
                                            <tr><th width="120">Name:</th><td><?= htmlspecialchars($appt['guardian_name']) ?></td></tr>
                                            <tr><th>Contact:</th><td><?= htmlspecialchars($appt['guardian_contact']) ?></td></tr>
                                        </table>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Reminder -->
                                <div class="alert alert-info mt-2 mb-0">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Reminder:</strong> Bring a valid ID and arrive <strong>10 minutes before</strong> your scheduled time slot.
                                </div>

                            </div>
                            <div class="card-footer no-print">
                                <a href="my_appointments.php" class="btn btn-default">
                                    <i class="fas fa-arrow-left"></i> Back
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