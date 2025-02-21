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
        <div class="accordion" id="transferAccordion">
            <?php while ($employee = mysqli_fetch_assoc($employees_result)) { 
                $emp_id = $employee['id'];
            ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $emp_id; ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $emp_id; ?>" aria-expanded="false">
                            <strong><?php echo $employee['name']; ?></strong> (<?php echo $employee['position']; ?>)
                        </button>
                    </h2>
                    <div id="collapse<?php echo $emp_id; ?>" class="accordion-collapse collapse" data-bs-parent="#transferAccordion">
                        <div class="accordion-body">
                            <p><strong>Current Branches: </strong> 
                                <span class="badge bg-primary"><?php echo $employee['current_branches'] ?: 'None'; ?></span>
                            </p>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Branch</th>
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
                                        if ($row['from_branch'] == null && $row['to_branch'] != null) {
                                            $status = '<span class="badge bg-success">Assigned ✅</span>';
                                        } elseif ($row['from_branch'] != null && $row['to_branch'] == null) {
                                            $status = '<span class="badge bg-danger">Removed ❌</span>';
                                        } elseif ($row['from_branch'] != null && $row['to_branch'] != null) {
                                            $status = '<span class="badge bg-warning">Modified 🔄</span>';
                                        }
                                    ?>
                                        <tr>
                                            <td><?php echo $status; ?></td>
                                            <td>
                                                <?php 
                                                if ($row['from_branch'] && $row['to_branch']) {
                                                    echo "<strong>{$row['from_branch']}</strong> → <strong>{$row['to_branch']}</strong>";
                                                } else {
                                                    echo "<strong>{$row['to_branch']}</strong>";
                                                }
                                                ?>
                                            </td>
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
</div>

<?php include 'includes/footer.php'; ?>
