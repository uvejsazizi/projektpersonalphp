<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySystem</title>
    <style>
        :root {
            --bg: #f4f7ff;
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --surface: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --radius: 20px;
            --shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', Arial, sans-serif;
            background: radial-gradient(circle at top left, rgba(59,132,246,.14), transparent 28%),
                        radial-gradient(circle at bottom right, rgba(59,132,246,.10), transparent 20%),
                        var(--bg);
            color: var(--text);
        }
        .page {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 48px;
        }
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            border-radius: 28px;
            background: rgba(255,255,255,.92);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
        }
        .logo {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-dark);
        }
        .nav-links {
            display: flex;
            gap: 22px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .nav-links a {
            color: var(--muted);
            text-decoration: none;
            font-weight: 500;
            transition: color .2s ease;
        }
        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary-dark);
        }
        .hero {
            margin-top: 48px;
            display: grid;
            gap: 28px;
            grid-template-columns: minmax(0, 1fr);
        }
        .hero-card {
            padding: 40px;
            border-radius: var(--radius);
            background: var(--surface);
            box-shadow: var(--shadow);
            max-width: 760px;
        }
        .hero-card h1 {
            margin: 0 0 16px;
            font-size: clamp(2.4rem, 4vw, 4rem);
            line-height: 1.05;
        }
        .hero-card p {
            margin: 0 0 28px;
            color: var(--muted);
            font-size: 1rem;
            max-width: 620px;
            line-height: 1.75;
        }
        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 22px;
            border-radius: 14px;
            border: 0;
            font-weight: 700;
            color: #ffffff;
            background: var(--primary);
            text-decoration: none;
            transition: transform .2s ease, background .2s ease;
        }
        .btn-secondary {
            background: #e5e7eb;
            color: #111827;
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }
        .summary {
            margin-top: 36px;
            display: grid;
            gap: 18px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 22px 24px;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }
        .summary-item strong {
            display: block;
            font-size: 1.1rem;
            color: var(--primary-dark);
        }
        .summary-item span {
            color: var(--muted);
        }
        @media (max-width: 720px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
            }
            .hero-card {
                padding: 30px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <?php include 'header.php'; ?>

        <main class="hero">
            <section class="hero-card">
                <h1>Build your account in a beautiful PHP dashboard.</h1>
                <p>Use the login and registration pages to create secure access. This page now includes a polished layout with consistent brand styling and a responsive design.</p>
                <div class="hero-actions">
                    <a class="btn" href="register.php">Register now</a>
                    <a class="btn btn-secondary" href="login.php">Login</a>
                </div>
            </section>
            <section class="summary">
                <div class="summary-item">
                    <div>
                        <strong>Fast onboarding</strong>
                        <span>Register and login with a modern experience.</span>
                    </div>
                    <span>Ready</span>
                </div>
                <div class="summary-item">
                    <div>
                        <strong>Responsive layout</strong>
                        <span>Looks great on desktop and mobile.</span>
                    </div>
                    <span>Adaptive</span>
                </div>
                <div class="summary-item">
                    <div>
                        <strong>Clean interface</strong>
                        <span>Simple navigation and clear calls to action.</span>
                    </div>
                    <span>Designed</span>
                </div>
            </section>
        </main>
        <?php include 'footer.php'; ?>
    </div>
</body>
</html>