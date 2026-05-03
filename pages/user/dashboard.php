<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireApplicant();

$stats = $pdo->prepare("
    SELECT
        COUNT(*) as total,
        SUM(status = 'pending')   as pending,
        SUM(status = 'confirmed') as confirmed,
        SUM(status = 'cancelled') as cancelled
    FROM appointments WHERE user_id = ?
");
$stats->execute([$_SESSION['user_id']]);
$stats = $stats->fetch();

$activeStmt = $pdo->prepare("
    SELECT a.*, d.name AS document_name, d.agency,
           t.slot_time, b.name AS branch_name, b.city, b.address AS branch_address
    FROM appointments a
    JOIN documents d  ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id     = t.id
    JOIN branches b   ON a.branch_id   = b.id
    WHERE a.user_id = ? AND a.status IN ('pending','confirmed')
    ORDER BY a.appointment_date ASC
    LIMIT 1
");
$activeStmt->execute([$_SESSION['user_id']]);
$active = $activeStmt->fetch();

$recentStmt = $pdo->prepare("
    SELECT a.*, d.name AS document_name, d.agency,
           t.slot_time, b.name AS branch_name
    FROM appointments a
    JOIN documents d  ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id     = t.id
    JOIN branches b   ON a.branch_id   = b.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
    LIMIT 5
");
$recentStmt->execute([$_SESSION['user_id']]);
$appointments = $recentStmt->fetchAll();

$badge = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger'];
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
            background: linear-gradient(135deg, #1a3a6b, #2563b0);
            color: white;
            border-radius: 10px;
            padding: 28px 32px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .welcome-banner::after {
            content: '\f19c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 30px; top: 50%;
            transform: translateY(-50%);
            font-size: 90px;
            opacity: 0.07;
            color: white;
        }
        .welcome-banner h4 { margin: 0 0 6px 0; font-size: 1.5rem; font-weight: 700; }
        .welcome-banner p  { margin: 0; opacity: 0.85; font-size: 0.95rem; }
        .welcome-banner .date-badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }

        /* Active appointment card */
        .appt-card-confirmed { border-left: 5px solid #28a745; }
        .appt-card-pending   { border-left: 5px solid #ffc107; }

        /* Countdown */
        .countdown-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px 16px;
            text-align: center;
            margin-top: 10px;
        }
        .countdown-box .days {
            font-size: 2rem;
            font-weight: 700;
            color: #1a3a6b;
            line-height: 1;
        }
        .countdown-box .label {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Steps */
        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
        }
        .step-num {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: #1a3a6b;
            color: white;
            font-size: 12px;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .step-text p  { margin: 0; font-size: 13px; font-weight: 600; color: #343a40; }
        .step-text span { font-size: 11.5px; color: #6c757d; }

        /* Recent list */
        .appt-list-item { transition: background .15s; }
        .appt-list-item:hover { background: #f8f9fa; }
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
                    <div class="date-badge">
                        <i class="fas fa-calendar-day mr-1"></i><?= date('l, F d, Y') ?>
                    </div>
                    <h4><i class="fas fa-landmark mr-2"></i>Hello, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h4>
                    <p>Welcome to GovSched — your government document appointment portal.</p>
                </div>

                <!-- Stat Cards -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner"><h3><?= $stats['total'] ?></h3><p>Total Appointments</p></div>
                            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                            <a href="my_appointments.php" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner"><h3><?= $stats['pending'] ?></h3><p>Pending</p></div>
                            <div class="icon"><i class="fas fa-clock"></i></div>
                            <a href="my_appointments.php" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner"><h3><?= $stats['confirmed'] ?></h3><p>Confirmed</p></div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                            <a href="my_appointments.php" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner"><h3><?= $stats['cancelled'] ?></h3><p>Cancelled</p></div>
                            <div class="icon"><i class="fas fa-times-circle"></i></div>
                            <a href="my_appointments.php" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Main Row -->
                <div class="row">

                    <!-- Active / Upcoming Appointment -->
                    <div class="col-md-5">
                        <?php if ($active):
                            $apptDate  = new DateTime($active['appointment_date']);
                            $today     = new DateTime(date('Y-m-d'));
                            $daysLeft  = (int)$today->diff($apptDate)->days;
                            $isPast    = $apptDate < $today;
                            $s         = $active['status'];
                        ?>
                        <div class="card appt-card-<?= $s ?>">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-calendar-check mr-1 text-<?= $s === 'confirmed' ? 'success' : 'warning' ?>"></i>
                                    Upcoming Appointment
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-<?= $badge[$s] ?>"><?= ucfirst($s) ?></span>
                                </div>
                            </div>
                            <div class="card-body">

                                <?php if (!$isPast): ?>
                                <div class="countdown-box mb-3">
                                    <div class="days"><?= $daysLeft === 0 ? 'TODAY' : $daysLeft ?></div>
                                    <div class="label"><?= $daysLeft === 0 ? 'Your appointment is today!' : 'day' . ($daysLeft > 1 ? 's' : '') . ' remaining' ?></div>
                                </div>
                                <?php endif; ?>

                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <th width="38%"><i class="fas fa-hashtag mr-1 text-muted"></i> Ref No</th>
                                        <td><code><?= htmlspecialchars($active['reference_no']) ?></code></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-file-alt mr-1 text-muted"></i> Document</th>
                                        <td>
                                            <?= htmlspecialchars($active['document_name']) ?>
                                            <small class="text-muted d-block"><?= htmlspecialchars($active['agency']) ?></small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-building mr-1 text-muted"></i> Branch</th>
                                        <td>
                                            <?= htmlspecialchars($active['branch_name']) ?>
                                            <small class="text-muted d-block"><?= htmlspecialchars($active['city']) ?></small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-map-marker-alt mr-1 text-muted"></i> Address</th>
                                        <td><small><?= htmlspecialchars($active['branch_address']) ?></small></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-calendar mr-1 text-muted"></i> Date</th>
                                        <td><?= date('F d, Y', strtotime($active['appointment_date'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-clock mr-1 text-muted"></i> Time</th>
                                        <td><?= htmlspecialchars($active['slot_time']) ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <a href="receipt.php?id=<?= $active['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View Receipt
                                </a>
                                <?php if ($s === 'pending'): ?>
                                <a href="my_appointments.php?cancel=<?= $active['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Cancel this appointment?')">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-1 font-weight-bold">No Upcoming Appointments</p>
                                <p class="text-muted small mb-3">You don't have any active bookings right now.</p>
                                <a href="book.php" class="btn btn-primary">
                                    <i class="fas fa-calendar-plus"></i> Book an Appointment
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions + How It Works -->
                    <div class="col-md-3">

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-bolt mr-1"></i> Quick Actions</h3>
                            </div>
                            <div class="card-body p-2">
                                <a href="book.php" class="btn btn-primary btn-block mb-2">
                                    <i class="fas fa-calendar-plus mr-1"></i> Book Appointment
                                </a>
                                <a href="my_appointments.php" class="btn btn-default btn-block mb-2">
                                    <i class="fas fa-list mr-1"></i> My Appointments
                                </a>
                                <a href="profile.php" class="btn btn-default btn-block">
                                    <i class="fas fa-user-edit mr-1"></i> Edit Profile
                                </a>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> How It Works</h3>
                            </div>
                            <div class="card-body">
                                <div class="step-item">
                                    <div class="step-num">1</div>
                                    <div class="step-text">
                                        <p>Select Document</p>
                                        <span>Choose what you need from the agency list</span>
                                    </div>
                                </div>
                                <div class="step-item">
                                    <div class="step-num">2</div>
                                    <div class="step-text">
                                        <p>Pick Branch & Schedule</p>
                                        <span>Select your preferred office, date and time</span>
                                    </div>
                                </div>
                                <div class="step-item">
                                    <div class="step-num">3</div>
                                    <div class="step-text">
                                        <p>Get Reference Number</p>
                                        <span>Save your receipt and show up on time</span>
                                    </div>
                                </div>
                                <div class="step-item mb-0">
                                    <div class="step-num">4</div>
                                    <div class="step-text">
                                        <p>Bring Valid ID</p>
                                        <span>Arrive 10 minutes before your slot</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Recent Activity -->
                    <div class="col-md-4">
                        <div class="card" style="height:calc(100% - 1rem);">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-history mr-1"></i> Recent Activity</h3>
                                <div class="card-tools">
                                    <a href="my_appointments.php" class="btn btn-sm btn-default">View All</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($appointments)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0 small">No activity yet.</p>
                                </div>
                                <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($appointments as $row):
                                        $s = $row['status'];
                                    ?>
                                    <li class="list-group-item px-3 py-2 appt-list-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div style="min-width:0;">
                                                <div class="font-weight-bold small text-truncate">
                                                    <?= htmlspecialchars($row['document_name']) ?>
                                                </div>
                                                <div class="text-muted" style="font-size:0.76rem;">
                                                    <i class="fas fa-building mr-1"></i><?= htmlspecialchars($row['branch_name']) ?>
                                                </div>
                                                <div class="text-muted" style="font-size:0.76rem;">
                                                    <i class="fas fa-calendar mr-1"></i><?= date('M d, Y', strtotime($row['appointment_date'])) ?>
                                                    &bull; <?= htmlspecialchars($row['slot_time']) ?>
                                                </div>
                                            </div>
                                            <div class="text-right ml-2 flex-shrink-0">
                                                <span class="badge badge-<?= $badge[$s] ?> mb-1 d-block"><?= ucfirst($s) ?></span>
                                                <a href="receipt.php?id=<?= $row['id'] ?>"
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