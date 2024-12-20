<?php
require 'includes/db.php';

// Debug: Print GET parameters to verify the `id` parameter
echo "<pre>";
print_r($_GET);
echo "</pre>";

// Get the employee ID from the URL
$employee_id = $_GET['id'] ?? null;

if (!$employee_id) {
    die("Error: Employee ID is required.");
}

echo "Employee ID: $employee_id"; // Debug: Print the employee ID

// Fetch all branches
$all_branches = $conn->query("SELECT * FROM branches");
if (!$all_branches) {
    die("Error fetching branches: " . $conn->error);
}

// Fetch branches the employee is currently assigned to
$current_branches_query = $conn->prepare("
    SELECT b.id, b.name 
    FROM employee_branch eb
    JOIN branches b ON eb.branch_id = b.id
    WHERE eb.employee_id = ?
");
$current_branches_query->bind_param("i", $employee_id);
$current_branches_query->execute();
$current_branches = $current_branches_query->get_result();

// Prepare a list of current branch IDs
$current_branch_ids = [];
while ($branch = $current_branches->fetch_assoc()) {
    $current_branch_ids[] = $branch['id'];
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
<form id="branch_form">
        <label for="branches">Select Branches:</label>
        <select name="branches[]" id="branches" multiple="multiple" class="form-control">
            <?php while ($branch = $all_branches->fetch_assoc()): ?>
                <option value="<?= $branch['id'] ?>" 
                    <?= in_array($branch['id'], $current_branch_ids) ? 'selected' : '' ?>>
                    <?= $branch['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>
        <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
        <br>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>

    <p id="response_message" style="color: green;"></p>


</div>


<script>
        $(document).ready(function () {
            // Initialize Select2
            $('#branches').select2({
                placeholder: "Select branches",
                allowClear: true,
                width: '100%' // Makes the dropdown fit the container
            });

            // Handle the form submission
            $('#branch_form').on('submit', function (e) {
                e.preventDefault(); // Prevent default form submission
                
                const formData = $(this).serialize();
                
                $.ajax({
                    url: 'update_branches.php',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        $('#response_message').text(response);
                    },
                    error: function () {
                        $('#response_message').text("Error updating branches.");
                    }
                });
            });
        });
    </script>

<?php
include 'includes/footer.php';
?>
    

    

    

