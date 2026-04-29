<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireApplicant();

$documents = $pdo->query("SELECT * FROM documents")->fetchAll();
$time_slots = $pdo->query("SELECT * FROM time_slots")->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $document_id = $_POST['document_id'];
    $appointment_date = $_POST['appointment_date'];
    $slot_id = $_POST['slot_id'];
    $request_type = $_POST['request_type'];
    $full_name = trim($_POST['full_name']);
    $age = intval($_POST['age']);
    $birthdate = $_POST['birthdate'];
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $civil_status = $_POST['civil_status'];
    $gender = $_POST['gender'];

    $for_name = null;
    $for_relationship = null;
    $is_minor = 0;
    $guardian_name = null;
    $guardian_contact = null;

    if ($request_type === 'other') {
        $for_name = trim($_POST['for_name']);
        $for_relationship = trim($_POST['for_relationship']);
        $for_age = intval($_POST['for_age']);
        if ($for_age < 18) {
            $is_minor = 1;
            $guardian_name = trim($_POST['guardian_name']);
            $guardian_contact = trim($_POST['guardian_contact']);
        }
    }

    // Check slot availability
    $check = $pdo->prepare("SELECT id FROM appointments WHERE slot_id = ? AND appointment_date = ?");
    $check->execute([$slot_id, $appointment_date]);
    if ($check->fetch()) {
        $error = 'That time slot is already taken. Please choose another.';
    } else {
        $reference_no = 'GS-' . strtoupper(uniqid());
        $stmt = $pdo->prepare("
            INSERT INTO appointments 
            (user_id, document_id, slot_id, appointment_date, request_type, full_name, age, birthdate, address, email, contact, civil_status, gender, for_name, for_relationship, is_minor, guardian_name, guardian_contact, reference_no)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'], $document_id, $slot_id, $appointment_date,
            $request_type, $full_name, $age, $birthdate, $address, $email,
            $contact, $civil_status, $gender, $for_name, $for_relationship,
            $is_minor, $guardian_name, $guardian_contact, $reference_no
        ]);

        $new_id = $pdo->lastInsertId();
        header("Location: receipt.php?id=$new_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | Book Appointment</title>
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
        <a href="/govsched/pages/user/dashboard.php" class="brand-link">
            <span class="brand-text font-weight-light"><b>Gov</b>Sched</span>
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
                        <a href="book.php" class="nav-link active">
                            <i class="nav-icon fas fa-calendar-plus"></i>
                            <p>Book Appointment</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="my_appointments.php" class="nav-link">
                            <i class="nav-icon fas fa-list"></i>
                            <p>My Appointments</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Book Appointment</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form action="book.php" method="POST">
                <div class="row">

                    <!-- Document & Schedule -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Document & Schedule</h3></div>
                            <div class="card-body">

                                <div class="form-group">
                                    <label>Document Type</label>
                                    <select name="document_id" class="form-control" required>
                                        <option value="">-- Select Document --</option>
                                        <?php foreach ($documents as $doc): ?>
                                        <option value="<?= $doc['id'] ?>"><?= $doc['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Appointment Date</label>
                                    <input type="date" name="appointment_date" class="form-control" 
                                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label>Time Slot</label>
                                    <select name="slot_id" class="form-control" required>
                                        <option value="">-- Select Time --</option>
                                        <?php foreach ($time_slots as $slot): ?>
                                        <option value="<?= $slot['id'] ?>"><?= $slot['slot_time'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Request Type</label>
                                    <select name="request_type" id="request_type" class="form-control" required>
                                        <option value="self">For Myself</option>
                                        <option value="other">For Another Person</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Personal Info -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Your Information</h3></div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="full_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Age</label>
                                    <input type="number" name="age" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Birthdate</label>
                                    <input type="date" name="birthdate" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea name="address" class="form-control" rows="2" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Contact Number</label>
                                    <input type="text" name="contact" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Civil Status</label>
                                    <select name="civil_status" class="form-control" required>
                                        <option value="">-- Select --</option>
                                        <option>Single</option>
                                        <option>Married</option>
                                        <option>Widowed</option>
                                        <option>Separated</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Gender</label>
                                    <select name="gender" class="form-control" required>
                                        <option value="">-- Select --</option>
                                        <option>Male</option>
                                        <option>Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- For Another Person -->
                    <div class="col-12" id="other_section" style="display:none;">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Person Being Requested For</h3></div>
                            <div class="card-body row">
                                <div class="col-md-4 form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="for_name" class="form-control">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Relationship</label>
                                    <input type="text" name="for_relationship" class="form-control">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Age</label>
                                    <input type="number" name="for_age" id="for_age" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guardian Info (if minor) -->
                    <div class="col-12" id="guardian_section" style="display:none;">
                        <div class="card">
                            <div class="card-header bg-warning"><h3 class="card-title">Guardian Information (Minor)</h3></div>
                            <div class="card-body row">
                                <div class="col-md-6 form-group">
                                    <label>Guardian Name</label>
                                    <input type="text" name="guardian_name" class="form-control">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Guardian Contact</label>
                                    <input type="text" name="guardian_contact" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-check"></i> Submit Appointment
                        </button>
                    </div>

                </div>
                </form>

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
<script>
    $('#request_type').change(function() {
        if ($(this).val() === 'other') {
            $('#other_section').show();
        } else {
            $('#other_section').hide();
            $('#guardian_section').hide();
        }
    });

    $('#for_age').on('input', function() {
        if (parseInt($(this).val()) < 18) {
            $('#guardian_section').show();
        } else {
            $('#guardian_section').hide();
        }
    });
</script>
</body>
</html>