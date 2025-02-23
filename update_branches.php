<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'] ?? null;
    $selected_branches = $_POST['branches'] ?? [];
    $order_number = trim($_POST['order_number'] ?? "N/A");
    $attachment_path = null;

    if (!$employee_id || !is_numeric($employee_id)) {
        die("Error: Valid Employee ID is required.");
    }

    // Handle file upload
    if (!empty($_FILES['attachment']['name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/orders/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $_FILES['attachment']['name']);
        $file_tmp_path = $_FILES['attachment']['tmp_name'];
        $attachment_path = $upload_dir . $file_name;

        if (!move_uploaded_file($file_tmp_path, $attachment_path)) {
            die('Error: File upload failed.');
        }
    } else {
        $attachment_path = "N/A";
    }

    // Fetch current branch assignments
    $current_branches_query = $conn->prepare("SELECT branch_id FROM employee_branch WHERE employee_id = ?");
    $current_branches_query->bind_param("i", $employee_id);
    $current_branches_query->execute();
    $current_branches_result = $current_branches_query->get_result();

    $current_branch_ids = [];
    while ($row = $current_branches_result->fetch_assoc()) {
        $current_branch_ids[] = $row['branch_id'];
    }

    // Determine changes
    $branches_to_add = array_diff($selected_branches, $current_branch_ids);
    $branches_to_remove = array_diff($current_branch_ids, $selected_branches);

    // Add new branches
    foreach ($branches_to_add as $to_branch_id) {
        $stmt = $conn->prepare("INSERT INTO employee_branch (employee_id, branch_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $employee_id, $to_branch_id);
        $stmt->execute();

        // Log the assignment
        $notes = "Assigned to branch";
        $log_stmt = $conn->prepare("
            INSERT INTO transfer_history (employee_id, from_branch_id, to_branch_id, order_number, attachment_path, notes)
            VALUES (?, NULL, ?, ?, ?, ?)
        ");
        $log_stmt->bind_param("iisss", $employee_id, $to_branch_id, $order_number, $attachment_path, $notes);
        $log_stmt->execute();
    }

    // Remove unselected branches
    foreach ($branches_to_remove as $from_branch_id) {
        $stmt = $conn->prepare("DELETE FROM employee_branch WHERE employee_id = ? AND branch_id = ?");
        $stmt->bind_param("ii", $employee_id, $from_branch_id);
        $stmt->execute();

        // Log the removal
        $notes = "Removed from branch";
        $log_stmt = $conn->prepare("
            INSERT INTO transfer_history (employee_id, from_branch_id, to_branch_id, order_number, attachment_path, notes)
            VALUES (?, ?, NULL, ?, ?, ?)
        ");
        $log_stmt->bind_param("iisss", $employee_id, $from_branch_id, $order_number, $attachment_path, $notes);
        $log_stmt->execute();
    }

    echo "Branches updated successfully with order number, attachment, and transfer notes logged!";
}
?>
