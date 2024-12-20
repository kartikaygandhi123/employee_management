<?php
require 'includes/db.php';

// Fetch transfer history with months and days duration calculation
$transfer_history = $conn->query("
    SELECT t.id, e.name AS employee_name, 
           b1.name AS from_branch, b2.name AS to_branch, 
           t.transfer_date, t.notes,
           CASE 
               WHEN t.to_branch_id IS NULL THEN 
                   CONCAT(
                       TIMESTAMPDIFF(MONTH, b.assigned_at, t.transfer_date), ' months, ',
                       DATEDIFF(t.transfer_date, b.assigned_at) % 30, ' days'
                   )
               ELSE 
                   CONCAT(
                       TIMESTAMPDIFF(MONTH, b.assigned_at, CURRENT_DATE), ' months, ',
                       DATEDIFF(CURRENT_DATE, b.assigned_at) % 30, ' days'
                   )
           END AS duration
    FROM transfer_history t
    LEFT JOIN employees e ON t.employee_id = e.id
    LEFT JOIN branches b1 ON t.from_branch_id = b1.id
    LEFT JOIN branches b2 ON t.to_branch_id = b2.id
    LEFT JOIN employee_branch b ON t.employee_id = b.employee_id AND (t.to_branch_id = b.branch_id OR t.from_branch_id = b.branch_id)
    ORDER BY t.transfer_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transfer History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Transfer History</h2>
        <a href="dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>From Branch</th>
                    <th>To Branch</th>
                    <th>Date</th>
                    <th>Notes</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $transfer_history->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['employee_name'] ?></td>
                        <td><?= $row['from_branch'] ?? 'N/A' ?></td>
                        <td><?= $row['to_branch'] ?? 'N/A' ?></td>
                        <td><?= $row['transfer_date'] ?></td>
                        <td><?= $row['notes'] ?></td>
                        <td><?= $row['duration'] ?? 'N/A' ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
