<?php
require 'includes/db.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=branch_transfer_history.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "Branch Name\tEmployee Name\tAssigned Date\tRemoved Date\tTenure (Days)\tOrder Number\tAttachment\tNotes\n";

// Fetch all transfer records grouped by branch and employee
$query = "
    SELECT b.name AS branch_name, e.name AS employee_name, th.transfer_date, th.order_number, 
           th.attachment_path, th.notes, th.from_branch_id, th.to_branch_id,
           fb.name AS from_branch, tb.name AS to_branch, th.employee_id
    FROM transfer_history th
    LEFT JOIN employees e ON th.employee_id = e.id
    LEFT JOIN branches b ON th.from_branch_id = b.id OR th.to_branch_id = b.id
    LEFT JOIN branches fb ON th.from_branch_id = fb.id
    LEFT JOIN branches tb ON th.to_branch_id = tb.id
    ORDER BY b.name, e.name, th.transfer_date
";

$result = mysqli_query($conn, $query);

$employee_transfers = [];

while ($row = mysqli_fetch_assoc($result)) {
    $branch_name = $row['branch_name'];
    $employee_id = $row['employee_id'];
    $employee_name = $row['employee_name'];
    
    // Track assigned and removed dates
    if (!isset($employee_transfers[$branch_name][$employee_id])) {
        $employee_transfers[$branch_name][$employee_id] = [];
    }

    if ($row['from_branch'] == null && $row['to_branch'] != null) {
        // Assigned
        $employee_transfers[$branch_name][$employee_id][] = [
            'assigned_date' => $row['transfer_date'],
            'removed_date' => null,
            'order_number' => $row['order_number'],
            'attachment_path' => $row['attachment_path'],
            'notes' => $row['notes']
        ];
    } elseif ($row['from_branch'] != null && $row['to_branch'] == null) {
        // Removed
        foreach ($employee_transfers[$branch_name][$employee_id] as &$record) {
            if ($record['removed_date'] === null) {
                $record['removed_date'] = $row['transfer_date'];
                break;
            }
        }
    }
}

// Output data in Excel format
foreach ($employee_transfers as $branch_name => $employees) {
    foreach ($employees as $employee_id => $records) {
        foreach ($records as $record) {
            $assigned_date = $record['assigned_date'] ? date('d M Y', strtotime($record['assigned_date'])) : 'N/A';
            $removed_date = $record['removed_date'] ? date('d M Y', strtotime($record['removed_date'])) : 'N/A';

            // Calculate tenure (only if removed date exists)
            $tenure = 'N/A';
            if ($record['removed_date']) {
                $assigned_ts = strtotime($record['assigned_date']);
                $removed_ts = strtotime($record['removed_date']);
                $tenure = round(($removed_ts - $assigned_ts) / (60 * 60 * 24)) . ' days';
            }

            echo "$branch_name\t$employee_name\t$assigned_date\t$removed_date\t$tenure\t"
                . ($record['order_number'] ?: 'N/A') . "\t"
                . (!empty($record['attachment_path']) ? $record['attachment_path'] : 'No Attachment') . "\t"
                . ($record['notes'] ?: 'N/A') . "\n";
        }
    }
}
?>
