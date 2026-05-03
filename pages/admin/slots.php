<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireAdmin();

$error = '';

// Add slot
if (isset($_POST['add_slot'])) {
    $branch_id    = (int)$_POST['branch_id'];
    $slot_time    = trim($_POST['slot_time']);
    $max_capacity = (int)$_POST['max_capacity'];

    if (!$branch_id || empty($slot_time)) {
        $error = 'All fields are required.';
    } elseif ($max_capacity < 1) {
        $error = 'Capacity must be at least 1.';
    } else {
        $exists = $pdo->prepare("SELECT id FROM time_slots WHERE slot_time = ? AND branch_id = ?");
        $exists->execute([$slot_time, $branch_id]);
        if ($exists->fetch()) {
            $error = 'That time slot already exists for this branch.';
        } else {
            $pdo->prepare("INSERT INTO time_slots (branch_id, slot_time, max_capacity) VALUES (?, ?, ?)")
                ->execute([$branch_id, $slot_time, $max_capacity]);
            header('Location: slots.php?msg=added');
            exit();
        }
    }
}

// Update capacity
if (isset($_POST['update_capacity'])) {
    $slot_id      = (int)$_POST['slot_id'];
    $max_capacity = (int)$_POST['max_capacity'];
    if ($max_capacity < 1) {
        $error = 'Capacity must be at least 1.';
    } else {
        $pdo->prepare("UPDATE time_slots SET max_capacity = ? WHERE id = ?")
            ->execute([$max_capacity, $slot_id]);
        header('Location: slots.php?msg=updated');
        exit();
    }
}

// Delete slot
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $active = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE slot_id = ? AND status IN ('pending','confirmed')");
    $active->execute([$del_id]);
    if ($active->fetchColumn() > 0) {
        $error = 'Cannot delete — this slot has active appointments.';
    } else {
        $pdo->prepare("DELETE FROM time_slots WHERE id = ?")->execute([$del_id]);
        header('Location: slots.php?msg=deleted');
        exit();
    }
}

// Fetch all branches
$branches = $pdo->query("SELECT * FROM branches ORDER BY city, agency, name")->fetchAll();

// Filter by branch
$filter_branch = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : 0;

$query = "
    SELECT ts.*, b.name AS branch_name, b.city, b.agency,
           COUNT(a.id) AS total_booked
    FROM time_slots ts
    JOIN branches b ON b.id = ts.branch_id
    LEFT JOIN appointments a ON a.slot_id = ts.id AND a.status != 'cancelled'
";
if ($filter_branch) {
    $query .= " WHERE ts.branch_id = $filter_branch";
}
$query .= " GROUP BY ts.id ORDER BY b.city, b.agency, b.name, ts.slot_time";

$slots = $pdo->query($query)->fetchAll();

// Group by branch_id
$grouped = [];
foreach ($slots as $slot) {
    $grouped[$slot['branch_id']][] = $slot;
}

