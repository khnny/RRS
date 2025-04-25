<?php
/**
 * Savory Haven Restaurant - Admin Panel - Add New User Page
 * Displays a form for adding a new admin panel user.
 * Accessible only by 'super admin'.
 */

// Include the configuration file which handles DB connection, session start, and helpers
// This file assumes it's accessed via admin.php or redirects to admin.php if not authenticated
require_once 'config.php';

// --- Check if the user has 'super admin' permission to access this page ---
// If not super admin, redirect to dashboard with an error message
if (!check_permission('super_admin')) {
    redirect_with_message('dashboard', 'error', 'You do not have permission to add new users.');
    exit; // Stop script execution
}

// Note: $conn is available from config.php if needed for dropdown options, etc.
// We will fetch available roles from the config.php hierarchy for the role dropdown.

// Get available roles from the hierarchy defined in config.php
$roles_hierarchy = [
    'staff'       => 1,
    'admin'       => 2,
    'super_admin' => 3
]; // Make sure this matches your config.php hierarchy exactly


// --- HTML for Add New User Page ---
// This file is a standalone page, so it needs the full HTML structure.
// However, since it's part of the admin panel, it will likely be included
// within the main admin.php layout IF you decide to change the structure later.
// For now, we'll build it as a basic standalone page that links back.
// A simpler approach might be to INCLUDE this form directly in manage_users.php
// if you prefer a single page layout. Let's build it standalone for now,
// linking back to manage_users.php.

// If you are including this page directly within admin.php's main-content div,
// you would remove the <DOCTYPE>, <html>, <head>, <body>, and their closing tags,
// and also remove the CSS/JS includes here. Let's assume it's standalone for now.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User - Savory Haven Admin</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        /* Add any specific styles for this page if needed */
        .add-user-form-container {
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

    <div class="add-user-form-container">
         <h3 class="form-title"><i class="fas fa-user-plus me-2"></i> Add New Admin User</h3>

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
             <input type="hidden" name="action" value="add_user"> <?php // Hidden input to tell admin.php what action to perform ?>

             <div class="mb-3">
                 <label for="username" class="form-label">Username:</label>
                 <input type="text" class="form-control" id="username" name="username" required>
             </div>

             <div class="mb-3">
                 <label for="password" class="form-label">Password:</label>
                 <input type="password" class="form-control" id="password" name="password" required>
             </div>

              <div class="mb-3">
                 <label for="confirm_password" class="form-label">Confirm Password:</label>
                 <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
             </div>

             <div class="mb-3">
                 <label for="role" class="form-label">Role:</label>
                 <select class="form-select" id="role" name="role" required>
                      <option value="">Select Role</option>
                      <?php
                       // Populate roles from the hierarchy, excluding super admin if preferred,
                       // or allowing all roles for assignment. Let's allow all for now.
                       // Get roles from the keys of the hierarchy array in config.php
                       $available_roles = array_keys($roles_hierarchy);
                       foreach ($available_roles as $role_option) {
                            // Optional: Prevent creating another Super Admin account via this form
                            // if ($role_option !== 'super admin') {
                                 echo '<option value="' . htmlspecialchars($role_option) . '">' . htmlspecialchars(ucfirst($role_option)) . '</option>';
                           // }
                       }
                      ?>
                 </select>
             </div>

              <div class="mb-3">
                 <label for="full_name" class="form-label">Full Name (Optional):</label>
                 <input type="text" class="form-control" id="full_name" name="full_name">
             </div>

              <div class="mb-3">
                 <label for="email" class="form-label">Email (Optional):</label>
                 <input type="email" class="form-control" id="email" name="email">
             </div>

             <button type="submit" class="btn btn-primary">
                 <i class="fas fa-user-plus me-1"></i> Add User
             </button>

             <a href="admin.php?page=users" class="btn btn-secondary ms-2">
                 <i class="fas fa-arrow-left me-1"></i> Cancel
             </a>
         </form>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
     <?php // You might need other scripts like main.js or admin.js if they have global listeners ?>
</body>
</html>