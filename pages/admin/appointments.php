<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM appointments WHERE id = ?")->execute([$del_id]);
    header('Location: appointments.php?msg=deleted');
    exit();
}

// Handle status update
if (isset($_POST['update_status'])) {
    $upd_id = intval($_POST['appointment_id']);
    $status = $_POST['status'];
    $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?")->execute([$status, $upd_id]);
    header('Location: appointments.php?msg=updated');
    exit();
}

// Filters
$where = "WHERE 1=1";
$params = [];

if (!empty($_GET['office'])) {
    $where .= " AND a.office = ?";
    $params[] = $_GET['office'];
}
if (!empty($_GET['status'])) {
    $where .= " AND a.status = ?";
    $params[] = $_GET['status'];
}
if (!empty($_GET['date'])) {
    $where .= " AND a.appointment_date = ?";
    $params[] = $_GET['date'];
}
if (!empty($_GET['search'])) {
    $where .= " AND (a.reference_no LIKE ? OR u.full_name LIKE ? OR a.full_name LIKE ?)";
    $q = '%' . $_GET['search'] . '%';
    $params[] = $q;
    $params[] = $q;
    $params[] = $q;
}

$stmt = $pdo->prepare("
    SELECT a.*, u.full_name as applicant_name, d.name as document_name, t.slot_time
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN documents d ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id = t.id
    $where
    ORDER BY a.appointment_date ASC, t.slot_time ASC
");
$stmt->execute($params);
$appointments = $stmt->fetchAll();
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
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="appointments.php" class="nav-link active">
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
                <h1 class="m-0">Appointments</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <!-- Flash Messages -->
                <?php if (isset($_GET['msg'])): ?>
                    <?php if ($_GET['msg'] === 'updated'): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fas fa-check-circle"></i> Status updated successfully.
                        </div>
                    <?php elseif ($_GET['msg'] === 'deleted'): ?>
                        <div class="alert alert-warning alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fas fa-trash"></i> Appointment deleted.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Filter & Search</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="appointments.php" class="form-inline flex-wrap" style="gap:8px;">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Ref no / Name"
                                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

                            <select name="office" class="form-control">
                                <option value="">All Offices</option>
                                <option value="Cabanatuan City" <?= ($_GET['office'] ?? '') === 'Cabanatuan City' ? 'selected' : '' ?>>Cabanatuan City</option>
                                <option value="Palayan City" <?= ($_GET['office'] ?? '') === 'Palayan City' ? 'selected' : '' ?>>Palayan City</option>
                            </select>

                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="pending"   <?= ($_GET['status'] ?? '') === 'pending'   ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= ($_GET['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>

                            <input type="date" name="date" class="form-control"
                                   value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="appointments.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            All Appointments
                            <span class="badge badge-primary ml-2"><?= count($appointments) ?></span>
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Reference No</th>
                                    <th>Applicant</th>
                                    <th>Document</th>
                                    <th>Office</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $row): ?>
                                <?php
                                    $badge = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger'];
                                    $s = $row['status'];
                                ?>
                                <tr>
                                    <td><code><?= $row['reference_no'] ?></code></td>
                                    <td><?= htmlspecialchars($row['applicant_name']) ?></td>
                                    <td><?= htmlspecialchars($row['document_name']) ?></td>
                                    <td><?= htmlspecialchars($row['office']) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                                    <td><?= $row['slot_time'] ?></td>
                                    <td><?= ucfirst($row['request_type']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $badge[$s] ?>"><?= ucfirst($s) ?></span>
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <a href="view_appointment.php?id=<?= $row['id'] ?>"
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="appointments.php" method="POST" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="update_status" value="1">
                                            <select name="status" class="form-control form-control-sm d-inline w-auto"
                                                    onchange="this.form.submit()">
                                                <option value="pending"   <?= $s==='pending'   ?'selected':'' ?>>Pending</option>
                                                <option value="confirmed" <?= $s==='confirmed' ?'selected':'' ?>>Confirmed</option>
                                                <option value="cancelled" <?= $s==='cancelled' ?'selected':'' ?>>Cancelled</option>
                                            </select>
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
                                <tr><td colspan="9" class="text-center text-muted py-3">No appointments found.</td></tr>
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