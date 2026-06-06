<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$car_id = $_GET['car_id'] ?? null;
$error = '';
$success = '';

if (!$car_id) {
    header('Location: cars.php');
    exit;
}


try {
    $stmt = $conn->prepare('SELECT id, name FROM cars WHERE id = :id');
    $stmt->bindParam(':id', $car_id);
    $stmt->execute();
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$car) {
        header('Location: cars.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $offer_price = trim($_POST['offer_price'] ?? '');

    if ($message === '' && $offer_price === '') {
        $error = 'Please provide a message or offer price.';
    } else {
        try {
           
            $conn->exec("CREATE TABLE IF NOT EXISTS inquiries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                car_id INT NOT NULL,
                user_id INT NOT NULL,
                message TEXT NULL,
                offer_price DECIMAL(10,2) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $ins = $conn->prepare('INSERT INTO inquiries (car_id, user_id, message, offer_price) VALUES (:car_id, :user_id, :message, :offer_price)');
            $ins->bindParam(':car_id', $car_id);
            $ins->bindParam(':user_id', $_SESSION['user_id']);
            $ins->bindParam(':message', $message);
            $ins->bindParam(':offer_price', $offer_price);
            $ins->execute();

            $success = 'Your inquiry has been sent to the admin.';
            header('Location: cars.php');
            exit;
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
    <title>Contact about <?= htmlspecialchars($car['name'] ?? 'Car') ?></title>
    <style>body{font-family:Inter,Arial,sans-serif;padding:24px}</style>
</head>
<body>
<?php include 'header.php'; ?>
<div style="max-width:720px;margin:24px auto;padding:18px;border:1px solid #eee;border-radius:8px;">
    <h2>Contact about <?= htmlspecialchars($car['name']) ?></h2>
    <?php if (!empty($error)): ?><div style="color:#991b1b"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
        <div style="margin:8px 0;"><label>Message</label><br><textarea name="message" rows="5" style="width:100%"></textarea></div>
        <div style="margin:8px 0;"><label>Offer Price (optional)</label><br><input type="number" name="offer_price" step="0.01" style="width:100%"></div>
        <div style="margin-top:12px"><button type="submit">Send Inquiry</button> <a href="cars.php">Cancel</a></div>
    </form>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
