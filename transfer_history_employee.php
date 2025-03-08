<?php
require 'includes/access_control.php';
require 'includes/db.php';
checkAccess('admin');

// Fetch employees with their current branches
$employees_query = "
    SELECT e.id, e.name, e.position, 
           GROUP_CONCAT(DISTINCT b.name ORDER BY b.name SEPARATOR ', ') AS current_branches
    FROM employees e
    LEFT JOIN employee_branch eb ON e.id = eb.employee_id
    LEFT JOIN branches b ON eb.branch_id = b.id
    GROUP BY e.id
";
$employees_result = mysqli_query($conn, $employees_query);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="body-wrapper">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
    <a href="export_transfer_history.php" class="btn btn-success mb-3">
    <i class="fas fa-file-excel"></i> Export to Excel
</a>
        <?php while ($employee = mysqli_fetch_assoc($employees_result)) { 
            $emp_id = $employee['id'];
        ?>




            <div class="card shadow-lg mb-4 border-0" style="background: #f8f9fa; border-radius: 10px;">
                <div class="card-header bg-light text-dark border-0 shadow-sm" style="border-radius: 10px;">
                    <h5 class="mb-0">
                        <button class="btn btn-link text-dark text-decoration-none fw-bold w-100 text-start" 
                                type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapse<?php echo $emp_id; ?>">
                            <i class="fas fa-user"></i> <?php echo $employee['name']; ?> 
                            <span class="text-muted">(<?php echo $employee['position']; ?>)</span>
                        </button>
                    </h5>
                </div>
                <div id="collapse<?php echo $emp_id; ?>" class="collapse">
                    <div class="card-body bg-white shadow-sm" style="border-radius: 10px;">
                        <p><strong>Current Branches: </strong> 
                            <span class="badge bg-success"><?php echo $employee['current_branches'] ?: 'None'; ?></span>
                        </p>

                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Status</th>
                                    <th>Branch Details</th>
                                    <th>Transfer Date</th>
                                    <th>Order Number</th>
                                    <th>Attachment</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch transfer history
                                $transfers_query = "
                                    SELECT th.transfer_date, th.order_number, th.attachment_path, th.notes,
                                           fb.name AS from_branch, tb.name AS to_branch
                                    FROM transfer_history th
                                    LEFT JOIN branches fb ON th.from_branch_id = fb.id
                                    LEFT JOIN branches tb ON th.to_branch_id = tb.id
                                    WHERE th.employee_id = $emp_id
                                    ORDER BY th.transfer_date DESC
                                ";
                                $transfers_result = mysqli_query($conn, $transfers_query);

                                while ($row = mysqli_fetch_assoc($transfers_result)) {
                                    $status = "";
                                    $branch_info = "";

                                    if ($row['from_branch'] == null && $row['to_branch'] != null) {
                                        $status = '<span class="badge bg-success">Assigned ✅</span>';
                                        $branch_info = "<strong>Added: </strong> {$row['to_branch']}";
                                    } elseif ($row['from_branch'] != null && $row['to_branch'] == null) {
                                        $status = '<span class="badge bg-danger">Removed ❌</span>';
                                        $branch_info = "<strong>Removed: </strong> {$row['from_branch']}";
                                    } elseif ($row['from_branch'] != null && $row['to_branch'] != null) {
                                        $status = '<span class="badge bg-warning">Modified 🔄</span>';
                                        $branch_info = "<strong>Moved: </strong> {$row['from_branch']} → {$row['to_branch']}";
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $status; ?></td>
                                        <td><?php echo $branch_info; ?></td>
                                        <td><?php echo date('d M Y', strtotime($row['transfer_date'])); ?></td>
                                        <td><?php echo $row['order_number'] ?: 'N/A'; ?></td>
                                        <td>
                                            <?php if (!empty($row['attachment_path'])) { ?>
                                                <a href="<?php echo $row['attachment_path']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            <?php } else { echo 'No Attachment'; } ?>
                                        </td>
                                        <td><?php echo $row['notes'] ?: 'N/A'; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
