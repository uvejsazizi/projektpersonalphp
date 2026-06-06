<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$cars = [];
$carImages = [];
// Filters, search, sort, pagination
$perPage = 9;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$filters = [];
$params = [':user_id' => $_SESSION['user_id']];

// search query
if (!empty($_GET['q'])) {
    $filters[] = "(name LIKE :q OR description LIKE :q)";
    $params[':q'] = '%' . $_GET['q'] . '%';
}
if (!empty($_GET['make'])) {
    $filters[] = "make = :make";
    $params[':make'] = $_GET['make'];
}
if (!empty($_GET['model'])) {
    $filters[] = "model = :model";
    $params[':model'] = $_GET['model'];
}
if (!empty($_GET['year_min'])) {
    $filters[] = "year >= :year_min";
    $params[':year_min'] = (int)$_GET['year_min'];
}
if (!empty($_GET['year_max'])) {
    $filters[] = "year <= :year_max";
    $params[':year_max'] = (int)$_GET['year_max'];
}
if (!empty($_GET['price_min'])) {
    $filters[] = "price >= :price_min";
    $params[':price_min'] = (float)$_GET['price_min'];
}
if (!empty($_GET['price_max'])) {
    $filters[] = "price <= :price_max";
    $params[':price_max'] = (float)$_GET['price_max'];
}

$where = "WHERE user_id = :user_id";
if (!empty($filters)) {
    $where .= ' AND ' . implode(' AND ', $filters);
}

$sort = 'id DESC';
if (!empty($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_asc': $sort = 'price ASC'; break;
        case 'price_desc': $sort = 'price DESC'; break;
        case 'year_asc': $sort = 'year ASC'; break;
        case 'year_desc': $sort = 'year DESC'; break;
        case 'name_asc': $sort = 'name ASC'; break;
        case 'name_desc': $sort = 'name DESC'; break;
    }
}

