<?php

include_once("config.php");

$id= $_GET['id'];

$sql ="SELECT * FROM pp WHERE id= :id";

$getUsers =$conn-> prepare($sql);

$getUsers->bindParam(":id",$id);

$getUsers->execute();

$data = $getUsers -> fetch();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit User</title>
</head>
<body>

<form action="update.php" method="POST">
    <input type="hidden" name="id" value="<?= $data['id'] ?>">
    
    <label for="name">Name:</label>
    <input type="text" name="name" value="<?= $data['name'] ?>" required><br><br>
    
    <label for="surname">Surname:</label>
    <input type="text" name="surname" value="<?= $data['user'] ?>" required><br><br>
    
    <label for="username">Username:</label>
    <input type="text" name="username" value="<?= $data['username'] ?>" required><br><br>
    
    <label for="email">Email:</label>
    <input type="email" name="email" value="<?= $data['email'] ?>" required><br><br>
    
    <label for="password">Password:</label>
    <input type="text" name="password" value="<?= $data['password'] ?>" required><br><br>
    
    <input type="submit" name="submit" value="Update">
    <a href="dashboard.php">Cancel</a>
</form>

</body>
</html>