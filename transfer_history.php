<?php
require 'includes/db.php';

$transfer_history = $conn->query("
    SELECT t.id, e.name AS employee_name, b1.name AS from_branch, b2.name AS to_branch, 
           t.transfer_date, t.notes
    FROM transfer_history t
    LEFT JOIN employees e ON t.employee_id = e.id
    LEFT JOIN branches b1 ON t.from_branch_id = b1.id
    LEFT JOIN branches b2 ON t.to_branch_id = b2.id
    ORDER BY t.transfer_date DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Transfer History</title>
</head>

<body>
    <h2>Transfer History</h2>
    <a href="dashboard.php">Back to Dashboard</a>
    <table border="1">
        <tr>
            <th>Employee</th>
            <th>From Branch</th>
            <th>To Branch</th>
            <th>Date</th>
            <th>Notes</th>
        </tr>
        <?php while ($row = $transfer_history->fetch_assoc()): ?>
            <tr>
                <td><?= $row['employee_name'] ?></td>
                <td><?= $row['from_branch'] ?? 'N/A' ?></td>
                <td><?= $row['to_branch'] ?></td>
                <td><?= $row['transfer_date'] ?></td>
                <td><?= $row['notes'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>

</html>