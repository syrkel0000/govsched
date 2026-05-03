<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

if (isLoggedIn()) {
    header('Location: /govsched/pages/user/dashboard.php');
    exit();
}

$error   = '';
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
            $success = 'Account created successfully!';
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/plugins/fontawesome-free/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:  #0d1f3c;
            --navy2: #16305a;
            --gold:  #c9a84c;
            --gold2: #e2c074;
            --white: #ffffff;
            --light: #f4f6fb;
            --muted: #8a96a8;
            --danger: #e05454;
            --success: #2e7d5e;
            --input-border: #d0d8e8;
        }

        body {
            min-height: 100vh;
            display: flex;
            font-family: 'DM Sans', sans-serif;
            background: var(--light);
        }

        .panel-left {
            width: 38%;
            background: var(--navy);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 44px;
            position: relative;
            overflow: hidden;
        }

        .panel-left::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                repeating-linear-gradient(45deg, rgba(201,168,76,.06) 0px, rgba(201,168,76,.06) 1px, transparent 1px, transparent 60px),
                repeating-linear-gradient(-45deg, rgba(201,168,76,.06) 0px, rgba(201,168,76,.06) 1px, transparent 1px, transparent 60px);
        }

        .panel-left::after {
            content: '';
            position: absolute;
            top: -60px; left: -60px;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201,168,76,.12) 0%, transparent 70%);
        }

        .brand { position: relative; z-index: 1; text-align: center; }

        .brand-seal {
            width: 68px; height: 68px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), var(--gold2));
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 22px;
            box-shadow: 0 8px 28px rgba(201,168,76,.35);
        }

        .brand-seal i { font-size: 28px; color: var(--navy); }

        .brand h1 {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            color: var(--white);
            letter-spacing: -1px;
        }

        .brand h1 span { color: var(--gold); }

        .brand p {
            margin-top: 10px;
            color: rgba(255,255,255,.5);
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .steps {
            position: relative; z-index: 1;
            margin-top: 52px;
            width: 100%;
        }

        .steps h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--gold);
            margin-bottom: 18px;
        }

        .step-item {
            display: flex; gap: 14px; align-items: flex-start;
            margin-bottom: 18px;
        }

        .step-num {
            width: 26px; height: 26px; flex-shrink: 0;
            border-radius: 50%;
            border: 1.5px solid rgba(201,168,76,.5);
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 500; color: var(--gold);
        }

        .step-text p { font-size: 13px; color: rgba(255,255,255,.85); font-weight: 500; }
        .step-text span { font-size: 11.5px; color: rgba(255,255,255,.38); }

        /* Right */
        .panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
        }

        .form-card { width: 100%; max-width: 440px; }

        .heading { margin-bottom: 28px; }
        .heading h2 {
            font-family: 'Playfair Display', serif;
            font-size: 26px; color: var(--navy); font-weight: 700;
        }
        .heading p { font-size: 13.5px; color: var(--muted); margin-top: 5px; }

        .alert-danger, .alert-success {
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
        }

        .alert-danger {
            background: #fdf0f0; border: 1px solid #f5c6c6; color: var(--danger);
        }

        .alert-success {
            background: #edf7f2; border: 1px solid #b2dbc9; color: var(--success);
        }

        .alert-success a {
            color: var(--success); font-weight: 600;
            text-decoration: underline;
        }

        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .form-group { margin-bottom: 16px; }

        .form-group label {
            display: block;
            font-size: 12.5px; font-weight: 500;
            color: var(--navy); margin-bottom: 6px; letter-spacing: .3px;
        }

        .input-wrap { position: relative; }

        .input-wrap i.icon-left {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            font-size: 13px; color: var(--muted); pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            padding: 11px 40px 11px 38px;
            border: 1.5px solid var(--input-border);
            border-radius: 10px;
            font-size: 13.5px;
            font-family: 'DM Sans', sans-serif;
            color: var(--navy); background: var(--white);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .input-wrap input:focus {
            border-color: var(--navy2);
            box-shadow: 0 0 0 3px rgba(13,31,60,.08);
        }

        .input-wrap input::placeholder { color: #b0bac9; }

        .toggle-pw {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: var(--muted); font-size: 13px;
            transition: color .2s;
        }
        .toggle-pw:hover { color: var(--navy); }

        .btn-submit {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, var(--navy), var(--navy2));
            color: var(--white); border: none; border-radius: 10px;
            font-size: 14px; font-weight: 500; font-family: 'DM Sans', sans-serif;
            cursor: pointer; letter-spacing: .3px; margin-top: 4px;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 16px rgba(13,31,60,.22);
        }

        .btn-submit:hover {
            opacity: .91; transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(13,31,60,.28);
        }

        .btn-submit:active { transform: translateY(0); }
        .btn-submit i { margin-right: 7px; }

        .divider { border: none; border-top: 1px solid #e8ecf3; margin: 22px 0; }

        .form-footer { text-align: center; font-size: 13px; color: var(--muted); }
        .form-footer a {
            color: var(--navy); font-weight: 500; text-decoration: none;
            border-bottom: 1px solid var(--gold); padding-bottom: 1px;
            transition: color .2s;
        }
        .form-footer a:hover { color: var(--gold); }

        @media (max-width: 768px) {
            .panel-left { display: none; }
            .row-2 { grid-template-columns: 1fr; }
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
    <div class="steps">
        <h3>How it works</h3>
        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-text">
                <p>Create your account</p>
                <span>Fill in your basic information</span>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-text">
                <p>Choose your appointment</p>
                <span>Select office, date, and time slot</span>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-text">
                <p>Get your reference number</p>
                <span>Track your appointment anytime</span>
            </div>
        </div>
    </div>
</div>

<!-- Right Panel -->
<div class="panel-right">
    <div class="form-card">
        <div class="heading">
            <h2>Create an account</h2>
            <p>Register to book your government appointments online</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $success ?>
                &mdash; <a href="/govsched/index.php">Sign in now</a>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <div class="input-wrap">
                    <i class="fas fa-user icon-left"></i>
                    <input type="text" name="full_name" placeholder="Juan Dela Cruz"
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope icon-left"></i>
                    <input type="email" name="email" placeholder="you@example.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>
            <div class="row-2">
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock icon-left"></i>
                        <input type="password" name="password" id="password"
                               placeholder="Min 8 characters" required>
                        <i class="fas fa-eye toggle-pw" id="toggle_pw"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock icon-left"></i>
                        <input type="password" name="confirm_password" id="confirm_password"
                               placeholder="Repeat password" required>
                        <i class="fas fa-eye toggle-pw" id="toggle_confirm"></i>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-submit">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <hr class="divider">

        <p class="form-footer">
            Already have an account?
            <a href="/govsched/index.php">Sign in here</a>
        </p>
    </div>
</div>

<script>
function togglePw(inputId, iconEl) {
    const input = document.getElementById(inputId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    iconEl.classList.toggle('fa-eye', !isHidden);
    iconEl.classList.toggle('fa-eye-slash', isHidden);
}

document.getElementById('toggle_pw').addEventListener('click', function () {
    togglePw('password', this);
});

document.getElementById('toggle_confirm').addEventListener('click', function () {
    togglePw('confirm_password', this);
});
</script>
</body>
</html>