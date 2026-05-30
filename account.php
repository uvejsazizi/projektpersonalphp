<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user_id'];
$error = '';
$success = '';

try {
    $stmt = $conn->prepare('SELECT id, name, user, username, email FROM pp WHERE id = :id');
    $stmt->bindParam(':id', $uid);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $user_type = trim($_POST['user'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($name) || empty($username) || empty($email)) {
        $error = 'Name, username and email are required.';
    } else {
        try {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = 'UPDATE pp SET name = :name, user = :user, username = :username, email = :email, password = :password WHERE id = :id';
            } else {
                $sql = 'UPDATE pp SET name = :name, user = :user, username = :username, email = :email WHERE id = :id';
            }
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':user', $user_type);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            if ($password !== '') {
                $stmt->bindParam(':password', $hash);
            }
            $stmt->bindParam(':id', $uid);
            $stmt->execute();
            $_SESSION['email'] = $email;
            $success = 'Profile updated.';
            // refresh displayed values
            $user['name'] = $name;
            $user['user'] = $user_type;
            $user['username'] = $username;
            $user['email'] = $email;
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Your Account</title>
    <style>
        :root {
            --bg: #0b1120;
            --surface: rgba(12, 24, 48, 0.92);
            --surface-soft: rgba(15, 34, 78, 0.88);
            --primary: #61dafb;
            --primary-dark: #38bdf8;
            --text: #f8fafc;
            --muted: #94a3b8;
            --border: rgba(97, 218, 251, 0.2);
            --shadow: 0 30px 80px rgba(8, 15, 35, 0.35);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--text);
            background: radial-gradient(circle at top left, rgba(97, 218, 251, 0.16), transparent 24%),
                        radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.18), transparent 20%),
                        linear-gradient(180deg, #07101f 0%, #0e1a36 100%);
        }

        .page {
            width: min(1080px, calc(100% - 32px));
            margin: 0 auto;
            padding: 34px 0 48px;
        }

        .hero {
            display: grid;
            grid-template-columns: 1fr;
            gap: 22px;
            margin-bottom: 32px;
            padding: 28px;
            border-radius: 32px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
        }

        .hero h1 {
            margin: 0;
            font-size: clamp(2.5rem, 4vw, 3.6rem);
            line-height: 1.05;
        }

        .hero p {
            margin: 12px 0 0;
            max-width: 720px;
            color: var(--muted);
            font-size: 1.05rem;
            line-height: 1.8;
        }

        .card {
            display: grid;
            gap: 24px;
            background: var(--surface);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 30px;
            padding: 32px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .card-header h2 {
            margin: 0;
            font-size: 2rem;
        }

        .card-header p {
            margin: 0;
            color: var(--muted);
            max-width: 680px;
        }

        .alert {
            padding: 16px 18px;
            border-radius: 18px;
            font-size: 0.98rem;
            line-height: 1.6;
        }

        .alert.error {
            background: rgba(248, 113, 113, 0.12);
            color: #fecaca;
            border: 1px solid rgba(248, 113, 113, 0.2);
        }

        .alert.success {
            background: rgba(59, 130, 246, 0.12);
            color: #bfdbfe;
            border: 1px solid rgba(59, 130, 246, 0.22);
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 28px;
        }

        .profile-panel {
            border-radius: 24px;
            padding: 24px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .profile-panel h3 {
            margin: 0 0 10px;
            font-size: 1.2rem;
        }

        .profile-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .profile-meta div {
            padding: 18px;
            border-radius: 16px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .profile-meta span {
            display: block;
            color: var(--muted);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .profile-meta strong {
            display: block;
            font-size: 1.1rem;
            color: var(--text);
        }

        .user-form {
            display: grid;
            gap: 18px;
        }

        .form-group {
            display: grid;
            gap: 10px;
        }

        label {
            color: var(--text);
            font-weight: 700;
            font-size: 0.95rem;
        }

        input {
            width: 100%;
            padding: 16px 18px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.05);
            color: var(--text);
            font-size: 1rem;
            transition: border-color 0.25s ease, transform 0.25s ease;
        }

        input:focus {
            outline: none;
            border-color: rgba(97, 218, 251, 0.6);
            transform: translateY(-1px);
            box-shadow: 0 0 0 4px rgba(97, 218, 251, 0.12);
        }

        .button {
            background: linear-gradient(135deg, #61dafb, #0ea5e9);
            color: #0b1120;
            border: none;
            border-radius: 16px;
            padding: 16px 22px;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 20px 40px rgba(97, 218, 251, 0.18);
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 48px rgba(97, 218, 251, 0.26);
        }

        .inline-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
        }

        .inline-actions a {
            color: #61dafb;
            font-weight: 700;
            text-decoration: none;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(97, 218, 251, 0.12);
            color: #bfdbfe;
            font-size: 0.9rem;
            letter-spacing: 0.03em;
        }

        @media (max-width: 860px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .page { padding: 24px 16px 32px; }
            .hero { padding: 22px; }
            .card { padding: 22px; }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="page">
    <section class="hero">
        <div>
            <h1>Welcome back, <?= htmlspecialchars($user['name']) ?>!</h1>
            <p>Manage your profile, update your info, and keep your account secure with a beautiful dashboard experience.</p>
        </div>
        <div class="tag">Personal account dashboard</div>
    </section>

    <div class="card">
        <div class="card-header">
            <div>
                <h2>Account settings</h2>
                <p>Update your profile details and password whenever you need. Your changes are saved securely.</p>
            </div>
            <div class="tag">Signed in as <?= htmlspecialchars($user['user']) ?></div>
        </div>

        <?php if (!empty($error)): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if (!empty($success)): ?><div class="alert success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <div class="profile-grid">
            <section class="profile-panel">
                <h3>Profile overview</h3>
                <div class="profile-meta">
                    <div>
                        <span>User role</span>
                        <strong><?= htmlspecialchars($user['user']) ?></strong>
                    </div>
                    <div>
                        <span>Username</span>
                        <strong><?= htmlspecialchars($user['username']) ?></strong>
                    </div>
                    <div>
                        <span>Email address</span>
                        <strong><?= htmlspecialchars($user['email']) ?></strong>
                    </div>
                    <div>
                        <span>Member since</span>
                        <strong><?= date('F Y') ?></strong>
                    </div>
                </div>
            </section>

            <section class="user-form">
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="user">User type</label>
                        <input id="user" name="user" value="<?= htmlspecialchars($user['user']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="<?= htmlspecialchars($user['email']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Change password</label>
                        <input id="password" name="password" type="password" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="inline-actions">
                        <button class="button" type="submit">Save changes</button>
                        <a href="cars.php">Go to My Cars</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>