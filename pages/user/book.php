<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireApplicant();

// Fetch logged-in user data
$user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$_SESSION['user_id']]);
$user = $user->fetch();

$documents = $pdo->query("SELECT * FROM documents")->fetchAll();
$offices = ['Cabanatuan City', 'Palayan City'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $document_id      = $_POST['document_id'];
    $appointment_date = $_POST['appointment_date'];
    $slot_id          = $_POST['slot_id'];
    $office           = $_POST['office'];
    $request_type     = $_POST['request_type'];
    $full_name        = trim($_POST['full_name']);
    $age              = intval($_POST['age']);
    $birthdate        = $_POST['birthdate'];
    $address          = trim($_POST['address']);
    $email            = trim($_POST['email']);
    $contact          = trim($_POST['contact']);
    $civil_status     = $_POST['civil_status'];
    $gender           = $_POST['gender'];

    $for_name = $for_relationship = $guardian_name = $guardian_contact = null;
    $is_minor = 0;

    if ($request_type === 'other') {
        $for_name         = trim($_POST['for_name']);
        $for_relationship = trim($_POST['for_relationship']);
        $for_age          = intval($_POST['for_age']);
        if ($for_age < 18) {
            $is_minor         = 1;
            $guardian_name    = trim($_POST['guardian_name']);
            $guardian_contact = trim($_POST['guardian_contact']);
        }
    }

    // Validate future date
    if (strtotime($appointment_date) <= strtotime('today')) {
        $error = 'Please select a future date.';
    }

    // Validate no weekends
    $day_of_week = date('N', strtotime($appointment_date));
    if (!$error && $day_of_week >= 6) {
        $error = 'Offices are closed on weekends. Please select a weekday.';
    }

    // Validate slot belongs to selected office
    if (!$error) {
        $slot_check = $pdo->prepare("SELECT id FROM time_slots WHERE id = ? AND office = ?");
        $slot_check->execute([$slot_id, $office]);
        if (!$slot_check->fetch()) {
            $error = 'Invalid slot selected.';
        }
    }

    // Check slot capacity
    if (!$error) {
        $count_stmt = $pdo->prepare("
            SELECT COUNT(*) FROM appointments
            WHERE slot_id = ? AND appointment_date = ? AND office = ? AND status != 'cancelled'
        ");
        $count_stmt->execute([$slot_id, $appointment_date, $office]);
        if ($count_stmt->fetchColumn() >= 20) {
            $error = 'This time slot is already full. Please choose another.';
        }
    }

    // Check if user already has a pending/confirmed appointment
    if (!$error) {
        $existing = $pdo->prepare("
            SELECT id FROM appointments
            WHERE user_id = ? AND status IN ('pending', 'confirmed')
        ");
        $existing->execute([$_SESSION['user_id']]);
        if ($existing->fetch()) {
            $error = 'You already have an active appointment. Please cancel it before booking a new one.';
        }
    }

    if (!$error) {
        $reference_no = 'GS-' . strtoupper(uniqid());
        $stmt = $pdo->prepare("
            INSERT INTO appointments
            (user_id, document_id, slot_id, office, appointment_date, request_type,
             full_name, age, birthdate, address, email, contact, civil_status, gender,
             for_name, for_relationship, is_minor, guardian_name, guardian_contact, reference_no)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'], $document_id, $slot_id, $office, $appointment_date,
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
    <style>
        .slot-badge { font-size: 0.75rem; }
        .field-locked { background-color: #f4f6f9 !important; cursor: not-allowed; }
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
                        <a href="book.php" class="nav-link active">
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
                <h1 class="m-0">Book Appointment</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="book.php" method="POST">
                <div class="row">

                    <!-- LEFT: Document & Schedule -->
                    <div class="col-md-6">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-calendar-alt mr-1"></i> Document & Schedule</h3>
                            </div>
                            <div class="card-body">

                                <div class="form-group">
                                    <label>Office <span class="text-danger">*</span></label>
                                    <select name="office" id="office" class="form-control" required>
                                        <option value="">-- Select Office --</option>
                                        <?php foreach ($offices as $o): ?>
                                        <option value="<?= $o ?>"><?= $o ?> Office</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Document Type <span class="text-danger">*</span></label>
                                    <select name="document_id" class="form-control" required>
                                        <option value="">-- Select Document --</option>
                                        <?php foreach ($documents as $doc): ?>
                                        <option value="<?= $doc['id'] ?>"><?= $doc['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Appointment Date <span class="text-danger">*</span> <small class="text-muted">(Mon–Fri only)</small></label>
                                    <input type="date" name="appointment_date" id="appointment_date"
                                           class="form-control"
                                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label>Time Slot <span class="text-danger">*</span></label>
                                    <select name="slot_id" id="slot_id" class="form-control" required>
                                        <option value="">-- Select office and date first --</option>
                                    </select>
                                    <small class="text-muted">Shows remaining slots out of 20.</small>
                                </div>

                                <div class="form-group">
                                    <label>Request Type <span class="text-danger">*</span></label>
                                    <select name="request_type" id="request_type" class="form-control" required>
                                        <option value="self">For Myself</option>
                                        <option value="other">For Another Person</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Personal Info -->
                    <div class="col-md-6">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-user mr-1"></i> Your Information</h3>
                            </div>
                            <div class="card-body">

                                <div class="form-group">
                                    <label>Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" id="full_name"
                                           class="form-control"
                                           value="<?= htmlspecialchars($user['full_name']) ?>"
                                           required>
                                    <small id="fullname_note" class="text-info">
                                        <i class="fas fa-info-circle"></i> Auto-filled from your account.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label>Birthdate <span class="text-danger">*</span></label>
                                    <input type="date" name="birthdate" id="birthdate"
                                           class="form-control"
                                           max="<?= date('Y-m-d') ?>" required>
                                    <small class="text-muted">Age will be calculated automatically.</small>
                                </div>

                                <div class="form-group">
                                    <label>Age</label>
                                    <input type="number" name="age" id="age"
                                           class="form-control field-locked"
                                           placeholder="Auto-calculated" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" rows="2" required></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email"
                                           class="form-control"
                                           value="<?= htmlspecialchars($user['email']) ?>"
                                           required>
                                    <small id="email_note" class="text-info">
                                        <i class="fas fa-info-circle"></i> Auto-filled from your account.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label>Contact Number <span class="text-danger">*</span></label>
                                    <input type="text" name="contact" class="form-control"
                                           placeholder="09XXXXXXXXX" maxlength="11" required>
                                </div>

                                <div class="form-group">
                                    <label>Civil Status <span class="text-danger">*</span></label>
                                    <select name="civil_status" class="form-control" required>
                                        <option value="">-- Select --</option>
                                        <option>Single</option>
                                        <option>Married</option>
                                        <option>Widowed</option>
                                        <option>Separated</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Gender <span class="text-danger">*</span></label>
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
                        <div class="card card-warning card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-user-friends mr-1"></i> Person Being Requested For</h3>
                            </div>
                            <div class="card-body row">
                                <div class="col-md-4 form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="for_name" class="form-control">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Relationship</label>
                                    <input type="text" name="for_relationship" class="form-control"
                                           placeholder="e.g. Son, Mother">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Age</label>
                                    <input type="number" name="for_age" id="for_age"
                                           class="form-control" min="0" max="120">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guardian Info -->
                    <div class="col-12" id="guardian_section" style="display:none;">
                        <div class="card card-danger card-outline">
                            <div class="card-header bg-warning">
                                <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> Guardian Information (Minor)</h3>
                            </div>
                            <div class="card-body row">
                                <div class="col-md-6 form-group">
                                    <label>Guardian Name</label>
                                    <input type="text" name="guardian_name" class="form-control">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Guardian Contact</label>
                                    <input type="text" name="guardian_contact" class="form-control"
                                           placeholder="09XXXXXXXXX">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-check"></i> Submit Appointment
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary btn-lg ml-2">
                            <i class="fas fa-times"></i> Cancel
                        </a>
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
// Auto-calculate age from birthdate
$('#birthdate').on('change', function() {
    var birthdate = new Date($(this).val());
    var today = new Date();
    var age = today.getFullYear() - birthdate.getFullYear();
    var m = today.getMonth() - birthdate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthdate.getDate())) age--;
    $('#age').val(age > 0 ? age : '');
});

// Request type toggle — autofill or clear for myself/other
$('#request_type').on('change', function() {
    if ($(this).val() === 'other') {
        $('#other_section').show();
        $('#guardian_section').hide();
        // Clear autofill when booking for others
        $('#full_name').val('').removeClass('field-locked').prop('readonly', false);
        $('#email').val('').removeClass('field-locked').prop('readonly', false);
        $('#fullname_note, #email_note').hide();
    } else {
        $('#other_section').hide();
        $('#guardian_section').hide();
        // Restore autofill for myself
        $('#full_name').val('<?= addslashes($user['full_name']) ?>').addClass('field-locked').prop('readonly', true);
        $('#email').val('<?= addslashes($user['email']) ?>').addClass('field-locked').prop('readonly', true);
        $('#fullname_note, #email_note').show();
    }
});

// Lock autofill fields on load for "myself"
$(document).ready(function() {
    $('#full_name, #email').addClass('field-locked').prop('readonly', true);
});

// Load slots
function loadSlots() {
    var date   = $('#appointment_date').val();
    var office = $('#office').val();
    var $slot  = $('#slot_id');

    if (!date || !office) {
        $slot.html('<option value="">-- Select office and date first --</option>');
        return;
    }

    $slot.html('<option value="">Loading...</option>');

    $.getJSON('get_slots.php', { date: date, office: office }, function(data) {
        if (data.weekend) {
            $slot.html('<option value="">Offices closed on weekends.</option>');
            return;
        }
        $slot.html('<option value="">-- Select Time --</option>');
        $.each(data, function(i, s) {
            if (s.full) {
                $slot.append('<option value="' + s.id + '" disabled style="color:#aaa;">' +
                    s.slot_time + ' (Full)</option>');
            } else {
                $slot.append('<option value="' + s.id + '">' +
                    s.slot_time + ' — ' + s.remaining + ' slots left</option>');
            }
        });
    });
}

// Block weekends on date picker
$('#appointment_date').on('change', function() {
    var d = new Date($(this).val() + 'T00:00:00');
    if (d.getDay() === 0 || d.getDay() === 6) {
        alert('Offices are closed on weekends. Please select a weekday.');
        $(this).val('');
        $('#slot_id').html('<option value="">-- Select office and date first --</option>');
        return;
    }
    loadSlots();
});

$('#office').on('change', loadSlots);

// Minor guardian toggle
$('#for_age').on('input', function() {
    $('#guardian_section').toggle(parseInt($(this).val()) < 18 && $(this).val() !== '');
});
</script>
</body>
</html>