<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$cars = [];
try {
    $stmt = $conn->prepare("SELECT * FROM cars ORDER BY id DESC");
    $stmt->execute();
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cars | MySystem</title>
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
            width: min(1200px, calc(100% - 32px));
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
        .header-actions {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .header a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 12px;
            transition: background .2s ease;
        }
        .header a:hover {
            background: rgba(59, 130, 246, 0.1);
        }
        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .header-title h1 {
            margin: 0;
            font-size: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: background .2s ease, transform .2s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #e5e7eb;
            color: var(--text);
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }
        .cars-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            margin-top: 32px;
        }
        .car-card {
            background: var(--surface);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .car-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 32px 80px rgba(15, 23, 42, 0.12);
        }
        .car-image {
            width: 100%;
            height: 240px;
            background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 3rem;
        }
        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .car-content {
            padding: 20px;
        }
        .car-content h3 {
            margin: 0 0 8px;
            font-size: 1.3rem;
            color: var(--text);
        }
        .car-content p {
            margin: 0 0 16px;
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .car-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 0.9rem;
        }
        .car-detail {
            background: #f9fafb;
            padding: 8px 12px;
            border-radius: 8px;
        }
        .car-detail-label {
            color: var(--muted);
            font-size: 0.85rem;
        }
        .car-detail-value {
            color: var(--text);
            font-weight: 600;
        }
        .car-actions {
            display: flex;
            gap: 8px;
        }
        .car-actions a,
        .car-actions button {
            flex: 1;
            padding: 10px 12px;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: background .2s ease;
        }
        .car-actions .edit {
            background: var(--primary);
            color: white;
        }
        .car-actions .edit:hover {
            background: var(--primary-dark);
        }
        .car-actions .delete {
            background: #fee2e2;
            color: #991b1b;
        }
        .car-actions .delete:hover {
            background: #fecaca;
        }
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        .empty-state h2 {
            margin: 0 0 12px;
            color: var(--text);
            font-size: 1.8rem;
        }
        .empty-state p {
            margin: 0 0 24px;
            color: var(--muted);
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="page">
        <?php include 'header.php'; ?>
        
        <div class="header-title">
            <h1>My Cars</h1>
            <a href="add.php" class="btn">+ Add New Car</a>
        </div>

        <?php if (isset($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 14px 18px; border-radius: 12px; margin-bottom: 22px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cars)): ?>
            <div class="empty-state">
                <h2>🚗 No cars yet</h2>
                <p>You haven't added any cars to your collection.</p>
                <a href="add.php" class="btn">Add Your First Car</a>
            </div>
        <?php else: ?>
            <div class="cars-container">
                <?php foreach ($cars as $car): ?>
                    <div class="car-card">
                        <div class="car-image">
                            <?php if (!empty($car['image'])): ?>
                                <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['name']) ?>">
                            <?php else: ?>
                                <span>🖼️</span>
                            <?php endif; ?>
                        </div>
                        <div class="car-content">
                            <h3><?= htmlspecialchars($car['name'] ?? 'Unnamed Car') ?></h3>
                            <p><?= htmlspecialchars($car['description'] ?? 'No description') ?></p>
                            
                            <div class="car-details">
                                <?php if (!empty($car['make'])): ?>
                                    <div class="car-detail">
                                        <div class="car-detail-label">Make</div>
                                        <div class="car-detail-value"><?= htmlspecialchars($car['make']) ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($car['model'])): ?>
                                    <div class="car-detail">
                                        <div class="car-detail-label">Model</div>
                                        <div class="car-detail-value"><?= htmlspecialchars($car['model']) ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($car['year'])): ?>
                                    <div class="car-detail">
                                        <div class="car-detail-label">Year</div>
                                        <div class="car-detail-value"><?= htmlspecialchars($car['year']) ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($car['color'])): ?>
                                    <div class="car-detail">
                                        <div class="car-detail-label">Color</div>
                                        <div class="car-detail-value"><?= htmlspecialchars($car['color']) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="car-actions">
                                <a href="edit.php?id=<?= $car['id'] ?>" class="edit">Edit</a>
                                <a href="delete.php?id=<?= $car['id'] ?>" class="delete" onclick="return confirm('Are you sure?')">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php include 'footer.php'; ?>
    </div>
</body>
</html>
