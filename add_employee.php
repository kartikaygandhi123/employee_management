<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];

    $stmt = $conn->prepare("INSERT INTO employees (name, email, phone, position) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $position);
    $stmt->execute();
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Employee</title>
</head>

<body>
    <h2>Add Employee</h2>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" required>
        <br>
        <label>Email:</label>
        <input type="email" name="email" required>
        <br>
        <label>Phone:</label>
        <input type="text" name="phone" required>
        <br>
        <label>Position:</label>
        <input type="text" name="position" required>
        <br>
        <button type="submit">Add Employee</button>
    </form>
</body>

</html>