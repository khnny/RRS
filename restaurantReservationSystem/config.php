<?php
// --- config.php ---

// Error Reporting (Keep for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Credentials
$host = "localhost";
$username = "root";
$password = "";
$db_name = "rrs"; // Make sure this matches your database name

// --- Session Management ---
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Database Connection ---
$conn = new mysqli($host, $username, $password, $db_name);

// Check Connection
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    die("A database error occurred. Please try again later.");
}

// --- Authentication & Authorization Functions ---

/**
 * Checks if a user is currently logged in.
 * @return bool True if logged in, false otherwise.
 */
function is_logged_in(): bool {
    return isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']; // Check the actual session variable you use for admin auth
}

/**
 * Gets the role of the currently logged-in user.
 * @return string|null The user's role ('admin', 'manager', 'staff') or null if not logged in.
 */
function get_current_user_role(): ?string {
     // Ensure this matches how you store the role in the session upon login
    return $_SESSION['user_role'] ?? null;
}

/**
 * Checks if the current user has the required permission level (or higher).
 * Assumes a hierarchy: admin > manager > staff
 * @param string $required_role The minimum role required ('admin', 'manager', 'staff').
 * @return bool True if the user meets the requirement, false otherwise.
 */
function check_permission(string $required_role): bool {
    $current_role = get_current_user_role();

    if (!$current_role) {
        return false; // Not logged in or role not set
    }

    $roles_hierarchy = [
        'staff'  => 1,
        'admin' => 2,
        'super_admin'  => 3
    ];

    if (!isset($roles_hierarchy[$current_role]) || !isset($roles_hierarchy[$required_role])) {
        // Log an error here if an invalid role is being checked or assigned
         error_log("Invalid role '$current_role' or required role '$required_role' used in check_permission.");
        return false; // Invalid role specified
    }

    return $roles_hierarchy[$current_role] >= $roles_hierarchy[$required_role];
}

/**
 * Redirects the user to a different page with optional status message parameters.
 * Headers must NOT have been sent before calling this function.
 * @param string $page The target page (e.g., 'dashboard', 'reservations', 'tables').
 * @param string $status The status ('success' or 'error').
 * @param string $message The message to display.
 */
function redirect_with_message(string $page, string $status, string $message): void {
    // Make sure no output has occurred before sending headers
    if (headers_sent()) {
         error_log("Headers already sent! Cannot redirect. Output started at " . headers_sent($file, $line) . ".");
         // In a production environment, you might show an error message here instead of redirecting
        die("Error: Output started before redirect. Cannot proceed.");
    }
    header("Location: admin.php?page=$page&status=$status&message=" . urlencode($message));
    exit;
}

// Ensure no accidental output before the closing tag (or omit closing tag which is safer)
?>