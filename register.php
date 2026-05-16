<?php

include_once('config.php');

if(isset($_POST['submit'])) {
    $name = $_POST['name'] ?? '';
    $user = $_POST['user'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);

    if(empty($name) || empty($user) || empty($username) || empty($email) || empty($_POST['password'])) {
        $registerError = 'Fill all fields!';
    } else {
        $sql = "INSERT INTO pp (name, user, username, email, password) VALUES (:name, :user, :username, :email, :password)";
        $insertSql = $conn->prepare($sql);
        $insertSql->bindParam(':name', $name);
        $insertSql->bindParam(':user', $user);
        $insertSql->bindParam(':username', $username);
        $insertSql->bindParam(':email', $email);
        $insertSql->bindParam(':password', $password);
        $insertSql->execute();
        $registerSuccess = 'User Registered Successfully!';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | MySystem</title>
    <style>
        :root {
            --bg: #eff6ff;
            --surface: #ffffff;
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --text: #111827;
            --muted: #4b5563;
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
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
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
            font-size: 1.2rem;
            font-weight: 700;
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
            max-width: 560px;
            margin: 0 auto;
        }
        .card h1 {
            margin-top: 0;
            margin-bottom: 12px;
            font-size: clamp(2rem, 2.5vw, 2.6rem);
        }
        .card p {
            margin: 0 0 28px;
            color: var(--muted);
            line-height: 1.75;
        }
        .alert {
            padding: 14px 18px;
            border-radius: 16px;
            margin-bottom: 22px;
            font-size: 0.98rem;
        }
        .alert.error {
            background: #fee2e2;
            color: #991b1b;
        }
        .alert.success {
            background: #dbeafe;
            color: #1e40af;
        }
        .form-group {
            display: grid;
            gap: 10px;
            margin-bottom: 18px;
        }
        .form-group label {
            color: var(--muted);
            font-size: 0.95rem;
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
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
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
        .footer-note {
            margin-top: 20px;
            font-size: 0.95rem;
            color: var(--muted);
            text-align: center;
        }
        .footer-note a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="page">
        <header class="header">
            <div class="logo">MySystem</div>
            <a href="login.php">Already have an account?</a>
        </header>
        <article class="card">
            <h1>Create your account</h1>
            <p>Fill in the fields below to register. This form now uses a modern layout with clear spacing and better typography.</p>
            <?php if (!empty($registerError)): ?>
                <div class="alert error"><?= htmlspecialchars($registerError) ?></div>
            <?php endif; ?>
            <?php if (!empty($registerSuccess)): ?>
                <div class="alert success"><?= htmlspecialchars($registerSuccess) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input id="name" type="text" name="name" placeholder="Your full name">
                </div>
                <div class="form-group">
                    <label for="user">User</label>
                    <input id="user" type="text" name="user" placeholder="User type or label">
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" type="text" name="username" placeholder="Choose a username">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" placeholder="you@example.com">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="Create a password">
                </div>
                <button class="button" type="submit" name="submit">Register now</button>
            </form>
            <p class="footer-note">Already registered? <a href="login.php">Sign in here</a>.</p>
        </article>
    </div>
</body>
</html>