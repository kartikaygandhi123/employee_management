<?php
session_start();
require 'includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Search and Filter
$search = $_GET['search'] ?? '';
$branch_filter = $_GET['branch_filter'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Query for employees with optional search and branch filter
$query = "
    SELECT e.id, e.name, e.email, e.phone, e.position, 
           GROUP_CONCAT(b.name SEPARATOR ', ') AS branches
    FROM employees e
    LEFT JOIN employee_branch eb ON e.id = eb.employee_id
    LEFT JOIN branches b ON eb.branch_id = b.id
    WHERE (e.name LIKE '%$search%' OR e.email LIKE '%$search%' OR e.phone LIKE '%$search%')
";

if ($branch_filter) {
    $query .= " AND b.id = $branch_filter";
}

$query .= " GROUP BY e.id LIMIT $limit OFFSET $offset";
$employees = $conn->query($query);

// Fetch branches for the branch filter dropdown
$branches = $conn->query("SELECT * FROM branches");

// Get total record count for pagination
$total_records_query = "
    SELECT COUNT(DISTINCT e.id) AS count 
    FROM employees e
    LEFT JOIN employee_branch eb ON e.id = eb.employee_id
    LEFT JOIN branches b ON eb.branch_id = b.id
    WHERE (e.name LIKE '%$search%' OR e.email LIKE '%$search%' OR e.phone LIKE '%$search%')
";

if ($branch_filter) {
    $total_records_query .= " AND b.id = $branch_filter";
}

$total_records = $conn->query($total_records_query)->fetch_assoc()['count'];
$total_pages = ceil($total_records / $limit);
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
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success" role="alert">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']); // Clear the message after displaying
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']); // Clear the message after displaying
}
?>

<div class="card">
<div class="card-body"> 
<div class="row">
<div class="col-12">
  <h5 class="card-title mb-9 fw-semibold"> Employees List </h5>
</div>

<div class="row">
  <div class="w-100">
  <div class="card-body">  
    <form method="GET">
    <div class="row">
        <div class="col-lg-4">
        <input type="text" name="search" class="form-control mb-3" placeholder="Search Employees" value="<?= $search ?>">
        </div>  
        <div class="col-lg-4">      
        <select class="form-select mb-3" name="branch_filter">
            <option value="">All Branches</option>
            <?php while ($branch = $branches->fetch_assoc()): ?>
                <option value="<?= $branch['id'] ?>" <?= $branch_filter == $branch['id'] ? 'selected' : '' ?>>
                    <?= $branch['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>
        </div>
        <div class="col-lg-4">
        <button class="btn btn-primary m-1" type="submit">Search</button>
        </div>
        </div>
    </form> 
    </div>
    </div>
</div>

</div>

<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Position</th>
                <th>Branches</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($employee = $employees->fetch_assoc()): ?>
                <tr>
                    <td><?= $employee['id'] ?></td>
                    <td><?= $employee['name'] ?></td>
                    <td><?= $employee['email'] ?></td>
                    <td><?= $employee['phone'] ?></td>
                    <td><?= $employee['position'] ?></td>
                    <td><?= $employee['branches'] ?: 'No Branch Assigned' ?></td>
                    <td>
                        <a class="btn btn-outline-primary m-1" href="assign_branch.php?id=<?= $employee['id'] ?>">Assign Branch</a> |
                        <a class="btn btn-outline-secondary m-1" href="transfer_employee.php?id=<?= $employee['id'] ?>">Transfer</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <div>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?search=<?= $search ?>&branch_filter=<?= $branch_filter ?>&page=<?= $i ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    </div>
    </div>
    </div>
    </div>
    </div>
  </div>
<?php
include 'includes/footer.php';
?>