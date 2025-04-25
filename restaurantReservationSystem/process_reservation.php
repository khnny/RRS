<?php
/**
 * Savory Haven Restaurant - Reservation Processing
 * This file processes reservation form submissions
 */
include 'includes/connection.php'; // Make sure this file establishes the $conn connection

// Set header for JSON response
header('Content-Type: application/json');

// Check if form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS); // Assuming date is sent as YYYY-MM-DD string
    $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_SPECIAL_CHARS);
    $guests = filter_input(INPUT_POST, 'guests', FILTER_SANITIZE_NUMBER_INT);

    // --- Correctly get the Special Requests message ---
    $special_requests_text = filter_input(INPUT_POST, 'special', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($special_requests_text === NULL || $special_requests_text === false) {
        $special_requests_text = ""; // Treat missing/empty input as an empty string
    }
    // ---------------------------------------------------

    // --- Get selected physical table data from hidden inputs ---
    $selected_physical_table_id = filter_input(INPUT_POST, 'table_id', FILTER_SANITIZE_NUMBER_INT);
    // --------------------------------------------------------------------------------------

    // Array to hold validation errors
    $booking_errors = [];

    // Validate required fields
    if (empty($name) || empty($email) || empty($phone) || empty($date) || empty($time) || empty($guests) || empty($selected_physical_table_id)) {
        $booking_errors[] = 'Please fill in all required fields and select an available table.';
    }

    // Validate email format
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) { // Check if email is not empty before validating format
        $booking_errors[] = 'Please enter a valid email address.';
    }

    // Validate number of guests
     if (!empty($guests) && $guests < 1) {
         $booking_errors[] = 'Number of guests must be at least 1.';
     }

    // --- SERVER-SIDE DATE VALIDATION ---
    $today = date('Y-m-d'); // Get today's date in YYYY-MM-DD format

    // Check if the submitted date is valid and not in the past
    if (!empty($date)) {
        // Check if the date format is valid (simple check, adjust as needed)
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
            $booking_errors[] = "Invalid date format.";
        } else {
             // Compare the submitted date with today's date
             if ($date < $today) {
                 $booking_errors[] = "You cannot book a reservation for a past date.";
             }
             // You could add validation here to prevent booking too far in the future if needed
        }
    }
    // --- END SERVER-SIDE DATE VALIDATION ---


    // If there are validation errors, return a JSON error response
    if (!empty($booking_errors)) {
        $response = [
            'success' => false,
            'message' => implode('<br>', $booking_errors) // Combine errors into a single message
        ];
        echo json_encode($response);
        exit;
    }

    // --- Continue with booking process only if there are no validation errors ---

    // Generate a confirmation code (still generated, consider storing it in the DB if you add a column)
    $confirmation_code = generateConfirmationCode(); // Make sure this function is defined

    // Default status for a new reservation
    $status = 'pending';

    // Get current timestamp for created_at
    $created_at = date('Y-m-d H:i:s');

    // Prepare SQL statement for inserting into database
    // Ensure the column list matches your table_booking table columns exactly.
    // This query expects 13 values to be bound by the ? placeholders.
    $sql = "INSERT INTO table_booking (physical_table_id, name, email, phone, date, people_count, special_requests_text, time, status, created_at, table_type, table_location, table_preference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Use prepared statements
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
         // Handle prepare error
         $response = [
             'success' => false,
             'message' => 'Database error preparing insert statement.'
         ];
          error_log("Database error preparing insert statement: " . $conn->error);
         echo json_encode($response);
         $conn->close();
         exit;
    }


    // --- Get table details from table_mapping based on selected physical_table_id ---
    // This is safer than relying on hidden inputs for type/location/pref
      $table_details_sql = "SELECT capacity, location, table_type, table_preference FROM table_mapping WHERE id = ?";
      $table_details_stmt = $conn->prepare($table_details_sql);

      if (!$table_details_stmt) {
           // Handle prepare error
          $response = [
              'success' => false,
              'message' => 'Database error preparing table details statement.'
          ];
           error_log("Database error preparing table details statement: " . $conn->error);
          echo json_encode($response);
          $stmt->close(); // Close the first prepared statement
          $conn->close();
          exit;
      }

      $table_details_stmt->bind_param("i", $selected_physical_table_id);
      $table_details_stmt->execute();
      $table_details_result = $table_details_stmt->get_result();
      $table_details = $table_details_result->fetch_assoc();
      $table_details_stmt->close();

      if (!$table_details) {
          // Should not happen if availability check works and ID is valid, but good to validate
          $response = [
                'success' => false,
                'message' => 'Invalid table selection. Please select a valid table.'
            ];
          echo json_encode($response);
          $stmt->close(); // Close the first prepared statement
          $conn->close();
          exit;
      }

      // Use details fetched from the database based on the selected ID
      $table_type_to_save = $table_details['table_type']; // Use column name from table_mapping
      $table_location_to_save = $table_details['location']; // Use column name from table_mapping
      $table_preference_to_save = $table_details['table_preference']; // Use column name from table_mapping
      // Decided to use guests from the form for people_count, not table capacity
      // $people_count_to_save = $table_details['capacity'];


    // --- Bind Parameters for INSERT ---
    // The bind types string 'issssssssssss' has 13 characters.
    // We need to provide 13 variables in the correct order and type.
    // physical_table_id (i), name (s), email (s), phone (s), date (s), people_count (s), message (s), time (s), status (s), created_at (s), table_type (s), table_location (s), table_preference (s)
    // (Based on your table_booking schema screenshot where many columns are VARCHAR)
    $stmt->bind_param("issssssssssss",
        $selected_physical_table_id,
        $name,
        $email,
        $phone,
        $date, // The validated date
        $guests, // The validated guests count
        $special_requests_text,
        $time, // The validated time
        $status,
        $created_at,
        $table_type_to_save, // Data from table_mapping
        $table_location_to_save, // Data from table_mapping
        $table_preference_to_save // Data from table_mapping
    );

    // Execute the statement
    if ($stmt->execute()) {
        // Database insertion successful
        $last_inserted_id = $stmt->insert_id; // Get the ID of the new reservation

        // Optional: Log to file
        $log_entry = date('Y-m-d H:i:s') . " - New Reservation ID: " . $last_inserted_id . ", Confirmation: $confirmation_code, Physical Table ID: $selected_physical_table_id\n";
        if (!file_exists('reservations_logs')) { // Changed directory name slightly to avoid potential conflicts
            mkdir('reservations_logs', 0755, true);
        }
        file_put_contents('reservations_logs/reservations.log', $log_entry, FILE_APPEND);


        $response = [
            'success' => true,
            'message' => 'Reservation confirmed! Your confirmation code is ' . $confirmation_code, // Display confirmation code
            'data' => [
                'confirmation_code' => $confirmation_code, // Still show code to user
                'name' => $name,
                'date' => $date,
                'time' => $time,
                'guests' => $guests,
                'reservation_id' => $last_inserted_id,
                'physical_table_id' => $selected_physical_table_id, // Return physical table ID
                'table_type' => $table_type_to_save // Return table type
            ]
        ];
          // Consider sending a confirmation email here in a real application
    } else {
        // Database insertion failed
        $response = [
            'success' => false,
            'message' => 'Error processing reservation. Please try again later.' // More generic error for user
        ];
          error_log("Database error inserting reservation: " . $stmt->error); // Log the specific MySQL error
    }

    $stmt->close();
    $conn->close(); // Close database connection

    // The header('Content-Type: application/json'); was moved to the top
    echo json_encode($response);
    exit;

} else {
     // Not a POST request
     http_response_code(405); // Method Not Allowed
     echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
     exit;
}


/**
 * Generate a random confirmation code
 * (This code is generated but NOT stored in the DB based on your table structure screenshot)
 * @return string
 */
function generateConfirmationCode() {
    $charset = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';

    for ($i = 0; $i < 8; $i++) {
        $random_index = rand(0, strlen($charset) - 1);
        $code .= $charset[$random_index];
    }

    return $code;
}
?>