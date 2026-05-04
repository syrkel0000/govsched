<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireAdmin();

$error  = '';
$agencies = ['PSA', 'LTO', 'SSS', 'Pag-IBIG', 'PhilHealth', 'PhilSys'];

// Add branch
if (isset($_POST['add_branch'])) {
    $agency  = trim($_POST['agency']);
    $name    = trim($_POST['name']);
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);
    $city    = trim($_POST['city']);

    if (!$agency || !$name || !$address || !$city) {
        $error = 'Agency, Name, Address, and City are required.';
    } else {
        $exists = $pdo->prepare("SELECT id FROM branches WHERE name = ? AND agency = ?");
        $exists->execute([$name, $agency]);
        if ($exists->fetch()) {
            $error = 'That branch already exists under this agency.';
        } else {
            $pdo->prepare("INSERT INTO branches (agency, name, address, contact, city) VALUES (?, ?, ?, ?, ?)")
                ->execute([$agency, $name, $address, $contact ?: null, $city]);
            header('Location: branches.php?msg=added');
            exit();
        }
    }
}

// Update branch
if (isset($_POST['update_branch'])) {
    $branch_id = (int)$_POST['branch_id'];
    $agency    = trim($_POST['agency']);
    $name      = trim($_POST['name']);
    $address   = trim($_POST['address']);
    $contact   = trim($_POST['contact']);
    $city      = trim($_POST['city']);

    if (!$agency || !$name || !$address || !$city) {
        $error = 'Agency, Name, Address, and City are required.';
    } else {
        $exists = $pdo->prepare("SELECT id FROM branches WHERE name = ? AND agency = ? AND id != ?");
        $exists->execute([$name, $agency, $branch_id]);
        if ($exists->fetch()) {
            $error = 'That branch name already exists under this agency.';
        } else {
            $pdo->prepare("UPDATE branches SET agency = ?, name = ?, address = ?, contact = ?, city = ? WHERE id = ?")
                ->execute([$agency, $name, $address, $contact ?: null, $city, $branch_id]);
            header('Location: branches.php?msg=updated');
            exit();
        }
    }
}

// Delete branch
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];

    // Block if active appointments
    $active = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE branch_id = ? AND status IN ('pending','confirmed')");
    $active->execute([$del_id]);
    if ($active->fetchColumn() > 0) {
        $error = 'Cannot delete — this branch has active appointments.';
    } else {
        // Block if has slots with active appointments
        $slots = $pdo->prepare("
            SELECT COUNT(*) FROM time_slots ts
            JOIN appointments a ON a.slot_id = ts.id
            WHERE ts.branch_id = ? AND a.status IN ('pending','confirmed')
        ");
        $slots->execute([$del_id]);
        if ($slots->fetchColumn() > 0) {
            $error = 'Cannot delete — this branch has slots with active appointments.';
        } else {
            $pdo->prepare("DELETE FROM branches WHERE id = ?")->execute([$del_id]);
            header('Location: branches.php?msg=deleted');
            exit();
        }
    }
}

