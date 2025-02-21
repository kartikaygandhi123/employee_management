<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'] ?? null;
    $selected_branches = $_POST['branches'] ?? []; // Selected branches in the multi-select
    $order_number = $_POST['order_number'] ?? "N/A"; // Default if empty
    $attachment_path = null;

    // Check if employee_id is valid
    if (!$employee_id) {
        die("Error: Employee ID is missing.");
    }

    // Handle file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/orders/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . '_' . basename($_FILES['attachment']['name']);
        $file_tmp_path = $_FILES['attachment']['tmp_name'];
        $attachment_path = $upload_dir . $file_name;

        if (!move_uploaded_file($file_tmp_path, $attachment_path)) {
            die('Error: File upload failed.');
        }
    } else {
        $attachment_path = "N/A"; // Default value if no file uploaded
    }

    // Fetch current branch assignments for the employee
    $current_branches_query = $conn->prepare("SELECT branch_id FROM employee_branch WHERE employee_id = ?");
    $current_branches_query->bind_param("i", $employee_id);
    $current_branches_query->execute();
    $current_branches_result = $current_branches_query->get_result();

    $current_branch_ids = [];
    while ($row = $current_branches_result->fetch_assoc()) {
        $current_branch_ids[] = $row['branch_id'];
    }

    // Calculate branches to add and remove
    $branches_to_add = array_diff($selected_branches, $current_branch_ids);
    $branches_to_remove = array_diff($current_branch_ids, $selected_branches);

    // Assign new branches and log transfer history
    foreach ($branches_to_add as $to_branch_id) {
        // Find the last assigned branch (if any)
        $from_branch_id = null;
        $fetch_prev_branch = $conn->prepare("SELECT branch_id FROM employee_branch WHERE employee_id = ? ORDER BY assigned_at DESC LIMIT 1");
        $fetch_prev_branch->bind_param("i", $employee_id);
        $fetch_prev_branch->execute();
        $fetch_prev_branch->bind_result($prev_branch);
        
        if ($fetch_prev_branch->fetch()) {
            $from_branch_id = $prev_branch;
        }
        $fetch_prev_branch->close();

        // Insert into employee_branch table
        $stmt = $conn->prepare("INSERT INTO employee_branch (employee_id, branch_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $employee_id, $to_branch_id);
        $stmt->execute();

        // Log the assignment in transfer_history
        $notes = "Assigned to branch";
        $log_stmt = $conn->prepare("
            INSERT INTO transfer_history (employee_id, from_branch_id, to_branch_id, order_number, attachment_path, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $log_stmt->bind_param("iiisss", $employee_id, $from_branch_id, $to_branch_id, $order_number, $attachment_path, $notes);
        $log_stmt->execute();
    }

    // Remove unselected branches and log transfer history
    foreach ($branches_to_remove as $from_branch_id) {
        // Remove from employee_branch table
        $stmt = $conn->prepare("DELETE FROM employee_branch WHERE employee_id = ? AND branch_id = ?");
        $stmt->bind_param("ii", $employee_id, $from_branch_id);
        $stmt->execute();

        // Log the removal in transfer_history
        $notes = "Removed from branch";
        $log_stmt = $conn->prepare("
            INSERT INTO transfer_history (employee_id, from_branch_id, to_branch_id, order_number, attachment_path, notes)
            VALUES (?, ?, NULL, ?, ?, ?)
        ");
        $log_stmt->bind_param("iisss", $employee_id, $from_branch_id, $order_number, $attachment_path, $notes);
        $log_stmt->execute();
    }

    // Log continued association for unchanged branches
    foreach ($current_branch_ids as $branch_id) {
        if (in_array($branch_id, $selected_branches)) {
            $notes = "Continued association with branch";
            $log_stmt = $conn->prepare("
                INSERT INTO transfer_history (employee_id, from_branch_id, to_branch_id, order_number, attachment_path, notes)
                VALUES (?, NULL, ?, ?, ?, ?)
            ");
            $log_stmt->bind_param("iisss", $employee_id, $branch_id, $order_number, $attachment_path, $notes);
            $log_stmt->execute();
        }
    }

    echo "Branches updated successfully with order number, attachment, and transfer notes logged!";
    exit();
}
?>
