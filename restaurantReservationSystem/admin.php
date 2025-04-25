<?php
/**
 * Savory Haven Restaurant - Admin Panel
 * This is the main admin page for managing different sections.
 * All action processing (that requires redirects) happens here before output.
 */
error_reporting(E_ALL); // Show all errors during development
ini_set('display_errors', 1);

// Start session (must be before any output)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Include Configuration and Helpers ---
// Include config.php which contains DB connection, session handling, and helper functions
require_once 'config.php';


// --- Database Connection Check (Using $conn from config.php) ---
if ($conn->connect_error) {
    // Error logging handled in config.php
    die("A database error occurred. Please try again later.");
}


// --- Authentication (Database Driven) ---
// Removed hardcoded $valid_username and $valid_password

$login_error = ""; // Initialize login error variable

// Check if user is trying to log in (This must happen before checking authentication for content)
if (isset($_POST['username']) && isset($_POST['password']) && !isset($_SESSION['admin_authenticated'])) {
    $submitted_username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $submitted_password = $_POST['password']; // Password will be verified against hash

    // Prepare SQL to fetch user by username from the adminPanel_users table
    $sql = "SELECT id, username, password_hash, role FROM adminPanel_users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $submitted_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify the submitted password against the hashed password from the database
            if (password_verify($submitted_password, $user['password_hash'])) {
                // Authentication successful!
                $_SESSION['admin_authenticated'] = true;
                $_SESSION['user_id'] = $user['id']; // Store user ID in session
                $_SESSION['username'] = $user['username']; // Store username in session
                $_SESSION['user_role'] = $user['role']; // Store the user's actual role from the database

                // Regenerate session ID after login for security (must be before output)
                session_regenerate_id(true);
                // Redirect to avoid resubmission on refresh (must be before output)
                header('Location: admin.php');
                exit; // Stop script execution after redirect

            } else {
                // Password does not match
                $login_error = "Invalid username or password";
            }
        } else {
            // Username not found or multiple users with same username (shouldn't happen with unique username)
            $login_error = "Invalid username or password";
        }

        $stmt->close();
    } else {
        // Database error preparing statement
        $login_error = "Database error during login.";
        error_log("Database error preparing login statement: " . $conn->error);
    }
}

// Check if user is trying to log out (must be before output)
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Unset specific session variables, then destroy the session
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    // Redirect to login page (admin.php without authentication) (must be before output)
    header('Location: admin.php');
    exit; // Stop script execution after redirect
}

// Check if user is NOT authenticated after trying to log in (or on initial page load)
if (!isset($_SESSION['admin_authenticated']) || !$_SESSION['admin_authenticated']) {
    // If not authenticated, show the login form below the 'else:' block in the HTML
    $show_login_form = true; // Use a flag to indicate showing the form
} else {
    $show_login_form = false; // User is authenticated, don't show login form
}