// Fetch branches grouped by agency
$branches = $pdo->query("
    SELECT b.*,
           COUNT(DISTINCT ts.id) AS slot_count,
           COUNT(DISTINCT a.id)  AS appt_count
    FROM branches b
    LEFT JOIN time_slots ts ON ts.branch_id = b.id
    LEFT JOIN appointments a ON a.branch_id = b.id AND a.status IN ('pending','confirmed')
    GROUP BY b.id
    ORDER BY b.agency, b.city, b.name
")->fetchAll();

$grouped = [];
foreach ($branches as $b) {
    $grouped[$b['agency']][] = $b;
}

// For edit modal — get single branch if edit_id passed
$edit_branch = null;
if (isset($_GET['edit'])) {
    $es = $pdo->prepare("SELECT * FROM branches WHERE id = ?");
    $es->execute([(int)$_GET['edit']]);
    $edit_branch = $es->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | Branch Management</title>
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
                        <a href="slots.php" class="nav-link">
                            <i class="nav-icon fas fa-clock"></i><p>Slot Management</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="branches.php" class="nav-link active">
                            <i class="nav-icon fas fa-map-marker-alt"></i><p>Branches</p>
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
                <h1 class="m-0">Branch Management</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <?php if (isset($_GET['msg'])): ?>
                <?php $msgs = ['added'=>['success','Branch added.'],'updated'=>['success','Branch updated.'],'deleted'=>['warning','Branch deleted.']]; ?>
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

                <div class="row">

                    <!-- Add Branch Form -->
                    <div class="col-md-4">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-plus mr-1"></i>
                                    <?= $edit_branch ? 'Edit Branch' : 'Add New Branch' ?>
                                </h3>
                                <?php if ($edit_branch): ?>
                                <div class="card-tools">
                                    <a href="branches.php" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if ($edit_branch): ?>
                                <form method="POST" action="branches.php">
                                    <input type="hidden" name="branch_id" value="<?= $edit_branch['id'] ?>">
                                    <div class="form-group">
                                        <label>Agency <span class="text-danger">*</span></label>
                                        <select name="agency" class="form-control" required>
                                            <option value="">-- Select Agency --</option>
                                            <?php foreach ($agencies as $ag): ?>
                                                <option value="<?= $ag ?>" <?= $edit_branch['agency'] === $ag ? 'selected' : '' ?>>
                                                    <?= $ag ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Branch Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control"
                                               value="<?= htmlspecialchars($edit_branch['name']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>City <span class="text-danger">*</span></label>
                                        <input type="text" name="city" class="form-control"
                                               value="<?= htmlspecialchars($edit_branch['city']) ?>"
                                               placeholder="e.g. Cabanatuan City" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Address <span class="text-danger">*</span></label>
                                        <textarea name="address" class="form-control" rows="2" required><?= htmlspecialchars($edit_branch['address']) ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Contact <small class="text-muted">(optional)</small></label>
                                        <input type="text" name="contact" class="form-control"
                                               value="<?= htmlspecialchars($edit_branch['contact'] ?? '') ?>"
                                               placeholder="e.g. (044) 123-4567">
                                    </div>
                                    <button type="submit" name="update_branch" class="btn btn-success btn-block">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="POST" action="branches.php">
                                    <div class="form-group">
                                        <label>Agency <span class="text-danger">*</span></label>
                                        <select name="agency" class="form-control" required>
                                            <option value="">-- Select Agency --</option>
                                            <?php foreach ($agencies as $ag): ?>
                                                <option value="<?= $ag ?>"><?= $ag ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Branch Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control"
                                               placeholder="e.g. PSA - Harrison Building" required>
                                    </div>
                                    <div class="form-group">
                                        <label>City <span class="text-danger">*</span></label>
                                        <input type="text" name="city" class="form-control"
                                               placeholder="e.g. Cabanatuan City" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Address <span class="text-danger">*</span></label>
                                        <textarea name="address" class="form-control" rows="2"
                                                  placeholder="Full address" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Contact <small class="text-muted">(optional)</small></label>
                                        <input type="text" name="contact" class="form-control"
                                               placeholder="e.g. (044) 123-4567">
                                    </div>
                                    <button type="submit" name="add_branch" class="btn btn-primary btn-block">
                                        <i class="fas fa-plus"></i> Add Branch
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Branch List grouped by agency -->
                    <div class="col-md-8">

                        <?php if (empty($branches)): ?>
                        <div class="card">
                            <div class="card-body text-center text-muted py-4">No branches yet.</div>
                        </div>
                        <?php endif; ?>

                        <?php foreach ($agencies as $agency):
                            if (empty($grouped[$agency])) continue; ?>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-building mr-1"></i> <?= $agency ?>
                                    <span class="badge badge-primary ml-2"><?= count($grouped[$agency]) ?></span>
                                </h3>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Branch Name</th>
                                            <th>City</th>
                                            <th>Address</th>
                                            <th>Contact</th>
                                            <th>Slots</th>
                                            <th>Active Appts</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($grouped[$agency] as $i => $b): ?>
                                        <tr <?= (isset($_GET['edit']) && (int)$_GET['edit'] === $b['id']) ? 'class="table-warning"' : '' ?>>
                                            <td><?= $i + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($b['name']) ?></strong></td>
                                            <td><span class="badge badge-secondary"><?= htmlspecialchars($b['city']) ?></span></td>
                                            <td><small><?= htmlspecialchars($b['address']) ?></small></td>
                                            <td><small><?= $b['contact'] ? htmlspecialchars($b['contact']) : '<span class="text-muted">—</span>' ?></small></td>
                                            <td><span class="badge badge-info"><?= $b['slot_count'] ?></span></td>
                                            <td>
                                                <?php if ($b['appt_count'] > 0): ?>
                                                    <span class="badge badge-warning"><?= $b['appt_count'] ?> active</span>
                                                <?php else: ?>
                                                    <span class="text-muted small">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="white-space:nowrap;">
                                                <a href="branches.php?edit=<?= $b['id'] ?>"
                                                   class="btn btn-warning btn-sm">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                <?php if ($b['appt_count'] == 0): ?>
                                                <a href="branches.php?delete=<?= $b['id'] ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Delete this branch? All its slots will also be deleted.')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <?php else: ?>
                                                <button class="btn btn-danger btn-sm" disabled title="Has active appointments">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endforeach; ?>

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