try {
    // total count for pagination
    $countStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM cars $where");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    $stmt = $conn->prepare("SELECT * FROM cars $where ORDER BY $sort LIMIT :limit OFFSET :offset");
    foreach ($params as $k => $v) {
        // bind params except limit/offset
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($cars)) {
        $conn->exec("CREATE TABLE IF NOT EXISTS car_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            car_id INT NOT NULL,
            image_path VARCHAR(500) NOT NULL,
            FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $ids = array_column($cars, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $imagesStmt = $conn->prepare("SELECT * FROM car_images WHERE car_id IN ($placeholders)");
        $imagesStmt->execute($ids);
        $imageRows = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($imageRows as $row) {
            $carImages[$row['car_id']][] = $row;
        }
    }
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
        .gallery-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin: 18px 0 0;
        }
        .gallery-thumb {
            border-radius: 14px;
            overflow: hidden;
            min-height: 70px;
            background: #f9fafb;
            border: 1px solid #d1d5db;
        }
        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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

        <form method="get" style="display:flex;gap:12px;flex-wrap:wrap;margin-top:18px;align-items:center;">
            <input type="search" name="q" placeholder="Search name or description" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="padding:10px;border-radius:12px;border:1px solid #e5e7eb;min-width:220px;">
            <input type="text" name="make" placeholder="Make" value="<?= htmlspecialchars($_GET['make'] ?? '') ?>" style="padding:10px;border-radius:12px;border:1px solid #e5e7eb;">
            <input type="text" name="model" placeholder="Model" value="<?= htmlspecialchars($_GET['model'] ?? '') ?>" style="padding:10px;border-radius:12px;border:1px solid #e5e7eb;">
            <input type="number" name="year_min" placeholder="Year from" value="<?= htmlspecialchars($_GET['year_min'] ?? '') ?>" style="padding:10px;border-radius:12px;border:1px solid #e5e7eb;width:110px;">
            <input type="number" name="year_max" placeholder="Year to" value="<?= htmlspecialchars($_GET['year_max'] ?? '') ?>" style="padding:10px;border-radius:12px;border:1px solid #e5e7eb;width:110px;">
            <input type="number" step="0.01" name="price_min" placeholder="Price min" value="<?= htmlspecialchars($_GET['price_min'] ?? '') ?>" style="padding:10px;border-radius:12px;border:1px solid #e5e7eb;width:120px;">
            <input type="number" step="0.01" name="price_max" placeholder="Price max" value="<?= htmlspecialchars($_GET['price_max'] ?? '') ?>" style="padding:10px;border-radius:12px;border:1px solid #e5e7eb;width:120px;">
            <select name="sort" style="padding:10px;border-radius:12px;border:1px solid #e5e7eb;">
                <option value="">Sort</option>
                <option value="price_asc" <?= (($_GET['sort'] ?? '')==='price_asc')? 'selected':'' ?>>Price ↑</option>
                <option value="price_desc" <?= (($_GET['sort'] ?? '')==='price_desc')? 'selected':'' ?>>Price ↓</option>
                <option value="year_asc" <?= (($_GET['sort'] ?? '')==='year_asc')? 'selected':'' ?>>Year ↑</option>
                <option value="year_desc" <?= (($_GET['sort'] ?? '')==='year_desc')? 'selected':'' ?>>Year ↓</option>
                <option value="name_asc" <?= (($_GET['sort'] ?? '')==='name_asc')? 'selected':'' ?>>Name A→Z</option>
                <option value="name_desc" <?= (($_GET['sort'] ?? '')==='name_desc')? 'selected':'' ?>>Name Z→A</option>
            </select>
            <button type="submit" class="btn btn-secondary" style="padding:8px 14px;">Filter</button>
        </form>

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
                        <?php
                            $primaryImage = !empty($car['image']) ? $car['image'] : ($carImages[$car['id']][0]['image_path'] ?? '');
                        ?>
                        <div class="car-image">
                            <?php if (!empty($primaryImage)): ?>
                                <img src="<?= htmlspecialchars($primaryImage) ?>" alt="<?= htmlspecialchars($car['name']) ?>">
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
                                <?php if (isset($car['price']) && $car['price'] !== ''): ?>
                                    <div class="car-detail">
                                        <div class="car-detail-label">Price</div>
                                        <div class="car-detail-value"><?= htmlspecialchars($car['price']) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($carImages[$car['id']])): ?>
                                <div class="gallery-row">
                                    <?php foreach (array_slice($carImages[$car['id']], 0, 3) as $galleryPhoto): ?>
                                        <div class="gallery-thumb">
                                            <img src="<?= htmlspecialchars($galleryPhoto['image_path']) ?>" alt="<?= htmlspecialchars($car['name']) ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="car-actions">
                                <a href="edit.php?id=<?= $car['id'] ?>" class="edit">Edit</a>
                                <a href="delete.php?id=<?= $car['id'] ?>" class="delete" onclick="return confirm('Are you sure?')">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($total) && $total > $perPage):
            $totalPages = (int)ceil($total / $perPage);
        ?>
            <nav style="display:flex;gap:8px;justify-content:center;margin:28px 0;">
                <?php
                    $queryBase = $_GET;
                    if ($page > 1) {
                        $queryBase['page'] = $page - 1;
                        echo '<a class="btn btn-secondary" href="?' . http_build_query($queryBase) . '">Prev</a>';
                    }
                    for ($p = 1; $p <= $totalPages; $p++) {
                        $queryBase['page'] = $p;
                        $cls = $p === $page ? 'btn' : 'btn btn-secondary';
                        echo '<a class="' . $cls . '" href="?' . http_build_query($queryBase) . '" style="padding:8px 12px;">' . $p . '</a>';
                    }
                    if ($page < $totalPages) {
                        $queryBase['page'] = $page + 1;
                        echo '<a class="btn btn-secondary" href="?' . http_build_query($queryBase) . '">Next</a>';
                    }
                ?>
            </nav>
        <?php endif; ?>

        <?php include 'footer.php'; ?>
    </div>
</body>
</html>
