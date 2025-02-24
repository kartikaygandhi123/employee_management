<?php
require 'includes/access_control.php';
require 'includes/db.php';
checkAccess('admin');

// Fetch branches with employee transfer history
$branches_query = "
    SELECT b.id, b.name,
           COUNT(DISTINCT th.employee_id) AS employee_count
    FROM branches b
    LEFT JOIN transfer_history th ON b.id = th.from_branch_id OR b.id = th.to_branch_id
    GROUP BY b.id
    ORDER BY b.name
";
$branches_result = mysqli_query($conn, $branches_query);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="body-wrapper">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid">
        <?php while ($branch = mysqli_fetch_assoc($branches_result)) { 
            $branch_id = $branch['id'];
        ?>
            <div class="card shadow-lg mb-4 border-0" style="background: #f8f9fa; border-radius: 10px;">
                <div class="card-header bg-light text-dark border-0 shadow-sm" style="border-radius: 10px;">
                    <h5 class="mb-0">
                        <button class="btn btn-link text-dark text-decoration-none fw-bold w-100 text-start" 
                                type="button" data-bs-toggle="collapse" 
                                data-bs-target="#collapse<?php echo $branch_id; ?>">
                            <i class="fas fa-building"></i> <?php echo $branch['name']; ?>
                            <span class="text-muted">(<?php echo $branch['employee_count']; ?> Employees)</span>
                        </button>
                    </h5>
                </div>
                <div id="collapse<?php echo $branch_id; ?>" class="collapse">
                    <div class="card-body bg-white shadow-sm" style="border-radius: 10px;">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Employee Name</th>
                                    <th>Status</th>
                                    <th>Transfer Date</th>
                                    <th>Order Number</th>
                                    <th>Attachment</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch transfer history for the branch
                                $transfers_query = "
                                    SELECT e.name AS employee_name, th.transfer_date, th.order_number, 
                                           th.attachment_path, th.notes, 
                                           fb.name AS from_branch, tb.name AS to_branch
                                    FROM transfer_history th
                                    LEFT JOIN employees e ON th.employee_id = e.id
                                    LEFT JOIN branches fb ON th.from_branch_id = fb.id
                                    LEFT JOIN branches tb ON th.to_branch_id = tb.id
                                    WHERE th.from_branch_id = $branch_id OR th.to_branch_id = $branch_id
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
                                        $status = '<span class="badge bg-warning">Moved 🔄</span>';
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $row['employee_name']; ?></td>
                                        <td><?php echo $status; ?></td>
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
