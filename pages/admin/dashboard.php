<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireAdmin();

$total_appointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$pending            = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn();
$confirmed          = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='confirmed'")->fetchColumn();
$cancelled          = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='cancelled'")->fetchColumn();

$today_total     = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()")->fetchColumn();
$today_pending   = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status = 'pending'")->fetchColumn();
$today_confirmed = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status = 'confirmed'")->fetchColumn();

$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'applicant'")->fetchColumn();

$agency_stats = $pdo->query("
    SELECT d.agency, COUNT(a.id) AS total
    FROM appointments a
    JOIN documents d ON a.document_id = d.id
    GROUP BY d.agency ORDER BY total DESC
")->fetchAll();

$doc_stats = $pdo->query("
    SELECT d.name, d.agency, COUNT(a.id) AS total
    FROM documents d
    LEFT JOIN appointments a ON a.document_id = d.id
    GROUP BY d.id ORDER BY total DESC LIMIT 5
")->fetchAll();

$recent = $pdo->query("
    SELECT a.*, u.full_name AS applicant_name, d.name AS document_name,
           t.slot_time, b.name AS branch_name
    FROM appointments a
    JOIN users u      ON a.user_id     = u.id
    JOIN documents d  ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id     = t.id
    JOIN branches b   ON a.branch_id   = b.id
    ORDER BY a.created_at DESC LIMIT 8
")->fetchAll();

$upcoming_today = $pdo->query("
    SELECT a.*, u.full_name AS applicant_name, d.name AS document_name,
           t.slot_time, b.name AS branch_name, b.city
    FROM appointments a
    JOIN users u      ON a.user_id     = u.id
    JOIN documents d  ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id     = t.id
    JOIN branches b   ON a.branch_id   = b.id
    WHERE a.appointment_date = CURDATE() AND a.status != 'cancelled'
    ORDER BY t.slot_time ASC LIMIT 10
")->fetchAll();

$colors = ['PSA'=>'primary','LTO'=>'info','SSS'=>'success','Pag-IBIG'=>'warning','PhilHealth'=>'danger','PhilSys'=>'secondary'];
$badge  = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger'];
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
        /* ── Banner ── */
        .admin-banner {
            background: linear-gradient(135deg, #0d1f3c 0%, #1a3a6b 60%, #2563b0 100%);
            color: #fff;
            border-radius: 12px;
            padding: 28px 36px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .admin-banner::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                repeating-linear-gradient(45deg, rgba(255,255,255,.03) 0, rgba(255,255,255,.03) 1px, transparent 1px, transparent 60px),
                repeating-linear-gradient(-45deg, rgba(255,255,255,.03) 0, rgba(255,255,255,.03) 1px, transparent 1px, transparent 60px);
        }
        .admin-banner::after {
            content: '\f505';
            font-family: 'Font Awesome 5 Free'; font-weight: 900;
            position: absolute; right: 36px; top: 50%; transform: translateY(-50%);
            font-size: 100px; opacity: .06; color: #fff;
        }
        .admin-banner h4 { font-size: 1.5rem; font-weight: 700; margin: 0 0 6px; }
        .admin-banner p  { margin: 0; opacity: .8; font-size: .92rem; }
        .admin-banner .today-pills { margin-top: 14px; display: flex; flex-wrap: wrap; gap: 8px; }
        .today-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 16px; border-radius: 20px; font-size: .82rem; font-weight: 500;
        }
        .today-pill.all      { background: rgba(255,255,255,.15); color: #fff; }
        .today-pill.pending  { background: #ffc107; color: #212529; }
        .today-pill.confirmed{ background: #28a745; color: #fff; }

        /* ── Info cards ── */
        .info-card {
            border-radius: 10px; padding: 20px 22px;
            color: #fff; position: relative; overflow: hidden;
            margin-bottom: 20px;
        }
        .info-card .ic-label { font-size: .78rem; text-transform: uppercase; letter-spacing: 1px; opacity: .85; }
        .info-card .ic-value { font-size: 2.2rem; font-weight: 700; line-height: 1.1; margin: 4px 0; }
        .info-card .ic-sub   { font-size: .78rem; opacity: .75; }
        .info-card .ic-icon  {
            position: absolute; right: 18px; top: 50%; transform: translateY(-50%);
            font-size: 48px; opacity: .15;
        }
        .info-card .ic-footer {
            margin-top: 14px; padding-top: 10px;
            border-top: 1px solid rgba(255,255,255,.2);
            font-size: .78rem;
        }
        .info-card .ic-footer a { color: rgba(255,255,255,.85); text-decoration: none; }
        .info-card .ic-footer a:hover { color: #fff; }
        .ic-blue   { background: linear-gradient(135deg,#1a3a6b,#2563b0); }
        .ic-orange { background: linear-gradient(135deg,#d97706,#f59e0b); }
        .ic-green  { background: linear-gradient(135deg,#15803d,#22c55e); }
        .ic-slate  { background: linear-gradient(135deg,#374151,#6b7280); }

        /* ── Section cards ── */
        .section-card { border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.06); margin-bottom: 20px; }
        .section-card .card-header {
            border-radius: 10px 10px 0 0 !important;
            background: #fff; border-bottom: 1px solid #f0f0f0;
            padding: 14px 18px;
        }
        .section-card .card-title { font-size: .92rem; font-weight: 600; color: #1a3a6b; margin: 0; }

        /* ── Schedule list ── */
        .schedule-item {
            padding: 10px 18px;
            border-bottom: 1px solid #f5f5f5;
            transition: background .15s;
        }
        .schedule-item:last-child { border-bottom: none; }
        .schedule-item:hover { background: #f8faff; }
        .schedule-item .time-badge {
            background: #1a3a6b; color: #fff;
            border-radius: 6px; padding: 2px 8px;
            font-size: .72rem; font-weight: 600;
            white-space: nowrap;
        }
        .schedule-item .name   { font-size: .85rem; font-weight: 600; color: #212529; }
        .schedule-item .detail { font-size: .74rem; color: #6c757d; }

        /* ── Agency bars ── */
        .agency-row { margin-bottom: 14px; }
        .agency-row .agency-label { font-size: .8rem; font-weight: 600; color: #374151; }
        .agency-row .agency-count { font-size: .75rem; color: #6c757d; }
        .agency-row .progress { height: 8px; border-radius: 4px; }

        /* ── Top docs ── */
        .doc-rank-item {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 16px; border-bottom: 1px solid #f5f5f5;
        }
        .doc-rank-item:last-child { border-bottom: none; }
        .doc-rank-num {
            width: 22px; height: 22px; border-radius: 50%;
            background: #1a3a6b; color: #fff;
            font-size: .7rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .doc-rank-name { font-size: .82rem; font-weight: 500; color: #212529; flex: 1; }
        .doc-rank-agency { font-size: .7rem; color: #6c757d; }

        /* ── Recent list ── */
        .recent-item {
            padding: 10px 16px; border-bottom: 1px solid #f5f5f5;
            transition: background .15s;
        }
        .recent-item:last-child { border-bottom: none; }
        .recent-item:hover { background: #f8faff; }
        .recent-item .r-name   { font-size: .83rem; font-weight: 600; color: #212529; }
        .recent-item .r-detail { font-size: .73rem; color: #6c757d; }
        .progress-label { font-size: 0.8rem; }
    </style>
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
                <span class="nav-link text-muted" style="font-size:.85rem;">
                    <i class="fas fa-calendar-day mr-1"></i><?= date('l, F d, Y') ?>
                </span>
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

    <!-- Content -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Dashboard</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <!-- Admin Banner -->
                <div class="admin-banner">
                    <div class="row align-items-center">
                        <div class="col-md-7" style="position:relative;z-index:1;">
                            <h4><i class="fas fa-shield-alt mr-2"></i>Admin Panel — GovSched</h4>
                            <p>Welcome back, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>. Here's your system overview for today.</p>
                            <div class="today-pills">
                                <span class="today-pill all">
                                    <i class="fas fa-calendar-day"></i>
                                    Today: <?= $today_total ?> appointment<?= $today_total != 1 ? 's' : '' ?>
                                </span>
                                <span class="today-pill pending">
                                    <i class="fas fa-clock"></i> <?= $today_pending ?> Pending
                                </span>
                                <span class="today-pill confirmed">
                                    <i class="fas fa-check"></i> <?= $today_confirmed ?> Confirmed
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="info-card ic-blue">
                            <div class="ic-label">Total Appointments</div>
                            <div class="ic-value"><?= $total_appointments ?></div>
                            <div class="ic-sub">All time records</div>
                            <i class="fas fa-calendar-alt ic-icon"></i>
                            <div class="ic-footer">
                                <a href="appointments.php"><i class="fas fa-arrow-right mr-1"></i>Manage Appointments</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="info-card ic-orange">
                            <div class="ic-label">Pending</div>
                            <div class="ic-value"><?= $pending ?></div>
                            <div class="ic-sub">Awaiting action</div>
                            <i class="fas fa-clock ic-icon"></i>
                            <div class="ic-footer">
                                <a href="appointments.php?status=pending"><i class="fas fa-arrow-right mr-1"></i>View Pending</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="info-card ic-green">
                            <div class="ic-label">Confirmed</div>
                            <div class="ic-value"><?= $confirmed ?></div>
                            <div class="ic-sub">Successfully booked</div>
                            <i class="fas fa-check-circle ic-icon"></i>
                            <div class="ic-footer">
                                <a href="appointments.php?status=confirmed"><i class="fas fa-arrow-right mr-1"></i>View Confirmed</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="info-card ic-slate">
                            <div class="ic-label">Registered Applicants</div>
                            <div class="ic-value"><?= $total_users ?></div>
                            <div class="ic-sub">Total user accounts</div>
                            <i class="fas fa-users ic-icon"></i>
                            <div class="ic-footer">
                                <a href="users.php"><i class="fas fa-arrow-right mr-1"></i>View Users</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="row">

                    <!-- Today's Schedule -->
                    <div class="col-md-5">
                        <div class="card section-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    <i class="fas fa-calendar-day mr-1"></i> Today's Schedule
                                    <span class="badge badge-primary ml-1"><?= count($upcoming_today) ?></span>
                                </h3>
                                <a href="appointments.php?date=<?= date('Y-m-d') ?>" class="btn btn-xs btn-outline-primary" style="font-size:.75rem;">
                                    View All
                                </a>
                            </div>
                            <div class="card-body p-0" style="max-height:380px;overflow-y:auto;">
                                <?php if (empty($upcoming_today)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                    No appointments scheduled today.
                                </div>
                                <?php else: ?>
                                <?php foreach ($upcoming_today as $row):
                                    $s = $row['status'];
                                ?>
                                <div class="schedule-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div style="min-width:0;">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="time-badge"><?= htmlspecialchars($row['slot_time']) ?></span>
                                                <span class="badge badge-<?= $badge[$s] ?> ml-1"><?= ucfirst($s) ?></span>
                                            </div>
                                            <div class="name"><?= htmlspecialchars($row['applicant_name']) ?></div>
                                            <div class="detail">
                                                <i class="fas fa-file-alt mr-1"></i><?= htmlspecialchars($row['document_name']) ?>
                                            </div>
                                            <div class="detail">
                                                <i class="fas fa-building mr-1"></i><?= htmlspecialchars($row['branch_name']) ?>
                                                <span class="text-muted">(<?= htmlspecialchars($row['city']) ?>)</span>
                                            </div>
                                        </div>
                                        <a href="view_appointment.php?id=<?= $row['id'] ?>"
                                           class="btn btn-xs btn-info ml-2 flex-shrink-0"
                                           style="font-size:.72rem;padding:3px 8px;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- By Agency + Top Documents -->
                    <div class="col-md-3">

                        <!-- By Agency -->
                        <div class="card section-card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> By Agency</h3>
                            </div>
                            <div class="card-body pb-2">
                                <?php if (empty($agency_stats)): ?>
                                <p class="text-muted text-center small mb-0">No data yet.</p>
                                <?php else: ?>
                                <?php foreach ($agency_stats as $ag):
                                    $pct   = $total_appointments > 0 ? round(($ag['total'] / $total_appointments) * 100) : 0;
                                    $color = $colors[$ag['agency']] ?? 'primary';
                                ?>
                                <div class="agency-row">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="agency-label"><?= htmlspecialchars($ag['agency']) ?></span>
                                        <span class="agency-count"><?= $ag['total'] ?> &bull; <?= $pct ?>%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-<?= $color ?>" style="width:<?= $pct ?>%"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Top Documents -->
                        <div class="card section-card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-trophy mr-1"></i> Top Documents</h3>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($doc_stats)): ?>
                                <div class="text-center py-3 text-muted small">No data yet.</div>
                                <?php else: ?>
                                <?php foreach ($doc_stats as $i => $doc): ?>
                                <div class="doc-rank-item">
                                    <div class="doc-rank-num"><?= $i + 1 ?></div>
                                    <div>
                                        <div class="doc-rank-name"><?= htmlspecialchars($doc['name']) ?></div>
                                        <div class="doc-rank-agency"><?= htmlspecialchars($doc['agency']) ?></div>
                                    </div>
                                    <span class="badge badge-<?= $colors[$doc['agency']] ?? 'primary' ?> ml-auto">
                                        <?= $doc['total'] ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div><!-- /col-md-3 -->

                    <!-- Recent Appointments -->
                    <div class="col-md-4">
                        <div class="card section-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title"><i class="fas fa-history mr-1"></i> Recent Appointments</h3>
                                <a href="appointments.php" class="btn btn-xs btn-outline-primary" style="font-size:.75rem;">View All</a>
                            </div>
                            <div class="card-body p-0" style="max-height:500px;overflow-y:auto;">
                                <?php if (empty($recent)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No appointments yet.
                                </div>
                                <?php else: ?>
                                <?php foreach ($recent as $row):
                                    $s = $row['status'];
                                ?>
                                <div class="recent-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div style="min-width:0;">
                                            <div class="r-name"><?= htmlspecialchars($row['applicant_name']) ?></div>
                                            <div class="r-detail">
                                                <i class="fas fa-file-alt mr-1"></i><?= htmlspecialchars($row['document_name']) ?>
                                            </div>
                                            <div class="r-detail">
                                                <i class="fas fa-building mr-1"></i><?= htmlspecialchars($row['branch_name']) ?>
                                            </div>
                                            <div class="r-detail">
                                                <i class="fas fa-calendar mr-1"></i><?= date('M d, Y', strtotime($row['appointment_date'])) ?>
                                                &bull; <?= htmlspecialchars($row['slot_time']) ?>
                                            </div>
                                        </div>
                                        <div class="text-right ml-2 flex-shrink-0">
                                            <span class="badge badge-<?= $badge[$s] ?> d-block mb-1"><?= ucfirst($s) ?></span>
                                            <a href="view_appointment.php?id=<?= $row['id'] ?>"
                                               class="btn btn-xs btn-info"
                                               style="font-size:.72rem;padding:2px 7px;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div><!-- /col-md-4 -->

                </div><!-- /row -->

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