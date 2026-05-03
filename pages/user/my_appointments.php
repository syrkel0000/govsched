<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireApplicant();

$error = '';

// Cancel appointment
if (isset($_GET['cancel'])) {
    $cancel_id = (int)$_GET['cancel'];
    $check = $pdo->prepare("SELECT id, status FROM appointments WHERE id = ? AND user_id = ?");
    $check->execute([$cancel_id, $_SESSION['user_id']]);
    $row = $check->fetch();
    if ($row && $row['status'] === 'pending') {
        $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?")
            ->execute([$cancel_id]);
        header('Location: my_appointments.php?msg=cancelled');
        exit();
    } else {
        $error = 'Only pending appointments can be cancelled.';
    }
}

$stmt = $pdo->prepare("
    SELECT a.*, d.name AS document_name, d.agency,
           t.slot_time, b.name AS branch_name, b.city
    FROM appointments a
    JOIN documents d  ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id     = t.id
    JOIN branches b   ON a.branch_id   = b.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | My Appointments</title>
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
                <h1 class="m-0">My Appointments</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cancelled'): ?>
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-times-circle"></i> Appointment cancelled successfully.
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All My Appointments</h3>
                        <div class="card-tools">
                            <a href="book.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Book New
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Reference No</th>
                                    <th>Document</th>
                                    <th>Branch</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $row):
                                    $badge = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger'];
                                    $s = $row['status'];
                                ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($row['reference_no']) ?></code></td>
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
                                    <td style="white-space:nowrap;">
                                        <a href="receipt.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($s === 'pending'): ?>
                                        <a href="my_appointments.php?cancel=<?= $row['id'] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Cancel this appointment?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($appointments)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-3">No appointments yet.</td>
                                </tr>
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