// --- CENTRALIZED ACTION HANDLING (Handle POST requests - MUST be before any output) ---
// This is the *single* block that processes form submissions from the admin panel.
// It only runs if the user is authenticated and it's a POST request.
// It uses helper functions like check_permission() and redirect_with_message() from config.php
if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] && $_SERVER["REQUEST_METHOD"] == "POST") {

    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);

    // --- Handle Reservation Actions (confirm, cancel, delete) ---
    // These actions are triggered by forms in manage_reservations.php posting back to admin.php
    if (in_array($action, ['confirm', 'cancel']) && isset($_POST['reservation_id'])) {
         // Staff or higher can confirm/cancel
         if (check_permission('staff')) {
             $reservation_id = filter_input(INPUT_POST, 'reservation_id', FILTER_SANITIZE_NUMBER_INT);
             if ($reservation_id !== false && $reservation_id !== null) {
                 $new_status = ($action === 'confirm') ? 'confirmed' : 'cancelled';
                 $sql = "UPDATE table_booking SET status = ? WHERE id = ?";
                 $stmt = $conn->prepare($sql);
                 if ($stmt) {
                     $stmt->bind_param("si", $new_status, $reservation_id);
                     if ($stmt->execute()) {
                         redirect_with_message('reservations', 'success', 'Reservation successfully ' . $action . 'ed!');
                     } else {
                         error_log("Error executing statement ($action) for ID $reservation_id: " . $stmt->error);
                         redirect_with_message('reservations', 'error', 'Error ' . $action . 'ing reservation.');
                     }
                     $stmt->close();
                 } else {
                      error_log("Error preparing statement for action ($action) for ID $reservation_id: " . $conn->error);
                     redirect_with_message('reservations', 'error', 'Error preparing database statement.');
                 }
             } else {
                 redirect_with_message('reservations', 'error', 'Invalid reservation ID.');
             }
         } else {
             redirect_with_message('reservations', 'error', 'Insufficient permissions to ' . $action . ' reservation.');
         }
    } elseif ($action === 'delete' && isset($_POST['reservation_id'])) {
        // --- Only Admin or Super Admin can delete reservations ---
        if (check_permission('admin')) { // Adjusted permission check for delete reservation
            $reservation_id = filter_input(INPUT_POST, 'reservation_id', FILTER_SANITIZE_NUMBER_INT);
            if ($reservation_id !== false && $reservation_id !== null) {
                 $sql = "DELETE FROM table_booking WHERE id = ?";
                 $stmt = $conn->prepare($sql);
                 if ($stmt) {
                     $stmt->bind_param("i", $reservation_id);
                      if ($stmt->execute()) {
                         redirect_with_message('reservations', 'success', 'Reservation successfully deleted!');
                     } else {
                         error_log("Error executing statement (delete) for ID $reservation_id: " . $stmt->error);
                         redirect_with_message('reservations', 'error', 'Error deleting reservation.');
                     }
                     $stmt->close();
                 } else {
                      error_log("Error preparing statement (delete) for ID $reservation_id: " . $conn->error);
                     redirect_with_message('reservations', 'error', 'Error preparing database statement.');
                 }
            } else {
                redirect_with_message('reservations', 'error', 'Invalid reservation ID.');
            }
        } else {
            redirect_with_message('reservations', 'error', 'Insufficient permissions to delete reservation.');
        }
    }

    // --- Handle Table Actions (add_table, delete_table) ---
    // These actions are triggered by forms in manage_tables.php posting back to admin.php
     if ($action === 'add_table') {
         // --- Only Admin or Super Admin can add tables ---
         if (check_permission('admin')) { // Adjusted permission check for add table
             $physical_table_id = filter_input(INPUT_POST, 'new_physical_table_id', FILTER_SANITIZE_NUMBER_INT);
             $capacity = filter_input(INPUT_POST, 'new_capacity', FILTER_SANITIZE_NUMBER_INT);
             $location = filter_input(INPUT_POST, 'new_location', FILTER_SANITIZE_SPECIAL_CHARS);
             $availability = filter_input(INPUT_POST, 'new_availability', FILTER_SANITIZE_SPECIAL_CHARS);

             if ($physical_table_id && $capacity && $location && $availability) {
                 if (!in_array($availability, ['available', 'taken', 'unavailable'])) {
                      redirect_with_message('tables', 'error', 'Invalid availability value.');
                 }
                 $sql = "INSERT INTO table_mapping (physical_table_id, capacity, location, availability) VALUES (?, ?, ?, ?)";
                 $stmt = $conn->prepare($sql);
                 if ($stmt) {
                     $stmt->bind_param("iiss", $physical_table_id, $capacity, $location, $availability);
                      if ($stmt->execute()) {
                         redirect_with_message('tables', 'success', 'Table successfully added!');
                     } else {
                         error_log("Error executing add table statement: " . $stmt->error);
                         if ($conn->errno == 1062) { // MySQL duplicate entry error code
                             redirect_with_message('tables', 'error', 'Error adding table: Physical Table ID already exists.');
                         } else {
                             redirect_with_message('tables', 'error', 'Error adding table.');
                         }
                     }
                     $stmt->close();
                 } else {
                     error_log("Error preparing add table statement: " . $conn->error);
                     redirect_with_message('tables', 'error', 'Error preparing database statement.');
                 }
             } else {
                 redirect_with_message('tables', 'error', 'Missing fields for adding a table.');
             }
         } else {
             redirect_with_message('tables', 'error', 'Insufficient permissions to add a table.');
         }
     } elseif ($action === 'delete_table' && isset($_POST['table_id'])) {
         // --- Only Super Admin can delete tables (most critical action) ---
         if (check_permission('admin')) { // Adjusted permission check for delete table
             $table_id = filter_input(INPUT_POST, 'table_id', FILTER_SANITIZE_NUMBER_INT);
             if ($table_id) {
                 $sql = "DELETE FROM table_mapping WHERE id = ?";
                 $stmt = $conn->prepare($sql);
                 if ($stmt) {
                     $stmt->bind_param("i", $table_id);
                      if ($stmt->execute()) {
                         redirect_with_message('tables', 'success', 'Table successfully deleted!');
                     } else {
                         error_log("Error executing delete table statement: " . $stmt->error);
                         redirect_with_message('tables', 'error', 'Error deleting table.');
                     }
                     $stmt->close();
                 } else {
                     error_log("Error preparing delete table statement: " . $conn->error);
                     redirect_with_message('tables', 'error', 'Error preparing database statement.');
                 }
             } else {
                 redirect_with_message('tables', 'error', 'Invalid table ID for deletion.');
             }
         } else {
             redirect_with_message('tables', 'error', 'Insufficient permissions to delete a table.');
         }
     }
 // --- Handle User Actions (delete_user, add_user, edit_user) ---
    // These actions are triggered by forms posting back to admin.php
    elseif ($action === 'delete_user' && isset($_POST['user_id'])) { // --- ADDED THIS BLOCK ---
        // --- Only Super Admin can delete users ---
        if (check_permission('super_admin')) {
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
            if ($user_id !== false && $user_id !== null) {

                // IMPORTANT SECURITY CHECK: Prevent a Super Admin from deleting their OWN account
                if (isset($_SESSION['user_id']) && $user_id == $_SESSION['user_id']) {
                     redirect_with_message('users', 'error', 'You cannot delete your own account.');
                } else {
                    $sql = "DELETE FROM adminPanel_users WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        $stmt->bind_param("i", $user_id);
                         if ($stmt->execute()) {
                            redirect_with_message('users', 'success', 'User successfully deleted!');
                        } else {
                            error_log("Error executing delete user statement for ID $user_id: " . $stmt->error);
                            redirect_with_message('users', 'error', 'Error deleting user.');
                        }
                        $stmt->close();
                    } else {
                        error_log("Error preparing delete user statement: " . $conn->error);
                        redirect_with_message('users', 'error', 'Error preparing database statement.');
                    }
                } // End check for deleting self
            } else {
                redirect_with_message('users', 'error', 'Invalid user ID for deletion.');
            }
        } else {
            redirect_with_message('users', 'error', 'Insufficient permissions to delete users.');
        }
     }
     // Add elseif blocks here for 'add_user' and 'edit_user' actions when you create forms for them posting back here.


    // If it's a POST request but not a recognized action, you might want to log or handle it
    // else { error_log("Unknown POST action received: " . $action); }
}
// --- END CENTRALIZED ACTION HANDLING ---

