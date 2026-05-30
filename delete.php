<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: cars.php');
    exit;
}

try {
    $stmt = $conn->prepare('SELECT image FROM cars WHERE id = :id AND user_id = :user_id');
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($car) {
        if (!empty($car['image'])) {
            $imagePath = __DIR__ . '/' . ltrim($car['image'], '/');
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS car_images (
                id INT AUTO_INCREMENT PRIMARY KEY,
                car_id INT NOT NULL,
                image_path VARCHAR(500) NOT NULL,
                FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $imgs = $conn->prepare('SELECT image_path FROM car_images WHERE car_id = :id');
            $imgs->bindParam(':id', $id);
            $imgs->execute();
            while ($row = $imgs->fetch(PDO::FETCH_ASSOC)) {
                $imagePath = __DIR__ . '/' . ltrim($row['image_path'], '/');
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $deleteImages = $conn->prepare('DELETE FROM car_images WHERE car_id = :id');
            $deleteImages->bindParam(':id', $id);
            $deleteImages->execute();
        } catch (PDOException $e) {
            // ignore missing table or other issues
        }

        $delete = $conn->prepare('DELETE FROM cars WHERE id = :id AND user_id = :user_id');
        $delete->bindParam(':id', $id);
        $delete->bindParam(':user_id', $_SESSION['user_id']);
        $delete->execute();
    }
} catch (PDOException $e) {
    // Optionally log error or show a message. Redirect back to the cars page.
}

header('Location: cars.php');
exit;
?>