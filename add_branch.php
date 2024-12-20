<?php
require 'includes/access_control.php';
require 'includes/db.php';
checkAccess('admin');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $location = $_POST['location'];
    

    $stmt = $conn->prepare("INSERT INTO branches (name, location) VALUES (?, ?)");
    $stmt->bind_param("ss", $name,$location);
    $stmt->execute();
    header("Location: dashboard.php");
    exit();
}
?>

<!-- fetch Branches -->
<?php
$fetch_branches = $conn->query("
SELECT b.id, b.name AS branch_name, b.location AS branch_location
FROM branches b");
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

<!-- add branch -->

<div class="row">
  <div class="card w-100">
  <div class="card-body">  
  <div class="card-title mb-9 fw-semibold">
<h5> Add Branch </h5>
</div>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" class="form-control mb-3" placeholder="name" required>
        <br>
        <label>Branch:</label>
        <input type="text" name="location" class="form-control mb-3" placeholder="location" required>
        <br>
        
        <button class="btn btn-primary m-1" type="submit">Add Branch</button>
    </form>
    </div>
    </div>
</div>


<!-- BRANCHES DATA -->
<div class="row">
<div class="card w-100">
<div class="card-body">
<div class="card-title mb-9 fw-semibold">
<h5> Branch List </h5>
</div>
    <table class="table table-bordered" border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Branch Name</th>
                <th>Location</th>
                </tr>
        </thead>
        <tbody>
            <?php while ($branches = $fetch_branches->fetch_assoc()): ?>
                <tr>
                    <td><?= $branches['id'] ?></td>
                    <td><?= $branches['branch_name'] ?></td>
                    <td><?= $branches['branch_location'] ?></td>
                    </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
        </div>
        </div>
        </div>
</div>


<?php
include 'includes/footer.php';
?>
