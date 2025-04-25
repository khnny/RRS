<?php
/**
 * Savory Haven Restaurant - Admin Panel - Edit Reservation Page
 * Handles editing of a specific reservation in the table_booking table.
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
$reservation = null;
$reservation_id = null;
$errors = [];
$success_message = '';

// --- Handle POST Request (Form Submission for Update) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reservation_id = filter_input(INPUT_POST, 'reservation_id', FILTER_SANITIZE_NUMBER_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS); // Phone might contain characters like +, -, spaces
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS);
    $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_SPECIAL_CHARS);
    $people_count = filter_input(INPUT_POST, 'people_count', FILTER_SANITIZE_NUMBER_INT);
    $table_type = filter_input(INPUT_POST, 'table_type', FILTER_SANITIZE_SPECIAL_CHARS);
    $table_location = filter_input(INPUT_POST, 'table_location', FILTER_SANITIZE_SPECIAL_CHARS);
    $table_preference = filter_input(INPUT_POST, 'table_preference', FILTER_SANITIZE_SPECIAL_CHARS);
    $special_requests_text = filter_input(INPUT_POST, 'special_requests_text', FILTER_SANITIZE_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

    // Basic Validation (add more robust validation as needed)
    if (!$reservation_id || !$name || !$email || !$phone || !$date || !$time || $people_count === false || $people_count === null || !$status) {
        $errors[] = 'Missing required fields.';
    } else {
         if ($people_count < 1) {
             $errors[] = 'Number of guests must be at least 1.';
         }
         if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             $errors[] = 'Invalid email format.';
         }
         // Basic validation for status
         if (!in_array($status, ['pending', 'confirmed', 'cancelled'])) {
             $errors[] = 'Invalid status value.';
         }
         // Basic date/time format check could be added here
    }


    if (empty($errors)) {
        $sql = "UPDATE table_booking SET name = ?, email = ?, phone = ?, date = ?, time = ?, people_count = ?, table_type = ?, table_location = ?, table_preference = ?, special_requests_text = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
             // Bind parameters: sssssissssis (string for all except people_count and id which are int)
            $stmt->bind_param("sssssissssis", $name, $email, $phone, $date, $time, $people_count, $table_type, $table_location, $table_preference, $special_requests_text, $status, $reservation_id);

            if ($stmt->execute()) {
                // Redirect back to the reservations list with a success message
                redirect_with_message('reservations', 'success', 'Reservation updated successfully!');
            } else {
                $errors[] = 'Error updating reservation: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = 'Database error preparing update statement: '  . $conn->error;
        }
    } else {
         // If there were errors on POST, we need the reservation ID to display the form again
         // This ensures the form can be redisplayed with previous values and errors
         if (isset($_POST['reservation_id'])) {
             $reservation_id = filter_var($_POST['reservation_id'], FILTER_SANITIZE_NUMBER_INT);
         } else {
             // If ID wasn't even in POST (shouldn't happen with the hidden field), try GET
              $reservation_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
         }

         // Re-populate variables for form display after errors
         $name = isset($_POST['name']) ? filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS) : '';
         $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
         $phone = isset($_POST['phone']) ? filter_var($_POST['phone'], FILTER_SANITIZE_SPECIAL_CHARS) : '';
         $date = isset($_POST['date']) ? filter_var($_POST['date'], FILTER_SANITIZE_SPECIAL_CHARS) : '';
         $time = isset($_POST['time']) ? filter_var($_POST['time'], FILTER_SANITIZE_SPECIAL_CHARS) : '';
         $people_count = isset($_POST['people_count']) ? filter_var($_POST['people_count'], FILTER_SANITIZE_NUMBER_INT) : '';
         $table_type = isset($_POST['table_type']) ? filter_var($_POST['table_type'], FILTER_SANITIZE_SPECIAL_CHARS) : '';
         $table_location = isset($_POST['table_location']) ? filter_var($_POST['table_location'], FILTER_SANITIZE_SPECIAL_CHARS) : '';
         $table_preference = isset($_POST['table_preference']) ? filter_var($_POST['table_preference'], FILTER_SANITIZE_SPECIAL_CHARS) : '';
         $special_requests_text = isset($_POST['special_requests_text']) ? filter_var($_POST['special_requests_text'], FILTER_SANITIZE_SPECIAL_CHARS) : '';
         $status = isset($_POST['status']) ? filter_var($_POST['status'], FILTER_SANITIZE_SPECIAL_CHARS) : '';
    }
}


// --- Handle GET Request (Display Form) OR Re-display Form After POST Errors ---
// Corrected condition below: Removed the extra parenthesis
if ($_SERVER["REQUEST_METHOD"] == "GET" || (!empty($errors) && $_SERVER["REQUEST_METHOD"] == "POST")) {

    // If $reservation_id is not set from POST with errors, get it from GET
    if ($reservation_id === null) {
        $reservation_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    }


    if ($reservation_id) {
        // Fetch the reservation data
        // Only fetch if $reservation is null (i.e., not a POST with errors)
        if ($reservation === null || $_SERVER["REQUEST_METHOD"] == "GET") {
            $sql = "SELECT id, table_type, name, email, phone, date, people_count, table_location, table_preference, special_requests_text, time, status, created_at FROM table_booking WHERE id = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("i", $reservation_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $reservation = $result->fetch_assoc();
                     // Assign fetched data to variables for form values if not already set by POST errors
                     if (empty($errors) || $_SERVER["REQUEST_METHOD"] == "GET") { // Only overwrite if no POST errors or if GET
                         $name = $reservation['name'];
                         $email = $reservation['email'];
                         $phone = $reservation['phone'];
                         $date = $reservation['date'];
                         $time = $reservation['time'];
                         $people_count = $reservation['people_count'];
                         $table_type = $reservation['table_type'];
                         $table_location = $reservation['table_location'];
                         $table_preference = $reservation['table_preference'];
                         $special_requests_text = $reservation['special_requests_text'];
                         $status = $reservation['status'];
                     }

                } else {
                    $errors[] = 'Reservation not found.';
                    $reservation = null; // Ensure reservation is null if not found
                }
                $stmt->close();
            } else {
                $errors[] = 'Database error preparing select statement: ' . $conn->error;
                 $reservation = null; // Ensure reservation is null on DB error
            }
        } // End if $reservation is null or GET request

    } else {
        $errors[] = 'No reservation ID specified for editing.';
         $reservation = null; // Ensure reservation is null if no ID
    }
}

// Close the database connection before rendering HTML
// Only close if $conn is still open (i.e., not already closed by a redirect)
if ($conn && $conn->ping()) {
    $conn->close();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reservation - Savory Haven Admin</title>
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
                <h1 class="admin-title mb-4">Edit Reservation</h1>

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

                <?php if ($reservation || (!empty($errors) && $_SERVER["REQUEST_METHOD"] == "POST")): // Display form if table data was fetched or if there were POST errors ?>
                     <?php if ($reservation_id): // Only attempt to show form if we have an ID ?>
                         <div class="card">
                             <div class="card-header admin-header">
                                 <h5 class="mb-0">Editing Reservation #<?php echo htmlspecialchars($reservation_id); ?></h5>
                             </div>
                             <div class="card-body">
                                 <form action="edit_reservation.php?id=<?php echo htmlspecialchars($reservation_id); ?>" method="post">
                                     <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation_id); ?>">

                                     <div class="mb-3">
                                         <label for="name" class="form-label">Name</label>
                                         <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                                     </div>

                                     <div class="mb-3">
                                         <label for="email" class="form-label">Email</label>
                                         <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                     </div>

                                     <div class="mb-3">
                                         <label for="phone" class="form-label">Phone</label>
                                         <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" required>
                                     </div>

                                     <div class="row">
                                          <div class="col-md-6 mb-3">
                                              <label for="date" class="form-label">Date</label>
                                              <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($date ?? ''); ?>" required>
                                          </div>
                                           <div class="col-md-6 mb-3">
                                              <label for="time" class="form-label">Time</label>
                                              <input type="time" class="form-control" id="time" name="time" value="<?php echo htmlspecialchars($time ?? ''); ?>" required>
                                          </div>
                                     </div>

                                      <div class="mb-3">
                                         <label for="people_count" class="form-label">Guests</label>
                                         <input type="number" class="form-control" id="people_count" name="people_count" value="<?php echo htmlspecialchars($people_count ?? ''); ?>" required min="1">
                                      </div>

                                      <div class="mb-3">
                                          <label for="table_type" class="form-label">Table Type</label>
                                           <input type="text" class="form-control" id="table_type" name="table_type" value="<?php echo htmlspecialchars($table_type ?? ''); ?>" placeholder="e.g., Standard, Booth">
                                      </div>

                                      <div class="mb-3">
                                          <label for="table_location" class="form-label">Table Location</label>
                                           <input type="text" class="form-control" id="table_location" name="table_location" value="<?php echo htmlspecialchars($table_location ?? ''); ?>" placeholder="e.g., Window, Patio">
                                      </div>

                                      <div class="mb-3">
                                          <label for="table_preference" class="form-label">Table Preference</label>
                                           <input type="text" class="form-control" id="table_preference" name="table_preference" value="<?php echo htmlspecialchars($table_preference ?? ''); ?>" placeholder="e.g., Quiet area">
                                      </div>

                                      <div class="mb-3">
                                          <label for="special_requests_text" class="form-label">Special Requests</label>
                                          <textarea class="form-control" id="special_requests_text" name="special_requests_text" rows="3"><?php echo htmlspecialchars($special_requests_text ?? ''); ?></textarea>
                                      </div>

                                     <div class="mb-3">
                                         <label for="status" class="form-label">Status</label>
                                         <select class="form-select" id="status" name="status" required>
                                             <option value="pending" <?php echo ((isset($status) && $status === 'pending') || (!isset($status) && isset($reservation) && $reservation['status'] === 'pending')) ? 'selected' : ''; ?>>Pending</option>
                                             <option value="confirmed" <?php echo ((isset($status) && $status === 'confirmed') || (!isset($status) && isset($reservation) && $reservation['status'] === 'confirmed')) ? 'selected' : ''; ?>>Confirmed</option>
                                             <option value="cancelled" <?php echo ((isset($status) && $status === 'cancelled') || (!isset($status) && isset($reservation) && $reservation['status'] === 'cancelled')) ? 'selected' : ''; ?>>Cancelled</option>
                                         </select>
                                     </div>

                                     <button type="submit" class="btn btn-primary me-2"><i class="fas fa-save me-1"></i> Save Changes</button>
                                     <a href="admin.php?page=reservations" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Reservations</a>
                                 </form>
                             </div>
                         </div>
                      <?php endif; ?>

                <?php else: // No reservation data and no POST errors ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Please select a reservation to edit from the reservations list.
                    </div>
                    <a href="admin.php?page=reservations" class="btn btn-primary"><i class="fas fa-arrow-left me-1"></i> Back to Reservations</a>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>