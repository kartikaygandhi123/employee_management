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

<?php
include 'includes/header.php';
?>

<?php
include 'includes/sidebar.php';
?>

<div class="body-wrapper">
<?php
include 'includes/navbar.php';
?>
<div class="container-fluid">
<div class="row">
  <div class="card w-100">
  <div class="card-body">  
    
  <h2>Add Employee</h2>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" class="form-control mb-3" placeholder="name" required>
        <br>
        <label>Email:</label>
        <input type="email" name="email" class="form-control mb-3" placeholder="email" required>
        <br>
        <label>Phone:</label>
        <input type="text" name="phone" class="form-control mb-3" placeholder="phone" required>
        <br>
        <label>Position:</label>
        <input type="text" name="position" class="form-control mb-3" placeholder="position" required>
        <br>
        <button class="btn btn-primary m-1" type="submit">Add Employee</button>
    </form>
    </div>
    </div>
</div>
</div>


<?php
include 'includes/footer.php';
?>
