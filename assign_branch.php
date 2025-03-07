<?php
require 'includes/access_control.php';
require 'includes/db.php';
checkAccess('admin');

$employee_id = $_GET['id'] ?? null;

if (!$employee_id || !is_numeric($employee_id)) {
    die("Error: Valid Employee ID is required.");
}

// Fetch all branches
$all_branches = $conn->query("SELECT id, name FROM branches");
if (!$all_branches) {
    die("Error fetching branches: " . $conn->error);
}

// Fetch assigned branches
$current_branches_query = $conn->prepare("
    SELECT b.id FROM employee_branch eb
    JOIN branches b ON eb.branch_id = b.id
    WHERE eb.employee_id = ?
");
$current_branches_query->bind_param("i", $employee_id);
$current_branches_query->execute();
$current_branches = $current_branches_query->get_result();

$current_branch_ids = [];
while ($branch = $current_branches->fetch_assoc()) {
    $current_branch_ids[] = $branch['id'];
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="body-wrapper">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid">
        <h4>Assign Branches to Employee</h4>
        <form id="assign_branch_form" enctype="multipart/form-data">
            <label for="branches">Select Branches:</label>
            <select name="branches[]" id="branches" multiple class="form-control">
                <?php while ($branch = $all_branches->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($branch['id']) ?>"
                        <?= in_array($branch['id'], $current_branch_ids) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($branch['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="order_number">Order Number:</label>
            <input type="text" id="order_number" name="order_number" class="form-control" required>

            <label for="order_date">Order Date:</label>
            <input type="date" id="assigned_at" name="assigned_at" class="form-control" required>

            <label for="attachment">Upload Order (Optional):</label>
            <input type="file" id="attachment" name="attachment" class="form-control" accept="image/*,application/pdf">

            <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee_id) ?>">
            <br>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>

        <p id="response_message" style="color: green;"></p>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#branches').select2({
            placeholder: "Select branches",
            allowClear: true,
            width: '100%'
        });

        $('#assign_branch_form').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: 'update_branches.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    $('#response_message').html(response).css("color", "green");
                },
                error: function () {
                    $('#response_message').html("Error updating branches.").css("color", "red");
                }
            });
        });
    });
</script>
