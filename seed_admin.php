<?php

include 'config.php';

$adminEmail = 'admin@example.com';
$adminPassword = 'Admin123!';
$adminName = 'Administrator';
$adminUserField = 'admin';

try {
    $stmt = $conn->prepare('SELECT id FROM pp WHERE email = :email LIMIT 1');
    $stmt->bindParam(':email', $adminEmail);
    $stmt->execute();
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exists) {
        echo "Admin already exists with email {$adminEmail}.\n";
        exit;
    }

    $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $ins = $conn->prepare('INSERT INTO pp (name, user, username, email, password) VALUES (:name, :user, :username, :email, :password)');
    $ins->bindParam(':name', $adminName);
    $ins->bindParam(':user', $adminUserField);
    $ins->bindParam(':username', $adminEmail);
    $ins->bindParam(':email', $adminEmail);
    $ins->bindParam(':password', $hash);
    $ins->execute();

    echo "Created admin user: {$adminEmail} with password: {$adminPassword}. Change the password immediately.\n";
} catch (PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}

?>