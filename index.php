<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: /govsched/pages/admin/dashboard.php');
    } else {
        header('Location: /govsched/pages/user/dashboard.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: /govsched/pages/admin/dashboard.php');
        } else {
            header('Location: /govsched/pages/user/dashboard.php');
        }
        exit();
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovSched | Login</title>
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <b>Gov</b>Sched
    </div>
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Sign in to your account</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                </div>
               <div class="input-group mb-3">
    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
    <div class="input-group-append">
        <div class="input-group-text" id="toggle_password" style="cursor:pointer;">
            <span class="fas fa-eye" id="eye_icon"></span>
        </div>
    </div>
</div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-3">
                <a href="pages/user/register.php">Register a new account</a>
            </p>
        </div>
    </div>
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>
<script>
$('#toggle_password').on('click', function() {
    var $input = $('#password');
    var $icon  = $('#eye_icon');
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