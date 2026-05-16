<?php
session_start();
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $success = 'Login successful! Redirecting...';
                header("refresh:2;url=dashboard.php");
            } else {
                $error = 'Invalid email or password.';
            }
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MySystem</title>
    <style>
        :root {
            --bg: #f5f7ff;
            --surface: #ffffff;
            --primary: #3b82f6;
            --primary-dark: #1d4ed8;
            --text: #111827;
            --muted: #6b7280;
            --radius: 24px;
            --shadow: 0 24px 64px rgba(15, 23, 42, 0.08);
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', Arial, sans-serif;
            background: linear-gradient(180deg, #eef2ff 0%, #f8fafc 100%);
            color: var(--text);
        }
        .page {
            width: min(1080px, calc(100% - 32px));
            margin: 0 auto;
            padding: 32px 0 48px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        .logo {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-dark);
        }
        .header a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 40px;
            box-shadow: var(--shadow);
            max-width: 520px;
            margin: 0 auto;
        }
        .card h1 {
            margin-top: 0;
            margin-bottom: 18px;
            font-size: clamp(2rem, 2.5vw, 2.6rem);
        }
        .card p {
            margin-top: 0;
            margin-bottom: 30px;
            color: var(--muted);
            line-height: 1.75;
        }
        .form-group {
            display: grid;
            gap: 10px;
            margin-bottom: 20px;
        }
        .form-group label {
            font-size: 0.95rem;
            color: var(--muted);
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #d1d5db;
            border-radius: 14px;
            font-size: 1rem;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }
        .button {
            width: 100%;
            border: none;
            border-radius: 14px;
            background: var(--primary);
            color: white;
            padding: 16px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 700;
            transition: background .2s ease, transform .2s ease;
        }
        .button:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        .helper-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .helper-row a {
            color: var(--primary);
            text-decoration: none;
        }
        .footer-note {
            margin-top: 18px;
            color: var(--muted);
            font-size: 0.95rem;
            text-align: center;
        }
        .footer-note a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="page">
        <header class="header">
            <div class="logo">MySystem</div>
            <a href="register.php">Create account</a>
        </header>
        <main class="card">
            <h1>Welcome back</h1>
            <p>Access your dashboard by signing in with your email and password.</p>
            <form method="POST" action="dashboard.php">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input id="email" type="email" name="email" placeholder="you@example.com">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="Enter your password">
                </div>
                <div class="helper-row">
                    <label style="display:flex; align-items:center; gap:10px; font-size:0.95rem; color:var(--muted);">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#">Forgot password?</a>
                </div>
                <button class="button" type="submit">Sign in</button>
                <p class="footer-note">Not a member? <a href="register.php">Create an account</a>.</p>
            </form>
        </main>
    </div>
</body>
</html>