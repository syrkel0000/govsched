<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireAdmin();

// Appointment stats
$total_appointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$pending            = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn();
$confirmed          = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='confirmed'")->fetchColumn();
$cancelled          = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='cancelled'")->fetchColumn();

// Today's appointments
$today_total = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()")->fetchColumn();
$today_pending = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status = 'pending'")->fetchColumn();
$today_confirmed = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status = 'confirmed'")->fetchColumn();

// Total users
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'applicant'")->fetchColumn();

// Appointments per office
$office_stats = $pdo->query("
    SELECT office, COUNT(*) as total
    FROM appointments
    GROUP BY office
")->fetchAll();
$office_map = [];
foreach ($office_stats as $o) {
    $office_map[$o['office']] = $o['total'];
}

// Appointments per document
$doc_stats = $pdo->query("
    SELECT d.name, COUNT(a.id) as total
    FROM documents d
    LEFT JOIN appointments a ON a.document_id = d.id
    GROUP BY d.id
    ORDER BY total DESC
    LIMIT 5
")->fetchAll();

// Recent appointments
$recent = $pdo->query("
    SELECT a.*, u.full_name as applicant_name, d.name as document_name, t.slot_time
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN documents d ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id = t.id
    ORDER BY a.created_at DESC
    LIMIT 8
")->fetchAll();

// Upcoming today
$upcoming_today = $pdo->query("
    SELECT a.*, u.full_name as applicant_name, d.name as document_name, t.slot_time
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN documents d ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id = t.id
    WHERE a.appointment_date = CURDATE() AND a.status != 'cancelled'
    ORDER BY t.slot_time ASC
    LIMIT 5
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
    <style>
        .stat-card-footer { font-size: 0.8rem; padding: 6px 12px; }
        .today-banner {
            background: linear-gradient(135deg, #343a40, #495057);
            color: white;
            border-radius: 8px;
            padding: 18px 24px;
            margin-bottom: 20px;
        }
        .today-banner h5 { margin: 0 0 4px 0; font-size: 1.1rem; }
        .today-banner p  { margin: 0; opacity: 0.8; font-size: 0.9rem; }
        .office-bar { height: 10px; border-radius: 5px; }
        .progress-label { font-size: 0.8rem; }
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
                <span class="nav-link text-muted">
                    <i class="fas fa-calendar-day mr-1"></i>
                    <?= date('l, F d, Y') ?>
                </span>
            </li>
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
                        <a href="dashboard.php" class="nav-link active">
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
                <h1 class="m-0">Dashboard</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <!-- Today Banner -->
                <div class="today-banner">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5><i class="fas fa-shield-alt mr-2"></i> Admin Panel — GovSched</h5>
                            <p>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>. Here's what's happening today.</p>
                        </div>
                        <div class="col-md-6 text-md-right mt-2 mt-md-0">
                            <span class="badge badge-light mr-2" style="font-size:0.9rem;padding:8px 14px;">
                                <i class="fas fa-calendar-day mr-1"></i>
                                Today: <?= $today_total ?> appointment<?= $today_total != 1 ? 's' : '' ?>
                            </span>
                            <span class="badge badge-warning mr-2" style="font-size:0.9rem;padding:8px 14px;">
                                <?= $today_pending ?> Pending
                            </span>
                            <span class="badge badge-success" style="font-size:0.9rem;padding:8px 14px;">
                                <?= $today_confirmed ?> Confirmed
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Overall Stats -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= $total_appointments ?></h3>
                                <p>Total Appointments</p>
                            </div>
                            <div class="icon"><i class="fas fa-calendar"></i></div>
                            <a href="appointments.php" class="small-box-footer">
                                Manage <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $pending ?></h3>
                                <p>Pending</p>
                            </div>
                            <div class="icon"><i class="fas fa-clock"></i></div>
                            <a href="appointments.php?status=pending" class="small-box-footer">
                                View <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $confirmed ?></h3>
                                <p>Confirmed</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                            <a href="appointments.php?status=confirmed" class="small-box-footer">
                                View <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3><?= $total_users ?></h3>
                                <p>Registered Applicants</p>
                            </div>
                            <div class="icon"><i class="fas fa-users"></i></div>
                            <a href="users.php" class="small-box-footer">
                                View <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">

                    <!-- Upcoming Today -->
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-calendar-day mr-1"></i> Today's Schedule
                                </h3>
                                <div class="card-tools">
                                    <a href="appointments.php?date=<?= date('Y-m-d') ?>"
                                       class="btn btn-sm btn-default">View All</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($upcoming_today)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                    <p class="mb-0">No appointments today.</p>
                                </div>
                                <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($upcoming_today as $row):
                                        $badge = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger'];
                                        $s = $row['status'];
                                    ?>
                                    <li class="list-group-item px-3 py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="font-weight-bold small">
                                                    <?= htmlspecialchars($row['applicant_name']) ?>
                                                </div>
                                                <div class="text-muted" style="font-size:0.78rem;">
                                                    <?= htmlspecialchars($row['document_name']) ?>
                                                    &bull; <?= $row['slot_time'] ?>
                                                    &bull; <?= htmlspecialchars($row['office']) ?>
                                                </div>
                                            </div>
                                            <span class="badge badge-<?= $badge[$s] ?>">
                                                <?= ucfirst($s) ?>
                                            </span>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Office Breakdown + Top Documents -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-building mr-1"></i> By Office
                                </h3>
                            </div>
                            <div class="card-body">
                                <?php
                                $offices = ['Cabanatuan City', 'Palayan City'];
                                foreach ($offices as $o):
                                    $count = $office_map[$o] ?? 0;
                                    $pct = $total_appointments > 0
                                        ? round(($count / $total_appointments) * 100)
                                        : 0;
                                ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between progress-label mb-1">
                                        <span><?= $o ?></span>
                                        <span><?= $count ?> (<?= $pct ?>%)</span>
                                    </div>
                                    <div class="progress" style="height:10px;">
                                        <div class="progress-bar bg-primary"
                                             style="width:<?= $pct ?>%; border-radius:5px;"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-alt mr-1"></i> Top Documents
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($doc_stats as $doc): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                                        <span class="small"><?= htmlspecialchars($doc['name']) ?></span>
                                        <span class="badge badge-primary badge-pill"><?= $doc['total'] ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                    <?php if (empty($doc_stats)): ?>
                                    <li class="list-group-item text-muted text-center small">No data yet.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Appointments -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-history mr-1"></i> Recent Appointments
                                </h3>
                                <div class="card-tools">
                                    <a href="appointments.php" class="btn btn-sm btn-default">View All</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($recent)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">No appointments yet.</p>
                                </div>
                                <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($recent as $row):
                                        $badge = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger'];
                                        $s = $row['status'];
                                    ?>
                                    <li class="list-group-item px-3 py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="font-weight-bold small">
                                                    <?= htmlspecialchars($row['applicant_name']) ?>
                                                </div>
                                                <div class="text-muted" style="font-size:0.78rem;">
                                                    <?= htmlspecialchars($row['document_name']) ?>
                                                    &bull; <?= date('M d', strtotime($row['appointment_date'])) ?>
                                                    &bull; <?= $row['slot_time'] ?>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-<?= $badge[$s] ?> mb-1">
                                                    <?= ucfirst($s) ?>
                                                </span><br>
                                                <a href="view_appointment.php?id=<?= $row['id'] ?>"
                                                   class="btn btn-xs btn-info"
                                                   style="font-size:0.72rem;padding:2px 7px;">
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