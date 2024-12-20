<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $selected_branches = $_POST['branches'] ?? []; // Branches selected in the multi-select

    // Fetch current branch assignments for the employee
    $current_branches_query = $conn->prepare("
        SELECT branch_id, assigned_at FROM employee_branch WHERE employee_id = ?
    ");
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

    // Add new branches and log to transfer history
    foreach ($branches_to_add as $branch_id) {
        // Insert into employee_branch table
        $stmt = $conn->prepare("INSERT INTO employee_branch (employee_id, branch_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $employee_id, $branch_id);
        $stmt->execute();

        // Log the assignment to transfer_history
        $log_stmt = $conn->prepare("
            INSERT INTO transfer_history (employee_id, from_branch_id, to_branch_id, notes)
            VALUES (?, NULL, ?, 'Assigned to branch')
        ");
        $log_stmt->bind_param("ii", $employee_id, $branch_id);
        $log_stmt->execute();
    }

    // Remove unselected branches and log to transfer history
    foreach ($branches_to_remove as $branch_id) {
        // Remove from employee_branch table
        $stmt = $conn->prepare("DELETE FROM employee_branch WHERE employee_id = ? AND branch_id = ?");
        $stmt->bind_param("ii", $employee_id, $branch_id);
        $stmt->execute();

        // Log the removal to transfer_history
        $log_stmt = $conn->prepare("
            INSERT INTO transfer_history (employee_id, from_branch_id, to_branch_id, notes)
            VALUES (?, ?, NULL, 'Removed from branch')
        ");
        $log_stmt->bind_param("ii", $employee_id, $branch_id);
        $log_stmt->execute();
    }

    // Check if the employee remains in existing branches
    foreach ($current_branch_ids as $branch_id) {
        if (in_array($branch_id, $selected_branches)) {
            // Log that the employee is still working in the branch
            $log_stmt = $conn->prepare("
                INSERT INTO transfer_history (employee_id, from_branch_id, to_branch_id, notes)
                VALUES (?, NULL, ?, 'Continued association with branch')
            ");
            $log_stmt->bind_param("ii", $employee_id, $branch_id);
            $log_stmt->execute();
        }
    }

    echo "Branches updated successfully and transfer history logged!";
    exit();
}
