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
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];

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
    <title>GovSched | Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:   #0d1f3c;
            --navy2:  #16305a;
            --gold:   #c9a84c;
            --gold2:  #e2c074;
            --white:  #ffffff;
            --light:  #f4f6fb;
            --muted:  #8a96a8;
            --danger: #e05454;
            --input-border: #d0d8e8;
        }

        body {
            min-height: 100vh;
            display: flex;
            font-family: 'DM Sans', sans-serif;
            background: var(--light);
        }

        /* ── Left Panel ── */
        .panel-left {
            width: 44%;
            background: var(--navy);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 50px;
            position: relative;
            overflow: hidden;
        }

        /* geometric background pattern */
        .panel-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(45deg, rgba(201,168,76,.06) 0px, rgba(201,168,76,.06) 1px, transparent 1px, transparent 60px),
                repeating-linear-gradient(-45deg, rgba(201,168,76,.06) 0px, rgba(201,168,76,.06) 1px, transparent 1px, transparent 60px);
            pointer-events: none;
        }

        .panel-left::after {
            content: '';
            position: absolute;
            bottom: -80px; right: -80px;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201,168,76,.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .brand {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .brand-seal {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), var(--gold2));
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 8px 32px rgba(201,168,76,.35);
        }

        .brand-seal i { font-size: 32px; color: var(--navy); }

        .brand h1 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: var(--white);
            letter-spacing: -1px;
            line-height: 1;
        }

        .brand h1 span { color: var(--gold); }

        .brand p {
            margin-top: 12px;
            color: rgba(255,255,255,.55);
            font-size: 13px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .panel-features {
            position: relative; z-index: 1;
            margin-top: 56px;
            display: flex; flex-direction: column; gap: 20px;
            width: 100%;
        }

        .feature-item {
            display: flex; align-items: center; gap: 14px;
        }

        .feature-icon {
            width: 38px; height: 38px; flex-shrink: 0;
            border-radius: 10px;
            background: rgba(201,168,76,.12);
            border: 1px solid rgba(201,168,76,.25);
            display: flex; align-items: center; justify-content: center;
        }

        .feature-icon i { font-size: 15px; color: var(--gold); }

        .feature-text p { font-size: 13.5px; color: rgba(255,255,255,.85); font-weight: 500; }
        .feature-text span { font-size: 11.5px; color: rgba(255,255,255,.4); }

        /* ── Right Panel ── */
        .panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
        }

        .form-card {
            width: 100%;
            max-width: 420px;
        }

        .form-card .heading {
            margin-bottom: 32px;
        }

        .form-card .heading h2 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: var(--navy);
            font-weight: 700;
        }

        .form-card .heading p {
            font-size: 13.5px;
            color: var(--muted);
            margin-top: 6px;
        }

        .alert-danger {
            background: #fdf0f0;
            border: 1px solid #f5c6c6;
            color: var(--danger);
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }

        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-size: 12.5px;
            font-weight: 500;
            color: var(--navy);
            margin-bottom: 7px;
            letter-spacing: .3px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i.icon-left {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            font-size: 14px; color: var(--muted); pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            padding: 12px 44px 12px 40px;
            border: 1.5px solid var(--input-border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: var(--navy);
            background: var(--white);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .input-wrap input:focus {
            border-color: var(--navy2);
            box-shadow: 0 0 0 3px rgba(13,31,60,.08);
        }

        .input-wrap input::placeholder { color: #b0bac9; }

        .toggle-pw {
            position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: var(--muted); font-size: 14px;
            transition: color .2s;
        }
        .toggle-pw:hover { color: var(--navy); }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--navy), var(--navy2));
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 14.5px;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            letter-spacing: .3px;
            margin-top: 6px;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 16px rgba(13,31,60,.25);
        }

        .btn-submit:hover {
            opacity: .92;
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(13,31,60,.3);
        }

        .btn-submit:active { transform: translateY(0); }

        .btn-submit i { margin-right: 7px; }

        .form-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: var(--muted);
        }

        .form-footer a {
            color: var(--navy);
            font-weight: 500;
            text-decoration: none;
            border-bottom: 1px solid var(--gold);
            padding-bottom: 1px;
            transition: color .2s;
        }

        .form-footer a:hover { color: var(--gold); }

        .divider {
            border: none;
            border-top: 1px solid #e8ecf3;
            margin: 24px 0;
        }

        @media (max-width: 768px) {
            .panel-left { display: none; }
        }
    </style>
</head>
<body>

<!-- Left Panel -->
<div class="panel-left">
    <div class="brand">
        <div class="brand-seal">
            <i class="fas fa-landmark"></i>
        </div>
        <h1><span>Gov</span>Sched</h1>
        <p>Appointment Scheduling System</p>
    </div>
    <div class="panel-features">
        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="feature-text">
                <p>Online Appointment Booking</p>
                <span>Schedule at your convenience, anytime</span>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-file-alt"></i></div>
            <div class="feature-text">
                <p>Document Request Management</p>
                <span>Track and manage all your requests</span>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
            <div class="feature-text">
                <p>Secure & Reliable</p>
                <span>Your data is protected at all times</span>
            </div>
        </div>
    </div>
</div>

<!-- Right Panel -->
<div class="panel-right">
    <div class="form-card">
        <div class="heading">
            <h2>Welcome back</h2>
            <p>Sign in to your GovSched account to continue</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope icon-left"></i>
                    <input type="email" name="email" placeholder="you@example.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock icon-left"></i>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                    <i class="fas fa-eye toggle-pw" id="toggle_pw"></i>
                </div>
            </div>
            <button type="submit" class="btn-submit">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <hr class="divider">

        <p class="form-footer">
            Don't have an account?
            <a href="pages/user/register.php">Create one here</a>
        </p>
    </div>
</div>

<script>
document.getElementById('toggle_pw').addEventListener('click', function () {
    const input = document.getElementById('password');
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    this.classList.toggle('fa-eye', !isHidden);
    this.classList.toggle('fa-eye-slash', isHidden);
});
</script>
</body>
</html>