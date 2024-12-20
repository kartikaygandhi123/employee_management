<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];

    $stmt = $conn->prepare("INSERT INTO employees (name, email, phone, position) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $position);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Employee created successfully!";
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Failed to create employee: " . $stmt->error;
        header("Location: add_employee.php");
        exit();
    }
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
<?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']); // Clear the message after displaying
        }
        ?>
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
