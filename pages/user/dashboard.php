<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireApplicant();

// Stats
$stats = $pdo->prepare("
    SELECT
        COUNT(*) as total,
        SUM(status = 'pending')   as pending,
        SUM(status = 'confirmed') as confirmed,
        SUM(status = 'cancelled') as cancelled
    FROM appointments
    WHERE user_id = ?
");
$stats->execute([$_SESSION['user_id']]);
$stats = $stats->fetch();

// Active appointment (pending or confirmed)
$active = $pdo->prepare("
    SELECT a.*, d.name as document_name, t.slot_time
    FROM appointments a
    JOIN documents d ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id = t.id
    WHERE a.user_id = ? AND a.status IN ('pending','confirmed')
    ORDER BY a.appointment_date ASC
    LIMIT 1
");
$active->execute([$_SESSION['user_id']]);
$active = $active->fetch();

// Recent appointments
$appointments = $pdo->prepare("
    SELECT a.*, d.name as document_name, t.slot_time
    FROM appointments a
    JOIN documents d ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id = t.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
    LIMIT 5
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
    <style>
        .welcome-banner {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 8px;
            padding: 24px 28px;
            margin-bottom: 20px;
        }
        .welcome-banner h4 { margin: 0 0 4px 0; font-size: 1.4rem; }
        .welcome-banner p  { margin: 0; opacity: 0.85; font-size: 0.95rem; }
        .active-appt-card {
            border-left: 5px solid #28a745;
        }
        .active-appt-card.pending {
            border-left-color: #ffc107;
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
                        <a href="dashboard.php" class="nav-link active">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="book.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-plus"></i><p>Book Appointment</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="my_appointments.php" class="nav-link">
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
                <h1 class="m-0">Dashboard</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <h4><i class="fas fa-hand-wave mr-2"></i> Hello, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h4>
                    <p>Welcome to GovSched — your government document appointment portal. Book, track, and manage your appointments easily.</p>
                </div>

                <!-- Stats -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= $stats['total'] ?></h3>
                                <p>Total Appointments</p>
                            </div>
                            <div class="icon"><i class="fas fa-calendar"></i></div>
                            <a href="my_appointments.php" class="small-box-footer">
                                View All <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $stats['pending'] ?></h3>
                                <p>Pending</p>
                            </div>
                            <div class="icon"><i class="fas fa-clock"></i></div>
                            <a href="my_appointments.php" class="small-box-footer">
                                View <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $stats['confirmed'] ?></h3>
                                <p>Confirmed</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                            <a href="my_appointments.php" class="small-box-footer">
                                View <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= $stats['cancelled'] ?></h3>
                                <p>Cancelled</p>
                            </div>
                            <div class="icon"><i class="fas fa-times-circle"></i></div>
                            <a href="my_appointments.php" class="small-box-footer">
                                View <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">

                    <!-- Active Appointment -->
                    <div class="col-md-5">
                        <?php if ($active): ?>
                        <div class="card active-appt-card <?= $active['status'] ?>">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-calendar-check mr-1 text-success"></i>
                                    Upcoming Appointment
                                </h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <th style="width:40%">Reference No</th>
                                        <td><code><?= $active['reference_no'] ?></code></td>
                                    </tr>
                                    <tr>
                                        <th>Document</th>
                                        <td><?= htmlspecialchars($active['document_name']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Office</th>
                                        <td><?= htmlspecialchars($active['office']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Date</th>
                                        <td><?= date('F d, Y', strtotime($active['appointment_date'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Time</th>
                                        <td><?= $active['slot_time'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <?php
                                            $badge = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger'];
                                            $s = $active['status'];
                                            ?>
                                            <span class="badge badge-<?= $badge[$s] ?>"><?= ucfirst($s) ?></span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card-footer">
                                <a href="receipt.php?id=<?= $active['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View Receipt
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-3">You have no upcoming appointments.</p>
                                <a href="book.php" class="btn btn-primary">
                                    <i class="fas fa-calendar-plus"></i> Book Now
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-bolt mr-1"></i> Quick Actions
                                </h3>
                            </div>
                            <div class="card-body p-2">
                                <a href="book.php" class="btn btn-primary btn-block mb-2">
                                    <i class="fas fa-calendar-plus"></i> Book Appointment
                                </a>
                                <a href="my_appointments.php" class="btn btn-default btn-block mb-2">
                                    <i class="fas fa-list"></i> My Appointments
                                </a>
                                <a href="profile.php" class="btn btn-default btn-block">
                                    <i class="fas fa-user-edit"></i> Edit Profile
                                </a>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="font-weight-bold mb-2">
                                    <i class="fas fa-info-circle text-info mr-1"></i> Reminder
                                </h6>
                                <ul class="pl-3 mb-0 small text-muted">
                                    <li>Offices are open Mon–Fri only</li>
                                    <li>Bring a valid ID on your appointment date</li>
                                    <li>Arrive 10 minutes before your time slot</li>
                                    <li>Only 1 active appointment allowed at a time</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Appointments -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-history mr-1"></i> Recent Activity
                                </h3>
                                <div class="card-tools">
                                    <a href="my_appointments.php" class="btn btn-sm btn-default">View All</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($appointments)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">No activity yet.</p>
                                </div>
                                <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($appointments as $row):
                                        $badge = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger'];
                                        $s = $row['status'];
                                    ?>
                                    <li class="list-group-item px-3 py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="font-weight-bold small">
                                                    <?= htmlspecialchars($row['document_name']) ?>
                                                </div>
                                                <div class="text-muted" style="font-size:0.78rem;">
                                                    <?= date('M d, Y', strtotime($row['appointment_date'])) ?>
                                                    &bull; <?= $row['slot_time'] ?>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-<?= $badge[$s] ?> mb-1">
                                                    <?= ucfirst($s) ?>
                                                </span><br>
                                                <a href="receipt.php?id=<?= $row['id'] ?>"
                                                   class="btn btn-xs btn-info" style="font-size:0.72rem;padding:2px 7px;">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
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