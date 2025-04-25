<?php
/**
 * Savory Haven Restaurant - Get Available Tables
 * This file provides a list of available tables for a given date, time, and guests.
 */

// --- Database Connection (Replace with your connection details or include your connection.php) ---
$host = "localhost";
$username = "root";
$password = ""; // Your database password
$db_name = "rrs"; // Your database name

// Create connection
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    // In a real application, log the error and return a generic message
    error_log("Database Connection failed: " . $conn->connect_error);
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}
// --- End Database Connection ---


header('Content-Type: application/json'); // Indicate that the response is JSON

// Check if the request method is POST and required parameters are set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['date']) && isset($_POST['time']) && isset($_POST['guests'])) {

    // Sanitize input
    $selected_date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS);
    $selected_time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_SPECIAL_CHARS); // Assuming a format like 'HH:MM' or 'HH:MM AM/PM' (VARCHAR in your DB)
    $number_of_guests = filter_input(INPUT_POST, 'guests', FILTER_SANITIZE_NUMBER_INT);

    // Basic validation
    if (empty($selected_date) || empty($selected_time) || empty($number_of_guests) || $number_of_guests <= 0) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Invalid input parameters.']);
        $conn->close();
        exit;
    }

    // --- Availability Check Logic ---

    // This logic queries your 'table_mapping' table and checks against
    // reservations in 'table_booking' using the 'physical_table_id'.
    //
    // IMPORTANT: The time comparison here assumes an EXACT match of date AND time.
    // For real-world availability (checking for overlapping reservations),
    // you NEED to implement more robust time range comparison logic in the SQL query.
    // Storing date and time in appropriate SQL types (DATE, TIME, DATETIME) is highly recommended.

    try {
        // --- Step 1: Find the physical_table_ids that are booked for the selected date and time slot ---
        // Using simplified exact date and time match for demonstration.
        // *** REPLACE THIS WITH YOUR TIME OVERLAP LOGIC IF NEEDED ***
        $booked_physical_tables_sql = "
            SELECT DISTINCT physical_table_id
            FROM table_booking
            WHERE date = ?
            AND time = ? -- *** THIS IS THE SIMPLIFIED TIME CHECK ***
            AND status IN ('pending', 'confirmed') -- Only consider active bookings
            AND physical_table_id IS NOT NULL -- Ensure a physical table was assigned
        ";

        $booked_stmt = $conn->prepare($booked_physical_tables_sql);

        // Bind parameters for the booked tables query (date and time)
        $booked_stmt->bind_param("ss", $selected_date, $selected_time);

        $booked_stmt->execute();
        $booked_result = $booked_stmt->get_result();

        $booked_physical_table_ids = [];
        while($row = $booked_result->fetch_assoc()){
            $booked_physical_table_ids[] = $row['physical_table_id'];
        }
        $booked_stmt->close();

        // --- Step 2: Query the 'table_mapping' table for tables that meet capacity and are NOT booked ---
        // Select all details you need for the frontend visualization
        $available_tables_query = "
            SELECT id, capacity, location, availability -- Include 'availability' if you use it for general status
            FROM table_mapping
            WHERE capacity >= ? -- Check if table capacity is sufficient for the number of guests
        ";

        // Add condition to exclude booked tables if any were found
        if (!empty($booked_physical_table_ids)) {
             // Create placeholders for the IN clause (?, ?, ?)
             $placeholders = implode(',', array_fill(0, count($booked_physical_table_ids), '?'));
             $available_tables_query .= " AND id NOT IN ($placeholders)"; // Exclude tables whose IDs are in the booked list
        }

        // Optional: Add ordering (e.g., by capacity, location)
        $available_tables_query .= " ORDER BY capacity, location";


        $available_stmt = $conn->prepare($available_tables_query);

        // Prepare parameters for binding: first the guest count (integer), then the booked IDs (all integers)
        // We need to pass parameters by reference for bind_param with a dynamic array
        $bind_types = 'i' . str_repeat('i', count($booked_physical_table_ids)); // 'i' for integer (guests), then 'i' for each booked ID
        $bind_params = array_merge([$bind_types, $number_of_guests], $booked_physical_table_ids);

        // Use call_user_func_array with array_ref to bind the parameters dynamically
        // This is needed because the number of booked_physical_table_ids is dynamic
        call_user_func_array([$available_stmt, 'bind_param'], array_ref($bind_params));


        $available_stmt->execute();
        $available_result = $available_stmt->get_result();

        $available_tables = [];
        while($row = $available_result->fetch_assoc()) {
            // Add any other details you want to send to the frontend
            $available_tables[] = $row; // Store available table details (id, capacity, location, etc.)
        }
        $available_stmt->close();


        // --- End of Availability Check ---

        // Return available tables as JSON
        echo json_encode([
            'success' => true,
            'tables' => $available_tables // Send the list of available tables
        ]);

    } catch (Exception $e) {
        // Log the error and return a generic message
        error_log("Error fetching available tables: " . $e->getMessage());
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching tables.']);
    }


    $conn->close(); // Close database connection

} else {
    // Invalid request method or missing parameters
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    $conn->close();
    exit;
}

// Helper function to pass array elements by reference for bind_param
// Needed when the number of parameters is dynamic (like the IN clause)
function array_ref($arr) {
    $refs = [];
    foreach($arr as $key => $value) {
        $refs[$key] = &$arr[$key];
    }
    return $refs;
}

?>