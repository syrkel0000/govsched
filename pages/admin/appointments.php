<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM appointments WHERE id = ?")->execute([$del_id]);
    header('Location: appointments.php');
    exit();
}

// Handle status update
if (isset($_POST['update_status'])) {
    $upd_id = intval($_POST['appointment_id']);
    $status = $_POST['status'];
    $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?")->execute([$status, $upd_id]);
    header('Location: appointments.php');
    exit();
}

$appointments = $pdo->query("
    SELECT a.*, u.full_name as applicant_name, d.name as document_name, t.slot_time
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN documents d ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id = t.id
    ORDER BY a.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | Appointments</title>
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
        <a href="/govsched/pages/admin/dashboard.php" class="brand-link">
            <span class="brand-text font-weight-light"><b>Gov</b>Sched Admin</span>
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
                        <a href="appointments.php" class="nav-link active">
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

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Appointments</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Appointments</h3>
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
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $row): ?>
                                <tr>
                                    <td><?= $row['reference_no'] ?></td>
                                    <td><?= $row['applicant_name'] ?></td>
                                    <td><?= $row['document_name'] ?></td>
                                    <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                                    <td><?= $row['slot_time'] ?></td>
                                    <td><?= ucfirst($row['request_type']) ?></td>
                                    <td>
                                        <?php
                                        $badge = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger'];
                                        $s = $row['status'];
                                        ?>
                                        <span class="badge badge-<?= $badge[$s] ?>"><?= ucfirst($s) ?></span>
                                    </td>
                                    <td>
                                        <a href="view_appointment.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Update Status -->
                                        <form action="appointments.php" method="POST" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                                            <select name="status" class="form-control form-control-sm d-inline w-auto" onchange="this.form.submit()">
                                                <option value="pending" <?= $s==='pending'?'selected':'' ?>>Pending</option>
                                                <option value="confirmed" <?= $s==='confirmed'?'selected':'' ?>>Confirmed</option>
                                                <option value="cancelled" <?= $s==='cancelled'?'selected':'' ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                        <a href="appointments.php?delete=<?= $row['id'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this appointment?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($appointments)): ?>
                                <tr><td colspan="8" class="text-center">No appointments yet.</td></tr>
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