// Branches to display — all or filtered
$display_branches = $filter_branch
    ? array_filter($branches, fn($b) => $b['id'] === $filter_branch)
    : $branches;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | Slot Management</title>
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
                        <a href="slots.php" class="nav-link active">
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
                <h1 class="m-0">Slot Management</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <?php if (isset($_GET['msg'])): ?>
                <?php $msgs = ['added'=>['success','Time slot added.'],'updated'=>['success','Capacity updated.'],'deleted'=>['warning','Time slot deleted.']]; ?>
                <?php if (isset($msgs[$_GET['msg']])): [$type,$text] = $msgs[$_GET['msg']]; ?>
                <div class="alert alert-<?= $type ?> alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $text ?>
                </div>
                <?php endif; endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>



                <!-- Add Slot Form -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-plus mr-1"></i> Add New Time Slot</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="slots.php" class="form-inline flex-wrap" style="gap:10px;">
                            <div class="form-group">
                                <label class="mr-2">Branch</label>
                                <select name="branch_id" class="form-control" required>
                                    <option value="">-- Select Branch --</option>
                                    <?php
                                    $city = '';
                                    foreach ($branches as $b):
                                        if ($city !== $b['city']):
                                            if ($city) echo '</optgroup>';
                                            $city = $b['city'];
                                            echo '<optgroup label="' . htmlspecialchars($city) . '">';
                                        endif;
                                    ?>
                                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                    <?php endforeach; ?>
                                    <?php if ($city) echo '</optgroup>'; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="mr-2">Time Slot</label>
                                <select name="slot_time" class="form-control" required>
                                    <option value="">-- Select Time --</option>
                                    <option>08:00 AM - 09:00 AM</option>
                                    <option>09:00 AM - 10:00 AM</option>
                                    <option>10:00 AM - 11:00 AM</option>
                                    <option>11:00 AM - 12:00 PM</option>
                                    <option>01:00 PM - 02:00 PM</option>
                                    <option>02:00 PM - 03:00 PM</option>
                                    <option>03:00 PM - 04:00 PM</option>
                                    <option>04:00 PM - 05:00 PM</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="mr-2">Max Capacity</label>
                                <input type="number" name="max_capacity" class="form-control"
                                       value="20" min="1" max="100" style="width:90px;" required>
                            </div>
                            <button type="submit" name="add_slot" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Slot
                            </button>
                        </form>
                    </div>
                </div>

                                <!-- Filter -->
<div class="card card-outline card-secondary">
    <div class="card-body py-2">
        <form method="GET" action="slots.php" class="form-inline" style="gap:10px;">
            <div class="form-group">
                <label class="mr-2"><i class="fas fa-filter mr-1"></i>Filter by Branch</label>
                <select name="branch_id" class="form-control">
                    <option value="">-- All Branches --</option>
                    <?php
                    $city = '';
                    foreach ($branches as $b):
                        if ($city !== $b['city']):
                            if ($city) echo '</optgroup>';
                            $city = $b['city'];
                            echo '<optgroup label="' . htmlspecialchars($city) . '">';
                        endif;
                    ?>
                        <option value="<?= $b['id'] ?>" <?= $filter_branch === $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if ($city) echo '</optgroup>'; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
            </button>
            <?php if ($filter_branch): ?>
            <a href="slots.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Clear
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

                <!-- Slots per Branch -->
               <?php foreach ($display_branches as $b): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-building mr-1"></i>
                            <?= htmlspecialchars($b['name']) ?>
                            <small class="text-muted ml-1">(<?= htmlspecialchars($b['city']) ?>)</small>
                            <span class="badge badge-primary ml-2">
                                <?= count($grouped[$b['id']] ?? []) ?> slots
                            </span>
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Time Slot</th>
                                    <th>Max Capacity</th>
                                    <th>Total Booked (All Time)</th>
                                    <th>Update Capacity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($grouped[$b['id']])): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No slots for this branch.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($grouped[$b['id']] as $i => $slot): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($slot['slot_time']) ?></strong></td>
                                    <td><span class="badge badge-info"><?= $slot['max_capacity'] ?> max</span></td>
                                    <td>
                                        <?php
                                        $pct = $slot['max_capacity'] > 0
                                            ? min(100, round(($slot['total_booked'] / $slot['max_capacity']) * 100))
                                            : 0;
                                        $bar = $pct >= 100 ? 'danger' : ($pct >= 60 ? 'warning' : 'success');
                                        ?>
                                        <div style="min-width:140px;">
                                            <?= $slot['total_booked'] ?> booked
                                            <div class="progress progress-sm mt-1">
                                                <div class="progress-bar bg-<?= $bar ?>" style="width:<?= $pct ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" action="slots.php" class="form-inline">
                                            <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
                                            <input type="number" name="max_capacity"
                                                   class="form-control form-control-sm mr-2"
                                                   value="<?= $slot['max_capacity'] ?>"
                                                   min="1" max="100" style="width:75px;" required>
                                            <button type="submit" name="update_capacity" class="btn btn-sm btn-success">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <a href="slots.php?delete=<?= $slot['id'] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this time slot?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endforeach; ?>

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