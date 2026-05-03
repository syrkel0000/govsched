<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireApplicant();

$user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$_SESSION['user_id']]);
$user = $user->fetch();

$docs = $pdo->query("SELECT id, name, agency FROM documents ORDER BY agency, name")->fetchAll();
$grouped = [];
foreach ($docs as $doc) {
    $grouped[$doc['agency']][] = $doc;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $document_id  = (int)$_POST['document_id'];
    $branch_id    = (int)$_POST['branch_id'];
    $slot_id      = (int)$_POST['slot_id'];
    $appt_date    = $_POST['appointment_date'];
    $request_type = $_POST['request_type'];
    $full_name    = trim($_POST['full_name']);
    $age          = (int)$_POST['age'];
    $birthdate    = $_POST['birthdate'];
    $address      = trim($_POST['address']);
    $email        = trim($_POST['email']);
    $contact      = trim($_POST['contact']);
    $civil_status = $_POST['civil_status'];
    $gender       = $_POST['gender'];

    $for_name = $for_relationship = $guardian_name = $guardian_contact = null;
    $is_minor = 0;

    if ($request_type === 'other') {
        $for_name         = trim($_POST['for_name']);
        $for_relationship = trim($_POST['for_relationship']);
        $for_age          = (int)($_POST['for_age'] ?? 0);
        if ($for_age < 18) {
            $is_minor         = 1;
            $guardian_name    = trim($_POST['guardian_name']);
            $guardian_contact = trim($_POST['guardian_contact']);
        }
    }

    // Block minor booking for myself
    if ($request_type === 'self' && $age < 18) {
        $error = 'Applicants below 18 cannot book for themselves. Please select "For Another Person" and provide guardian details.';
    }

    if (!$error && strtotime($appt_date) <= strtotime('today')) {
        $error = 'Please select a future date.';
    }

    if (!$error) {
        $day = date('N', strtotime($appt_date));
        if ($day >= 6) $error = 'Offices are closed on weekends. Please select a weekday.';
    }

    if (!$error) {
        $cap = $pdo->prepare("
            SELECT ts.max_capacity, COUNT(a.id) AS booked
            FROM time_slots ts
            LEFT JOIN appointments a 
                ON a.slot_id = ts.id 
                AND a.appointment_date = ?
                AND a.branch_id = ?
                AND a.status != 'cancelled'
            WHERE ts.id = ? AND ts.branch_id = ?
            GROUP BY ts.max_capacity
        ");
        $cap->execute([$appt_date, $branch_id, $slot_id, $branch_id]);
        $capRow = $cap->fetch();
        if (!$capRow || ($capRow['max_capacity'] - $capRow['booked']) <= 0) {
            $error = 'This time slot is already full. Please choose another.';
        }
    }

    if (!$error) {
        $existing = $pdo->prepare("SELECT id FROM appointments WHERE user_id = ? AND status IN ('pending','confirmed')");
        $existing->execute([$_SESSION['user_id']]);
        if ($existing->fetch()) {
            $error = 'You already have an active appointment. Please cancel it before booking a new one.';
        }
    }

    if (!$error) {
        $branchRow = $pdo->prepare("SELECT name FROM branches WHERE id = ?");
        $branchRow->execute([$branch_id]);
        $branchName = $branchRow->fetchColumn();

        $reference_no = 'GS-' . strtoupper(substr(md5(uniqid()), 0, 8));

        $pdo->prepare("
            INSERT INTO appointments
            (user_id, document_id, slot_id, branch_id, office, appointment_date, request_type,
             full_name, age, birthdate, address, email, contact, civil_status, gender,
             for_name, for_relationship, is_minor, guardian_name, guardian_contact, reference_no)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $_SESSION['user_id'], $document_id, $slot_id, $branch_id, $branchName,
            $appt_date, $request_type, $full_name, $age, $birthdate, $address,
            $email, $contact, $civil_status, $gender, $for_name, $for_relationship,
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
                                    <label>Document Needed <span class="text-danger">*</span></label>
                                    <select name="document_id" id="document_id" class="form-control" required>
                                        <option value="">-- Select Document --</option>
                                        <?php foreach ($grouped as $agency => $items): ?>
                                            <optgroup label="<?= htmlspecialchars($agency) ?>">
                                                <?php foreach ($items as $d): ?>
                                                    <option value="<?= $d['id'] ?>">
                                                        <?= htmlspecialchars($d['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Branch / Office <span class="text-danger">*</span></label>
                                    <select name="branch_id" id="branch_id" class="form-control" required>
                                        <option value="">-- Select document first --</option>
                                    </select>
                                    <small class="text-muted" id="branch_info"></small>
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
                                        <option value="">-- Select branch and date first --</option>
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

                                <!-- Minor warning -->
                                <div class="alert alert-warning d-none" id="minor_warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Notice:</strong> Applicants below 18 <strong>cannot</strong> book for themselves.
                                    Please select <strong>"For Another Person"</strong> and provide guardian details.
                                </div>

                                <div class="form-group">
                                    <label>Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" id="full_name"
                                           class="form-control field-locked"
                                           value="<?= htmlspecialchars($user['full_name']) ?>" readonly required>
                                    <small id="fullname_note" class="text-info">
                                        <i class="fas fa-info-circle"></i> Auto-filled from your account.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label>Birthdate <span class="text-danger">*</span></label>
                                    <input type="date" name="birthdate" id="birthdate"
                                           class="form-control" max="<?= date('Y-m-d') ?>" required>
                                    <small class="text-muted">Age will be calculated automatically.</small>
                                </div>

                                <div class="form-group">
                                    <label>Age</label>
                                    <input type="number" name="age" id="age"
                                           class="form-control field-locked" placeholder="Auto-calculated" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" rows="2" required></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email"
                                           class="form-control field-locked"
                                           value="<?= htmlspecialchars($user['email']) ?>" readonly required>
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
                                    <label>Age of Person</label>
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
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
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
$(function () {

    // 1. Document → load branches
    $('#document_id').on('change', function () {
        var docId = $(this).val();
        $('#branch_id').html('<option value="">-- Select document first --</option>');
        $('#branch_info').text('');
        $('#slot_id').html('<option value="">-- Select branch and date first --</option>');

        if (!docId) return;

        $.getJSON('get_branches.php', { document_id: docId }, function (branches) {
            if (!branches.length) {
                $('#branch_id').html('<option value="">No branches available</option>');
                return;
            }

            $('#branch_id').html('<option value="">-- Select Branch --</option>');
            $.each(branches, function (i, b) {
                $('#branch_id').append(
                    $('<option>').val(b.id)
                        .text(b.name + ' — ' + b.city)
                        .data('address', b.address)
                        .data('contact', b.contact || '')
                );
            });

            // Auto-select if only 1
            if (branches.length === 1) {
                $('#branch_id option:eq(1)').prop('selected', true).trigger('change');
            }
        });
    });

    // 2. Branch → show address info + reload slots if date already set
    $('#branch_id').on('change', function () {
        var selected = $(this).find('option:selected');
        var addr    = selected.data('address') || '';
        var contact = selected.data('contact') || '';

        if (addr) {
            $('#branch_info').html('<i class="fas fa-map-marker-alt mr-1 text-danger"></i>' + addr +
                (contact ? ' &nbsp;|&nbsp; <i class="fas fa-phone mr-1 text-success"></i>' + contact : ''));
        } else {
            $('#branch_info').text('');
        }

        // Reload slots if date already picked
        var date = $('#appointment_date').val();
        if (date && $(this).val()) loadSlots();
    });

    // 3. Date → validate weekend + load slots
    $('#appointment_date').on('change', function () {
        var d = new Date($(this).val() + 'T00:00:00');
        if (d.getDay() === 0 || d.getDay() === 6) {
            alert('Offices are closed on weekends. Please select a weekday.');
            $(this).val('');
            $('#slot_id').html('<option value="">-- Select branch and date first --</option>');
            return;
        }
        loadSlots();
    });

    function loadSlots() {
        var branchId = $('#branch_id').val();
        var date     = $('#appointment_date').val();

        if (!branchId || !date) {
            $('#slot_id').html('<option value="">-- Select branch and date first --</option>');
            return;
        }

        $('#slot_id').html('<option value="">Loading...</option>');

        $.getJSON('get_slots.php', { branch_id: branchId, date: date }, function (data) {
            if (!data.length || data.weekend) {
                $('#slot_id').html('<option value="">No slots available</option>');
                return;
            }
            $('#slot_id').html('<option value="">-- Select Time --</option>');
            $.each(data, function (i, s) {
                if (s.full) {
                    $('#slot_id').append('<option value="' + s.id + '" disabled style="color:#aaa;">' + s.slot_time + ' (Full)</option>');
                } else {
                    $('#slot_id').append('<option value="' + s.id + '">' + s.slot_time + ' — ' + s.remaining + ' slots left</option>');
                }
            });
        });
    }

    // 4. Request type toggle
    $('#request_type').on('change', function () {
        var age = parseInt($('#age').val()) || 0;
        handleRequestType($(this).val(), age);
    });

    // 5. Birthdate → auto-calc age + minor check
    $('#birthdate').on('change', function () {
        var bdate = new Date($(this).val());
        var today = new Date();
        var age   = today.getFullYear() - bdate.getFullYear();
        var m     = today.getMonth() - bdate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < bdate.getDate())) age--;
        age = age > 0 ? age : 0;
        $('#age').val(age);
        handleRequestType($('#request_type').val(), age);
    });

    function handleRequestType(type, age) {
        var isMinor = age > 0 && age < 18;

        if (type === 'self') {
            $('#other_section, #guardian_section').hide();
            $('#full_name').val('<?= addslashes($user['full_name']) ?>').addClass('field-locked').prop('readonly', true);
            $('#email').val('<?= addslashes($user['email']) ?>').addClass('field-locked').prop('readonly', true);
            $('#fullname_note, #email_note').show();

            if (isMinor) {
                $('#minor_warning').removeClass('d-none');
                $('#submitBtn').prop('disabled', true);
            } else {
                $('#minor_warning').addClass('d-none');
                $('#submitBtn').prop('disabled', false);
            }
        } else {
            $('#minor_warning').addClass('d-none');
            $('#submitBtn').prop('disabled', false);
            $('#other_section').show();
            $('#full_name').val('').removeClass('field-locked').prop('readonly', false);
            $('#email').val('').removeClass('field-locked').prop('readonly', false);
            $('#fullname_note, #email_note').hide();
        }
    }

    // 6. For another person minor check
    $('#for_age').on('input', function () {
        var age = parseInt($(this).val());
        $('#guardian_section').toggle(!isNaN(age) && age < 18);
    });

    // Init on load
    $('#full_name, #email').addClass('field-locked').prop('readonly', true);
});
</script>
</body>
</html>