<?php
require 'includes/db.php';

$employee_id = $_GET['id'];
$branches = $conn->query("SELECT * FROM branches");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branch_id = $_POST['branch_id'];

    $stmt = $conn->prepare("INSERT INTO employee_branch (employee_id, branch_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $employee_id, $branch_id);
    $stmt->execute();
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Assign Branch</title>
</head>

<body>
    <h2>Assign Branch</h2>
    <form method="POST">
        <label>Select Branch:</label>
        <select name="branch_id" required>
            <?php while ($branch = $branches->fetch_assoc()): ?>
                <option value="<?= $branch['id'] ?>"><?= $branch['name'] ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Assign</button>
    </form>
</body>

</html>