// --- Display feedback message if set from redirects (Catching GET parameters) ---
// This happens AFTER POST handling and redirects, but BEFORE the main HTML.
$feedback_message = '';
$feedback_status = '';
if (isset($_GET['status']) && isset($_GET['message'])) {
    $feedback_status = htmlspecialchars($_GET['status']);
    $feedback_message = htmlspecialchars($_GET['message']);
    // JavaScript below will clear the parameters from the URL after display
}

// --- Define the $page variable HERE ---
// This needs to be defined before the HTML starts for the sidebar links.
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; // Default to dashboard if no page is specified


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savory Haven - Admin Panel</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    
</head>
<body>
    <?php if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']): ?>
        <div class="sidebar">
            <h2 class="sidebar-heading">Savory Haven</h2>
                            <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page === 'dashboard') ? 'active' : ''; ?>" href="admin.php?page=dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page === 'reservations') ? 'active' : ''; ?>" href="admin.php?page=reservations">
                            <i class="fas fa-calendar-alt"></i> Reservations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page === 'tables') ? 'active' : ''; ?>" href="admin.php?page=tables">
                            <i class="fas fa-chair"></i> Tables
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-utensils"></i> Menu</a></li>

                    <?php // --- Show Users link only for Super Admin --- ?>
                    <?php if (check_permission('super_admin')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page === 'users') ? 'active' : ''; ?>" href="admin.php?page=users">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php // --- End Users link check --- ?>

                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li class="nav-item mt-auto mb-2">
                        <a class="nav-link" href="index.html" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Website
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?action=logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
             <div class="sidebar-footer">
                 <small>&copy; <?php echo date("Y"); ?> Savory Haven</small>
             </div>
        </div>

        <div class="main-content">
            <?php
            // Display feedback message if set (using variables populated before HTML)
            // $feedback_message and $feedback_status are set before the HTML starts
            if (!empty($feedback_message)): ?>
                <div class="alert alert-<?php echo ($feedback_status === 'success' ? 'success' : 'danger'); ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo ($feedback_status === 'success' ? 'check-circle' : 'exclamation-triangle'); ?> me-2"></i>
                    <?php echo htmlspecialchars($feedback_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php
            // --- Define the $page variable HERE, inside the authenticated section ---
            // This ensures $page is defined only when the user is authenticated
            $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; // Default to dashboard if no page is specified

            // --- Fetch counts for the dashboard (only done if page is dashboard) ---
            // This logic remains here as it's specific to the dashboard overview.
            $total_reservations = 0;
            $pending_reservations = 0;
            $todays_guests = 0;

            if ($page === 'dashboard') {
                // Database queries for counts using $conn from config.php

                 // Fetch total reservation count
                $count_sql = "SELECT COUNT(*) AS total_count FROM table_booking";
                if ($count_result = $conn->query($count_sql)) {
                    $count_row = $count_result->fetch_assoc();
                    $total_reservations = $count_row['total_count'];
                    $count_result->free();
                } else {
                     error_log("Error fetching total reservation count: " . $conn->error);
                }

                // Fetch pending reservation count
                $pending_sql = "SELECT COUNT(*) AS pending_count FROM table_booking WHERE status = 'pending'";
                if ($pending_result = $conn->query($pending_sql)) {
                    $pending_row = $pending_result->fetch_assoc();
                    $pending_reservations = $pending_row['pending_count'];
                    $pending_result->free();
                } else {
                     error_log("Error fetching pending reservation count: " . $conn->error);
                }

                // Fetch total guests for today's confirmed reservations
                $today_date = date('Y-m-d');
                $today_sql = "SELECT SUM(people_count) AS guests_today FROM table_booking WHERE date = ? AND status = 'confirmed'";
                $stmt_today = $conn->prepare($today_sql);
                if ($stmt_today) {
                    $stmt_today->bind_param("s", $today_date);
                    if ($stmt_today->execute()) {
                        $today_result = $stmt_today->get_result();
                        $today_row = $today_result->fetch_assoc();
                        $todays_guests = $today_row['guests_today'] ?? 0;
                        $today_result->free();
                    } else {
                        error_log("Error executing today's guests query: " . $stmt_today->error);
                    }
                    $stmt_today->close();
                } else {
                     error_log("Error preparing today's guests query: " . $conn->error);
                }
            }


            
            // --- Content based on the 'page' parameter ---

            if ($page === 'dashboard') {
                // Dashboard Content (HTML)
                // Keep this PHP block open as it contains HTML mixed with PHP
            ?>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                     <h1 class="admin-title h2">Dashboard</h1>
                </div>
                 <p class="text-muted mb-4">Overview of your restaurant activity</p>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-center mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-calendar-check text-primary me-2"></i> Total Reservations</h5>
                                <p class="card-text display-4 fw-bold"><?php echo $total_reservations; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-concierge-bell text-warning me-2"></i> Pending Reservations</h5>
                                <p class="card-text display-4 fw-bold"><?php echo $pending_reservations; ?></p>
                            </div>
                        </div>
                    </div>
                     <div class="col-md-4">
                        <div class="card text-center mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-users text-success me-2"></i> Today's Guests</h5>
                                <p class="card-text display-4 fw-bold"><?php echo $todays_guests; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php // Close the PHP block after the dashboard HTML 

            
            } elseif ($page === 'reservations') {
                // Reservations Management Page Content
                include 'manage_reservations.php';
            } elseif ($page === 'tables') {
                // Tables Management Page Content
                include 'manage_tables.php';
            } elseif ($page === 'users') {
                 // Users Management Page Content
                 include 'manage_users.php';
            }
            // Add more `elseif` blocks here for other pages (Menu, Reports)
            // elseif ($page === 'menu') { include 'manage_menu.php'; }
            // elseif ($page === 'reports') { include 'reports.php'; }
            else {
                // Default or fallback content if page isn't recognized
                 echo "<div class='alert alert-warning'>Page not found.</div>";
            }
            // The PHP block for page content ends here with the final ?> tag below this block
            ?>

            <div class="text-muted text-center mt-4 pt-3 border-top">
                 <small>&copy; <?php echo date("Y"); ?> Savory Haven</small>
             </div>
        </div>

    <?php else: ?>
        <div class="container">
            <div class="row justify-content-center">
                 <div class="col-md-6 col-lg-5">
                     <div class="card login-form">
                         <div class="card-header admin-header text-center py-3">
                             <h3 class="mb-0 admin-title">Admin Login</h3>
                         </div>
                         <div class="card-body p-4 p-md-5">
                             <?php if (isset($login_error)): ?>
                                 <div class="alert alert-danger small">
                                     <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $login_error; ?>
                                 </div>
                             <?php endif; ?>

                             <form method="post" action="admin.php">
                                 <div class="mb-3">
                                     <label for="username" class="form-label">Username</label>
                                     <input type="text" class="form-control" id="username" name="username" required>
                                 </div>
                                 <div class="mb-4">
                                     <label for="password" class="form-label">Password</label>
                                     <input type="password" class="form-control" id="password" name="password" required>
                                 </div>
                                 <div class="d-grid">
                                     <button type="submit" class="btn btn-primary btn-lg">
                                         <i class="fas fa-sign-in-alt me-2"></i> Login
                                     </button>
                                 </div>
                             </form>
                             <div class="mt-4 text-center">
                                 <a href="index.html" class="text-decoration-none text-muted">
                                     <i class="fas fa-arrow-left me-1"></i> Back to website
                                 </a>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
        </div>
    <?php endif; ?>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
<?php
// Close the database connection at the very end of the script
// This ensures the connection is kept open for all included files' operations.
if ($conn) {
   $conn->close();
}
?>
</html>