<?php
require 'includes/access_control.php';
require 'includes/db.php';
checkAccess('admin');
// Fetch grouped transfer history
$transfer_history = $conn->query("
    SELECT t.id, e.name AS employee_name, 
           b1.name AS from_branch, b2.name AS to_branch, 
           t.transfer_date, t.notes,
           b.assigned_at AS start_date,
           CASE 
               WHEN t.to_branch_id IS NULL THEN t.transfer_date
               ELSE NULL
           END AS end_date,
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
    ORDER BY e.name, t.transfer_date DESC
");

// Group data by employee
$grouped_history = [];
while ($row = $transfer_history->fetch_assoc()) {
    $grouped_history[$row['employee_name']][] = $row;
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

<div class="card">
<div class="card-body"> 

<h5 class="card-title mb-9 fw-semibold">Transfer History (Grouped by Employee)</h5>

        <?php foreach ($grouped_history as $employee_name => $records): ?>
            <h4><?= $employee_name ?></h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>From Branch</th>
                        <th>To Branch</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Duration</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $row): ?>
                        <tr>
                            <td><?= $row['from_branch'] ?? 'N/A' ?></td>
                            <td><?= $row['to_branch'] ?? 'N/A' ?></td>
                            <td><?= $row['start_date'] ?? 'N/A' ?></td>
                            <td><?= $row['end_date'] ? $row['end_date'] : 'Present' ?></td>
                            <td><?= $row['duration'] ?? 'N/A' ?></td>
                            <td><?= $row['notes'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>




