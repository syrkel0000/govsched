<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

if (isLoggedIn()) {
    header('Location: /govsched/pages/user/dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'applicant')")
                ->execute([$full_name, $email, $hashed]);
            $success = 'Account created! You can now sign in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | Register</title>
    <link rel="stylesheet" href="../../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../../assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition register-page">
<div class="register-box">
    <div class="register-logo">
        <b>Gov</b>Sched
    </div>
    <div class="card">
        <div class="card-body register-card-body">
            <p class="login-box-msg">Create a new account</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= $success ?>
                    <a href="/govsched/index.php" class="font-weight-bold">Sign in now</a>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="input-group mb-3">
                    <input type="text" name="full_name"
                           class="form-control" placeholder="Full Name"
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-user"></span></div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="email" name="email"
                           class="form-control" placeholder="Email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" id="password"
                           class="form-control" placeholder="Password (min 8 characters)" required>
                    <div class="input-group-append">
                        <div class="input-group-text" id="toggle_password" style="cursor:pointer;">
                            <span class="fas fa-eye" id="eye_password"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="confirm_password" id="confirm_password"
                           class="form-control" placeholder="Confirm Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text" id="toggle_confirm" style="cursor:pointer;">
                            <span class="fas fa-eye" id="eye_confirm"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-3">
                <a href="/govsched/index.php">Already have an account? Sign in</a>
            </p>
        </div>
    </div>
</div>
<script src="../../assets/plugins/jquery/jquery.min.js"></script>
<script src="../../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/dist/js/adminlte.min.js"></script>
<script>
$('#toggle_password').on('click', function() {
    var $input = $('#password');
    var $icon  = $('#eye_password');
    if ($input.attr('type') === 'password') {
        $input.attr('type', 'text');
        $icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        $input.attr('type', 'password');
        $icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
});

$('#toggle_confirm').on('click', function() {
    var $input = $('#confirm_password');
    var $icon  = $('#eye_confirm');
    if ($input.attr('type') === 'password') {
        $input.attr('type', 'text');
        $icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        $input.attr('type', 'password');
        $icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
});
</script>
</body>
</html>