<?php
/**
 * Savory Haven Restaurant - Admin Panel - Edit User Page
 * Displays a form to edit an existing admin panel user.
 * Accessible only by 'super admin'.
 */

// Include the configuration file which handles DB connection, session start, and helpers
// This file assumes it's accessed via a link from manage_users.php and redirects if not authenticated
require_once 'config.php';

// --- Check if the user has 'super admin' permission to access this page ---
// If not super admin, redirect to dashboard with an error message
if (!check_permission('super_admin')) {
    redirect_with_message('dashboard', 'error', 'You do not have permission to edit users.');
    exit; // Stop script execution
}

// --- Get User ID from URL ---
$user_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Validate User ID
if ($user_id === false || $user_id === null || $user_id <= 0) {
    // Invalid or missing ID, redirect back to users list with an error
    redirect_with_message('users', 'error', 'Invalid user ID specified.');
    exit;
}

// --- Fetch User Data from Database ---
$user = null; // Initialize user variable
if ($conn) {
    // Prepare SQL to fetch user data (excluding password hash for security)
    $sql = "SELECT id, username, role, full_name, email, created_at, updated_at FROM adminPanel_users WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
        } else {
            // User not found with this ID
            redirect_with_message('users', 'error', 'User not found.');
            exit;
        }

        $stmt->close();
    } else {
        // Database error preparing statement
        $error_message = "Database error fetching user data: " . $conn->error;
        error_log($error_message);
        redirect_with_message('users', 'error', 'Error fetching user data.');
        exit;
    }
} else {
     // $conn was not set, usually indicates a config.php issue
     error_log("Database connection not available in edit_user.php");
     redirect_with_message('users', 'error', 'Database connection error.');
     exit;
}

// --- Get Available Roles for Dropdown ---
// Get available roles from the hierarchy defined in config.php
$roles_hierarchy = [
    'staff'       => 1,
    'admin'       => 2,
    'super admin' => 3
]; // Make sure this matches your config.php hierarchy exactly
$available_roles = array_keys($roles_hierarchy);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Savory Haven Admin</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        /* Add any specific styles for this page if needed */
        .edit-user-form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
         .form-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: #343a40;
            margin-bottom: 30px;
            text-align: center;
         }
    </style>
</head>
<body>

    <div class="edit-user-form-container">
         <h3 class="form-title"><i class="fas fa-user-edit me-2"></i> Edit User: <?php echo htmlspecialchars($user['username']); ?></h3>

        <?php
        // --- Display Feedback Message if set (e.g., from a previous failed attempt) ---
        // Check for status and message parameters in the URL from a redirect
        $feedback_message = '';
        $feedback_status = '';
        if (isset($_GET['status']) && isset($_GET['message'])) {
            $feedback_status = htmlspecialchars($_GET['status']);
            $feedback_message = htmlspecialchars($_GET['message']);
            // Optional: JavaScript to clear URL parameters after display
             echo '<script>
                 window.addEventListener("load", function() {
                      const url = new URL(window.location.href);
                      if (url.searchParams.has("status") || url.searchParams.has("message")) {
                           url.searchParams.delete("status");
                           url.searchParams.delete("message");
                           if (window.history.replaceState) {
                                window.history.replaceState({}, document.title, url.toString());
                           }
                      }
                 });
             </script>';
        }

        if (!empty($feedback_message)): ?>
            <div class="alert alert-<?php echo ($feedback_status === 'success' ? 'success' : 'danger'); ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo ($feedback_status === 'success' ? 'check-circle' : 'exclamation-triangle'); ?> me-2"></i>
                <?php echo $feedback_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>


         <form action="admin.php" method="post">
             <input type="hidden" name="action" value="edit_user"> <?php // Hidden input for the action ?>
             <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>"> <?php // Hidden input for the user ID ?>

             <div class="mb-3">
                 <label for="username" class="form-label">Username:</label>
                 <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
             </div>

             <div class="mb-3">
                 <label for="role" class="form-label">Role:</label>
                 <select class="form-select" id="role" name="role" required>
                      <option value="">Select Role</option>
                      <?php
                       // Populate roles from the hierarchy and pre-select the user's current role
                       foreach ($available_roles as $role_option) {
                            $selected = ($user['role'] === $role_option) ? 'selected' : '';
                            // Optional: Prevent demoting the current logged-in user's role if they are super admin
                            // if (check_permission('super admin') && $user['id'] == $_SESSION['user_id'] && $role_option !== 'super admin') {
                            //     // Don't allow a super admin to demote themselves
                            //     echo '<option value="' . htmlspecialchars($role_option) . '" ' . $selected . ' disabled>' . htmlspecialchars(ucfirst($role_option)) . ' (Cannot demote self)</option>';
                            // } else {
                                 echo '<option value="' . htmlspecialchars($role_option) . '" ' . $selected . '>' . htmlspecialchars(ucfirst($role_option)) . '</option>';
                            // }
                       }
                      ?>
                 </select>
                  <?php // Optional: Add a warning if a super admin is trying to change their own role ?>
                  <?php if (check_permission('super admin') && $user['id'] == $_SESSION['user_id']): ?>
                      <div class="form-text text-warning">Warning: Changing your own role might limit your access. You cannot demote yourself below super admin.</div>
                  <?php endif; ?>
             </div>

              <div class="mb-3">
                 <label for="full_name" class="form-label">Full Name (Optional):</label>
                 <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
             </div>

              <div class="mb-3">
                 <label for="email" class="form-label">Email (Optional):</label>
                 <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
             </div>

             <hr> <?php // Separator for password change section ?>

             <div class="mb-3">
                 <label for="new_password" class="form-label">New Password (Leave blank if not changing):</label>
                 <input type="password" class="form-control" id="new_password" name="new_password">
             </div>

              <div class="mb-3">
                 <label for="confirm_new_password" class="form-label">Confirm New Password:</label>
                 <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password">
                  <div id="passwordHelpBlock" class="form-text">
                       Enter a new password only if you wish to change it. Confirm it below.
                  </div>
             </div>


             <button type="submit" class="btn btn-primary">
                 <i class="fas fa-save me-1"></i> Save Changes
             </button>

             <a href="admin.php?page=users" class="btn btn-secondary ms-2">
                 <i class="fas fa-arrow-left me-1"></i> Cancel
             </a>
         </form>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
     <?php // You might need other scripts like main.js or admin.js if they have global listeners ?>
     <script>
         // Optional: Add client-side validation for password fields if filled
         document.addEventListener('DOMContentLoaded', function() {
             const form = document.querySelector('.edit-user-form-container form');
             const newPassword = document.getElementById('new_password');
             const confirmNewPassword = document.getElementById('confirm_new_password');

             form.addEventListener('submit', function(event) {
                 if (newPassword.value !== '' || confirmNewPassword.value !== '') {
                     if (newPassword.value !== confirmNewPassword.value) {
                         alert('New password and confirm password do not match!');
                         event.preventDefault(); // Prevent form submission
                     }
                 }
             });
         });
     </script>
</body>
</html>