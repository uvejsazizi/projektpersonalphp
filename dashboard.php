<?php

session_start();

include_once('config.php');

$sql = "SELECT * FROM pp";
$selectUsers = $conn->prepare($sql);
$selectUsers->execute();

$user = $selectUsers->fetchAll();

// If current session is admin, load inquiries
$inquiries = [];
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    try {
        $iq = $conn->prepare("SELECT i.id, i.car_id, i.user_id, i.message, i.offer_price, i.created_at, c.name as car_name, u.email as user_email, u.name as user_name FROM inquiries i LEFT JOIN cars c ON i.car_id = c.id LEFT JOIN pp u ON i.user_id = u.id ORDER BY i.created_at DESC");
        $iq->execute();
        $inquiries = $iq->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // ignore if table doesn't exist yet
        $inquiries = [];
    }
}


?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - MySystem</title>
    <style>
        :root {
            --bg: #eef2ff;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --accent: #7c3aed;
            --border: rgba(148, 163, 184, 0.22);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', Arial, sans-serif;
            background: radial-gradient(circle at top left, rgba(37, 99, 235, 0.14), transparent 26%),
                        radial-gradient(circle at bottom right, rgba(124, 58, 237, 0.12), transparent 18%),
                        var(--bg);
            color: var(--text);
        }

        .page {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 32px 0 48px;
        }

        .dashboard-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
        }

        .dashboard-title {
            margin: 0;
            font-size: clamp(2rem, 2.8vw, 3rem);
            line-height: 1.05;
        }

        .dashboard-description {
            margin: 14px 0 0;
            color: var(--muted);
            max-width: 640px;
            line-height: 1.75;
        }

        .dashboard-badge {
            display: inline-flex;
            align-items: center;
            padding: 14px 24px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.12);
            color: var(--primary);
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .dashboard-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 18px;
        }

        .dashboard-button {
            padding: 12px 24px;
            border: none;
            border-radius: 14px;
            background: var(--primary);
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .dashboard-button.secondary {
            background: #e2e8f0;
            color: var(--text);
        }

        .dashboard-button:hover {
            transform: translateY(-1px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            padding: 24px;
            border-radius: 26px;
            background: var(--surface);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(148, 163, 184, 0.12);
        }

        .stat-card strong {
            display: block;
            font-size: 1.9rem;
            margin-bottom: 8px;
        }

        .stat-card span {
            color: var(--muted);
            font-size: 0.95rem;
        }

        .table-panel {
            border-radius: 30px;
            overflow: hidden;
            background: var(--surface);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(148, 163, 184, 0.15);
        }

        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        .dashboard-table th,
        .dashboard-table td {
            padding: 18px 22px;
            text-align: left;
        }

        .dashboard-table th {
            background: var(--surface-soft);
            color: var(--text);
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.22);
        }

        .dashboard-table td {
            color: #334155;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        }

        .dashboard-table tr:hover td {
            background: #f8fafc;
        }

        .dashboard-table tbody tr:last-child td {
            border-bottom: none;
        }

        .action-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .action-link:hover {
            color: #1d4ed8;
        }

        .table-footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 18px 24px;
            background: var(--surface-soft);
            color: var(--muted);
            font-size: 0.95rem;
        }

        @media (max-width: 920px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .dashboard-header,
            .table-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-table {
                min-width: 100%;
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  </head>
  <body>
    <div class="page">
        <?php include 'header.php'; ?>

        <header class="dashboard-header">
            <div>
                <h1 class="dashboard-title">User Dashboard</h1>
                <p class="dashboard-description">A polished dashboard for reviewing registered users, editing records, and keeping the interface modern and professional.</p>
                <div class="dashboard-actions">
                    <a class="dashboard-button" href="register.php">Add user</a>
                    <a class="dashboard-button secondary" href="login.php">View login</a>
                </div>
            </div>
            <span class="dashboard-badge"><?= count($user) ?> active users</span>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <strong><?= count($user) ?></strong>
                <span>Total users registered</span>
            </div>
            <div class="stat-card">
                <strong><?= count($user) * 2 ?></strong>
                <span>Action items today</span>
            </div>
            <div class="stat-card">
                <strong>Fresh</strong>
                <span>Clean design for quick review and action.</span>
            </div>
        </div>

        <div class="table-panel">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>User</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['user']) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['password']) ?></td>
                            <td>
                                <a class="action-link" href="edit.php?id=<?= urlencode($row['id']) ?>">Edit</a>
                                <span style="color: #94a3b8; margin: 0 10px;">|</span>
                                <a class="action-link" href="delete.php?id=<?= urlencode($row['id']) ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="table-footer">
                <span><?= count($user) ?> users shown</span>
                <span>Updated live from the database.</span>
            </div>
        </div>

        <?php if (!empty($inquiries)): ?>
            <h2 style="margin-top:28px;">Incoming Inquiries</h2>
            <div class="table-panel">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Car</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Offer Price</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inquiries as $iq): ?>
                        <tr>
                            <td><?= htmlspecialchars($iq['id']) ?></td>
                            <td><?= htmlspecialchars($iq['car_name'] ?? '—') ?> (ID <?= htmlspecialchars($iq['car_id']) ?>)</td>
                            <td><?= htmlspecialchars($iq['user_name'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($iq['user_email'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($iq['message'] ?? '') ?></td>
                            <td><?= htmlspecialchars($iq['offer_price'] ?? '') ?></td>
                            <td><?= htmlspecialchars($iq['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php include 'footer.php'; ?>
    </div>
  </body>
</html>