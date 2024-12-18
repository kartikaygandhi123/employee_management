<?php
require 'includes/db.php';

$employee_id = $_GET['id'];
$branches = $conn->query("SELECT * FROM branches");
$current_branches = $conn->query("
    SELECT b.id, b.name 
    FROM employee_branch eb
    JOIN branches b ON eb.branch_id = b.id
    WHERE eb.employee_id = $employee_id
");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $from_branch_id = $_POST['from_branch_id'];
    $to_branch_id = $_POST['to_branch_id'];
    $notes = $_POST['notes'];

    // Insert the transfer into the transfer history
    $stmt = $conn->prepare("
        INSERT INTO transfer_history (employee_id, from_branch_id, to_branch_id, notes)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiis", $employee_id, $from_branch_id, $to_branch_id, $notes);
    $stmt->execute();

    // Remove the employee from the old branch
    $stmt = $conn->prepare("DELETE FROM employee_branch WHERE employee_id = ? AND branch_id = ?");
    $stmt->bind_param("ii", $employee_id, $from_branch_id);
    $stmt->execute();

    // Assign the employee to the new branch
    $stmt = $conn->prepare("INSERT INTO employee_branch (employee_id, branch_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $employee_id, $to_branch_id);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Transfer Employee</title>
</head>

<body>
    <h2>Transfer Employee</h2>
    <form method="POST">
        <label>From Branch:</label>
        <select name="from_branch_id" required>
            <?php while ($branch = $current_branches->fetch_assoc()): ?>
                <option value="<?= $branch['id'] ?>"><?= $branch['name'] ?></option>
            <?php endwhile; ?>
        </select>
        <br>
        <label>To Branch:</label>
        <select name="to_branch_id" required>
            <?php while ($branch = $branches->fetch_assoc()): ?>
                <option value="<?= $branch['id'] ?>"><?= $branch['name'] ?></option>
            <?php endwhile; ?>
        </select>
        <br>
        <label>Notes:</label>
        <textarea name="notes"></textarea>
        <br>
        <button type="submit">Transfer</button>
    </form>
</body>

</html>