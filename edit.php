<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';
$car = null;

// Get car ID from URL
$car_id = $_GET['id'] ?? null;

if (!$car_id) {
    header("Location: cars.php");
    exit;
}

// Fetch car data
try {
    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $car_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$car) {
        header("Location: cars.php");
        exit;
    }

    try {
        $conn->exec("CREATE TABLE IF NOT EXISTS car_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            car_id INT NOT NULL,
            image_path VARCHAR(500) NOT NULL,
            FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $imagesStmt = $conn->prepare('SELECT * FROM car_images WHERE car_id = :car_id');
        $imagesStmt->bindParam(':car_id', $car_id);
        $imagesStmt->execute();
        $galleryImages = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $galleryImages = [];
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $make = trim($_POST['make'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $year = trim($_POST['year'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $price = trim($_POST['price'] ?? ($car['price'] ?? ''));
        $image = $car['image'];
        $upload_dir = __DIR__ . '/uploads/';

        if (empty($name)) {
            $error = 'Car name is required!';
        } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array(strtolower($file_ext), $allowed_ext)) {
                $file_name = uniqid('car_', true) . '.' . $file_ext;
                $target_file = $upload_dir . $file_name;
                $image_path = 'uploads/' . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    if (!empty($car['image'])) {
                        $old_image = __DIR__ . '/' . ltrim($car['image'], '/');
                        if (file_exists($old_image)) {
                            unlink($old_image);
                        }
                    }
                    $image = $image_path;
                } else {
                    $error = 'Failed to upload image.';
                }
            } else {
                $error = 'Invalid image format. Only JPG, PNG, GIF, and WebP are allowed.';
            }
        }

        $galleryFiles = [];
        if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0]) && empty($error)) {
            try {
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $conn->exec("CREATE TABLE IF NOT EXISTS car_images (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    car_id INT NOT NULL,
                    image_path VARCHAR(500) NOT NULL,
                    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                foreach ($_FILES['gallery_images']['name'] as $index => $galleryName) {
                    $galleryError = $_FILES['gallery_images']['error'][$index];
                    if ($galleryError !== UPLOAD_ERR_OK) {
                        continue;
                    }
                    $galleryExt = pathinfo($galleryName, PATHINFO_EXTENSION);
                    if (!in_array(strtolower($galleryExt), $allowed_ext)) {
                        $error = 'Invalid gallery image format. Only JPG, PNG, GIF, and WebP are allowed.';
                        break;
                    }

                    $galleryFileName = uniqid('gallery_') . '.' . $galleryExt;
                    $galleryTarget = $upload_dir . $galleryFileName;
                    if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$index], $galleryTarget)) {
                        $galleryFiles[] = 'uploads/' . $galleryFileName;
                    }
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }

        if (empty($error)) {
            try {
                $sql = "UPDATE cars SET name = :name, description = :description, make = :make, 
                    model = :model, year = :year, color = :color, image = :image, price = :price WHERE id = :id AND user_id = :user_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':make', $make);
                $stmt->bindParam(':model', $model);
                $stmt->bindParam(':year', $year);
                $stmt->bindParam(':color', $color);
                $stmt->bindParam(':image', $image);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':id', $car_id);
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();

                if (!empty($galleryFiles)) {
                    $galleryInsert = $conn->prepare('INSERT INTO car_images (car_id, image_path) VALUES (:car_id, :image_path)');
                    foreach ($galleryFiles as $galleryImage) {
                        $galleryInsert->bindParam(':car_id', $car_id);
                        $galleryInsert->bindParam(':image_path', $galleryImage);
                        $galleryInsert->execute();
                    }
                }

                $success = 'Car updated successfully!';
                $car['name'] = $name;
                $car['description'] = $description;
                $car['make'] = $make;
                $car['model'] = $model;
                $car['year'] = $year;
                $car['color'] = $color;
                $car['image'] = $image;
                
                header('Location: cars.php');
                exit;
            } catch(PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
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
    <title>Edit Car | MySystem</title>
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
            width: min(1000px, calc(100% - 32px));
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
            max-width: 600px;
            margin: 0 auto;
        }
        .card h1 {
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 2rem;
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
            font-weight: 600;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .image-preview {
            width: 100%;
            height: 200px;
            background: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            color: var(--muted);
            font-size: 3rem;
            transition: border-color .2s ease, background .2s ease;
            overflow: hidden;
        }
        .image-preview.has-image {
            background: white;
            border-color: var(--primary);
        }
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .gallery-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .gallery-thumb {
            border-radius: 14px;
            overflow: hidden;
            height: 90px;
            background: #f9fafb;
            border: 1px solid #d1d5db;
        }

        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .button {
            width: 100%;
            border: none;
            border-radius: 12px;
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
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .button-group .button {
            flex: 1;
        }
        .button.secondary {
            background: #e5e7eb;
            color: var(--text);
        }
        .button.secondary:hover {
            background: #d1d5db;
        }
        .back-link {
            display: inline-block;
            margin-top: 24px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="page">
        <?php include 'header.php'; ?>
        
        <article class="card">
            <h1>Edit Car</h1>
            <p>Update the details of your car below.</p>

            <?php if (!empty($error)): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($car): ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Car Name *</label>
                        <input id="name" type="text" name="name" placeholder="e.g., My Red Ferrari" required value="<?= htmlspecialchars($car['name']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Describe your car..."><?= htmlspecialchars($car['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="make">Make</label>
                            <input id="make" type="text" name="make" placeholder="e.g., Ferrari" value="<?= htmlspecialchars($car['make'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="model">Model</label>
                            <input id="model" type="text" name="model" placeholder="e.g., F8 Tributo" value="<?= htmlspecialchars($car['model'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="year">Year</label>
                            <input id="year" type="number" name="year" min="1900" max="<?= date('Y') + 1 ?>" placeholder="e.g., 2023" value="<?= htmlspecialchars($car['year'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="color">Color</label>
                            <input id="color" type="text" name="color" placeholder="e.g., Red" value="<?= htmlspecialchars($car['color'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="price">Price</label>
                        <input id="price" type="number" step="0.01" name="price" placeholder="e.g., 25000" value="<?= htmlspecialchars($car['price'] ?? '') ?>">
                    </div>

                    <?php if (!empty($galleryImages)): ?>
                        <div class="gallery-row">
                            <?php foreach ($galleryImages as $gallery): ?>
                                <div class="gallery-thumb">
                                    <img src="<?= htmlspecialchars($gallery['image_path']) ?>" alt="Additional photo">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="gallery_images">Interior & extra photos</label>
                        <input id="gallery_images" type="file" name="gallery_images[]" accept="image/*" multiple>
                        <small style="color: var(--muted);">Upload extra photos to show the interior and more angles.</small>
                    </div>

                    <div class="form-group">
                        <label for="image">Car Image</label>
                        <div class="image-preview <?= !empty($car['image']) ? 'has-image' : '' ?>" id="preview">
                            <?php if (!empty($car['image'])): ?>
                                <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['name']) ?>">
                            <?php else: ?>
                                🖼️
                            <?php endif; ?>
                        </div>
                        <input id="image" type="file" name="image" accept="image/*" style="display: none;">
                        <button type="button" class="button secondary" onclick="document.getElementById('image').click();">Change Image</button>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="button">Update Car</button>
                        <a href="cars.php" class="button secondary" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">Cancel</a>
                    </div>
                </form>

                <a href="cars.php" class="back-link">← Back to Cars</a>
            <?php endif; ?>
        </article>

        <?php include 'footer.php'; ?>
    </div>

    <script>
        const fileInput = document.getElementById('image');
        const preview = document.getElementById('preview');

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    preview.innerHTML = '<img src="' + event.target.result + '" alt="Preview">';
                    preview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>