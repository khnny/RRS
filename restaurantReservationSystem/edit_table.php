<?php
/**
 * Savory Haven Restaurant - Admin Panel - Edit Table Page
 * Handles editing of a specific table in the table_mapping table.
 */
require_once 'config.php'; // Include configuration

// Check if admin is authenticated
if (!is_logged_in()) {
    header('Location: admin.php');
    exit;
}

// Helper function to redirect with a status message (if not already defined in config)
if (!function_exists('redirect_with_message')) {
    function redirect_with_message(string $page, string $status, string $message): void {
        header("Location: admin.php?page=$page&status=$status&message=" . urlencode($message));
        exit;
    }

}

$table = null;
$table_id = null;
$errors = [];
$success_message = '';

// --- Handle POST Request (Form Submission for Update) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $table_id = filter_input(INPUT_POST, 'table_id', FILTER_SANITIZE_NUMBER_INT);
    $physical_table_id = filter_input(INPUT_POST, 'physical_table_id', FILTER_SANITIZE_NUMBER_INT);
    $capacity = filter_input(INPUT_POST, 'capacity', FILTER_SANITIZE_NUMBER_INT);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_SPECIAL_CHARS);
    $availability = filter_input(INPUT_POST, 'availability', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($table_id && $physical_table_id !== false && $physical_table_id !== null && $capacity !== false && $capacity !== null && $location && $availability) {

        // Basic validation
        if (!in_array($availability, ['available', 'taken', 'unavailable'])) { 
            $errors[] = 'Invalid availability value.';
        }
         if ($capacity < 1) {
             $errors[] = 'Capacity must be at least 1.';
         }
         if ($physical_table_id < 1) {
             $errors[] = 'Physical Table ID must be at least 1.';
         }


        if (empty($errors)) {
            // Use physical_table_id as the column name
            $sql = "UPDATE table_mapping SET physical_table_id = ?, capacity = ?, location = ?, availability = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                 // Bind parameters: i (int), i (int), s (string), s (string), i (int)
                $stmt->bind_param("iissi", $physical_table_id, $capacity, $location, $availability, $table_id);

                if ($stmt->execute()) {
                    // Redirect back to the tables list with a success message
                    redirect_with_message('tables', 'success', 'Table updated successfully!');
                } else {
                     // Check for duplicate entry error
                     if ($conn->errno == 1062) { // MySQL duplicate entry error code
                          $errors[] = 'Error updating table: Physical Table ID already exists.';
                     } else {
                         $errors[] = 'Error updating table: ' . $stmt->error;
                     }
                }
                $stmt->close();
            } else {
                $errors[] = 'Database error preparing update statement: ' . $conn->error;
            }
        }
    } else {
        $errors[] = 'Missing required fields or invalid table ID.';
         // If table_id is missing from POST, try to get it from GET for display
         $table_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    }
}

// --- Handle GET Request (Display Form) ---
if ($_SERVER["REQUEST_METHOD"] == "GET" || (!empty($errors) && $_SERVER["REQUEST_METHOD"] == "POST")) {
     // Get table ID from URL if it's a GET request or if there were errors on POST
    if ($table_id === null) {
        $table_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    }


    if ($table_id) {
        // Fetch the table data using physical_table_id as the column name
        $sql = "SELECT id, physical_table_id, capacity, location, availability FROM table_mapping WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $table_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $table = $result->fetch_assoc();
                 // Assign fetched data to variables for form values if not already set by POST errors
                 if (empty($errors)) {
                     $physical_table_id = $table['physical_table_id']; // Use correct column name
                     $capacity = $table['capacity'];
                     $location = $table['location'];
                     $availability = $table['availability'];
                 }

            } else {
                $errors[] = 'Table not found.';
            }
            $stmt->close();
        } else {
             $errors[] = 'Database error preparing select statement: ' . $conn->error;
        }
    } else {
        $errors[] = 'No table ID specified for editing.';
    }
}

// Close the database connection before rendering HTML
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Table - Savory Haven Admin</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f5f5;
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .admin-title {
            font-family: 'Playfair Display', serif;
        }
        .card {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
         .admin-header {
             background-color: #9c3d26;
             color: white;
         }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="admin-title mb-4">Edit Table</h1>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php foreach ($errors as $error) { echo $error . '<br>'; } ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($table): // Display form only if table data was fetched ?>
                    <div class="card">
                        <div class="card-header admin-header">
                            <h5 class="mb-0">Editing Table #<?php echo htmlspecialchars($table['id']); ?> (Physical ID: <?php echo htmlspecialchars($table['physical_table_id']); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <form action="edit_table.php?id=<?php echo htmlspecialchars($table['id']); ?>" method="post">
                                <input type="hidden" name="table_id" value="<?php echo htmlspecialchars($table['id']); ?>">

                                <div class="mb-3">
                                    <label for="physical_table_id" class="form-label">Physical Table ID</label>
                                    <input type="number" class="form-control" id="physical_table_id" name="physical_table_id" value="<?php echo htmlspecialchars($physical_table_id); ?>" required min="1">
                                </div>

                                <div class="mb-3">
                                    <label for="capacity" class="form-label">Capacity</label>
                                    <input type="number" class="form-control" id="capacity" name="capacity" value="<?php echo htmlspecialchars($capacity); ?>" required min="1">
                                </div>

                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>" required>
                                </div>

                                <div class="mb-3">
    <label for="availability" class="form-label">Availability</label>
    <select class="form-select" id="availability" name="availability" required>
        <option value="available" <?php echo ($availability === 'available') ? 'selected' : ''; ?>>Available</option>
        <option value="taken" <?php echo ($availability === 'taken') ? 'selected' : ''; ?>>Taken</option> <option value="unavailable" <?php echo ($availability === 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
    </select>
</div>

                                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-save me-1"></i> Save Changes</button>
                                <a href="admin.php?page=tables" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Tables</a>
                            </form>
                        </div>
                    </div>
                <?php elseif (!$table && empty($errors)): ?>
                     <div class="alert alert-info">
                          <i class="fas fa-info-circle me-2"></i> Please select a table to edit from the tables management page.
                      </div>
                     <a href="admin.php?page=tables" class="btn btn-primary"><i class="fas fa-arrow-left me-1"></i> Back to Tables</a>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>