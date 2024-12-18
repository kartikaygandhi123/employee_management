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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management Dashboard</title>
</head>

<body>
    <h1>Employee Management Dashboard</h1>
    <a href="add_employee.php">Add Employee</a> |
    <a href="transfer_history.php">View Transfer History</a> |
    <a href="logout.php">Logout</a>

    <h3>Filter and Search</h3>
    <form method="GET">
        <input type="text" name="search" placeholder="Search Employees" value="<?= $search ?>">
        <select name="branch_filter">
            <option value="">All Branches</option>
            <?php while ($branch = $branches->fetch_assoc()): ?>
                <option value="<?= $branch['id'] ?>" <?= $branch_filter == $branch['id'] ? 'selected' : '' ?>>
                    <?= $branch['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Search</button>
    </form>

    <h3>Employees List</h3>
    <table border="1">
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
                        <a href="assign_branch.php?id=<?= $employee['id'] ?>">Assign Branch</a> |
                        <a href="transfer_employee.php?id=<?= $employee['id'] ?>">Transfer</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h3>Pagination</h3>
    <div>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?search=<?= $search ?>&branch_filter=<?= $branch_filter ?>&page=<?= $i ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</body>

</html>