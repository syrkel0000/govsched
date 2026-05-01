<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireApplicant();

$error = '';
$success = '';

// Fetch current user
$user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$_SESSION['user_id']]);
$user = $user->fetch();

// Update profile
if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);

    if (empty($full_name) || empty($email)) {
        $error = 'Full name and email are required.';
    } else {
        // Check duplicate email excluding self
        $exists = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $exists->execute([$email, $_SESSION['user_id']]);
        if ($exists->fetch()) {
            $error = 'That email is already used by another account.';
        } else {
            $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?")
                ->execute([$full_name, $email, $_SESSION['user_id']]);
            // Update session name
            $_SESSION['full_name'] = $full_name;
            header('Location: profile.php?msg=updated');
            exit();
        }
    }
}

// Change password
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($current, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $hashed = password_hash($new, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
            ->execute([$hashed, $_SESSION['user_id']]);
        header('Location: profile.php?msg=password_updated');
        exit();
    }
}

// Appointment stats
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

// Recent appointments
$recent = $pdo->prepare("
    SELECT a.*, d.name as document_name, t.slot_time
    FROM appointments a
    JOIN documents d ON a.document_id = d.id
    JOIN time_slots t ON a.slot_id = t.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
    LIMIT 5
");
$recent->execute([$_SESSION['user_id']]);
$recent = $recent->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | My Profile</title>
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
                        <a href="my_appointments.php" class="nav-link">
                            <i class="nav-icon fas fa-list"></i><p>My Appointments</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link active">
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
                <h1 class="m-0">My Profile</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <!-- Flash Messages -->
                <?php if (isset($_GET['msg'])): ?>
                <?php
                $msgs = [
                    'updated'          => ['success', 'Profile updated successfully.'],
                    'password_updated' => ['success', 'Password changed successfully.'],
                ];
                if (isset($msgs[$_GET['msg']])):
                    [$type, $text] = $msgs[$_GET['msg']];
                ?>
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

                <!-- Stats Row -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= $stats['total'] ?></h3>
                                <p>Total Appointments</p>
                            </div>
                            <div class="icon"><i class="fas fa-calendar"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $stats['pending'] ?></h3>
                                <p>Pending</p>
                            </div>
                            <div class="icon"><i class="fas fa-clock"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $stats['confirmed'] ?></h3>
                                <p>Confirmed</p>
                            </div>
                            <div class="icon"><i class="fas fa-check"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= $stats['cancelled'] ?></h3>
                                <p>Cancelled</p>
                            </div>
                            <div class="icon"><i class="fas fa-times"></i></div>
                        </div>
                    </div>
                </div>

                <div class="row">

                    <!-- Update Profile -->
                    <div class="col-md-6">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user-edit mr-1"></i> Edit Profile
                                </h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="profile.php">
                                    <div class="form-group">
                                        <label>Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="full_name" class="form-control"
                                               value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control"
                                               value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Member Since</label>
                                        <input type="text" class="form-control"
                                               value="<?= date('F d, Y', strtotime($user['created_at'])) ?>"
                                               readonly style="background:#f4f6f9;">
                                    </div>
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Change Password -->
                    <div class="col-md-6">
                        <div class="card card-warning card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-key mr-1"></i> Change Password
                                </h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="profile.php">
                                    <div class="form-group">
                                        <label>Current Password</label>
                                        <input type="password" name="current_password"
                                               class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>New Password <small class="text-muted">(min 8 characters)</small></label>
                                        <input type="password" name="new_password"
                                               class="form-control" minlength="8" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Confirm New Password</label>
                                        <input type="password" name="confirm_password"
                                               class="form-control" minlength="8" required>
                                    </div>
                                    <button type="submit" name="change_password" class="btn btn-warning">
                                        <i class="fas fa-lock"></i> Update Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Recent Appointments -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history mr-1"></i> Recent Appointments
                        </h3>
                        <div class="card-tools">
                            <a href="my_appointments.php" class="btn btn-sm btn-default">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Reference No</th>
                                    <th>Document</th>
                                    <th>Office</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent as $row): ?>
                                <?php
                                $badge = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger'];
                                $s = $row['status'];
                                ?>
                                <tr>
                                    <td><code><?= $row['reference_no'] ?></code></td>
                                    <td><?= htmlspecialchars($row['document_name']) ?></td>
                                    <td><?= htmlspecialchars($row['office']) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                                    <td><?= $row['slot_time'] ?></td>
                                    <td>
                                        <span class="badge badge-<?= $badge[$s] ?>">
                                            <?= ucfirst($s) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="receipt.php?id=<?= $row['id'] ?>"
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recent)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">
                                        No appointments yet. <a href="book.php">Book one now</a>.
                                    </td>
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