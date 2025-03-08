<?php
require 'includes/db.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=transfer_history.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "Employee ID\tEmployee Name\tBranch Name\tAssigned Date\tRemoved Date\tTenure (Days)\tStatus\n";

$query = "
    SELECT 
        e.id AS employee_id, 
        e.name AS employee_name, 
        b.name AS branch_name,
        MIN(CASE WHEN th.to_branch_id IS NOT NULL THEN th.transfer_date END) AS assigned_date,
        MAX(CASE WHEN th.from_branch_id IS NOT NULL THEN th.transfer_date END) AS removed_date
    FROM transfer_history th
    LEFT JOIN employees e ON th.employee_id = e.id
    LEFT JOIN branches b ON (th.to_branch_id = b.id OR th.from_branch_id = b.id)
    GROUP BY e.id, b.id
    ORDER BY e.id, assigned_date
";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $assigned_date = strtotime($row['assigned_date']);
    $removed_date = $row['removed_date'] ? strtotime($row['removed_date']) : null;
    $status = $removed_date ? "Removed ❌" : "Active ✅";

    $tenure_days = $removed_date ? floor(($removed_date - $assigned_date) / (60 * 60 * 24)) : floor((time() - $assigned_date) / (60 * 60 * 24));

    echo "{$row['employee_id']}\t{$row['employee_name']}\t{$row['branch_name']}\t"
        . (!empty($row['assigned_date']) ? date('d M Y', strtotime($row['assigned_date'])) : 'N/A') . "\t"
        . (!empty($row['removed_date']) ? date('d M Y', strtotime($row['removed_date'])) : 'N/A') . "\t"
        . "$tenure_days\t$status\n";
}
?>
