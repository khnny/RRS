<?php
/**
 * Savory Haven Restaurant - Admin Panel - Users Management Page
 * Displays and handles basic CRUD operations for the adminPanel_users table.
 * Accessible only by 'super admin'.
 */

// This file assumes admin.php has already:
// - Started the session
// - Included config.php (which provides $conn, is_logged_in, check_permission, redirect_with_message)
// - Checked for administrator authentication
// - Defined the $page variable

// --- Check if the user has 'super admin' permission to view this page ---
if (!check_permission('super_admin')) {
    // If not super admin, redirect to dashboard or show an error
    redirect_with_message('dashboard', 'error', 'You do not have permission to access user management.');
    exit; // Stop script execution
}


// --- FETCH USERS FOR DISPLAY ---
$users = [];
// Assuming $conn is open and authenticated check has passed
if ($conn) {
    // Fetch all users from the adminPanel_users table
    // IMPORTANT: NEVER select the password_hash here for display!
    $sql = "SELECT id, username, role, full_name, email, created_at, updated_at FROM adminPanel_users ORDER BY id ASC";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        $result->free();
    } else {
        echo "<div class='alert alert-danger'>Error fetching users: " . $conn->error . "</div>";
        error_log("Error fetching users: " . $conn->error);
    }
}


// --- HTML for Users Management Page ---
// This HTML will be included directly into the main-content div of admin.php
?>

<div class="row justify-content-between align-items-center mb-4">
    <div class="col-md-6">
        <h1 class="admin-title">Users Management</h1>
        <p class="text-muted">Manage admin panel user accounts and roles</p>
    </div>
    <div class="col-md-6 text-md-end">
        <?php // Link to Add New User page (will need add_user.php) ?>
        <a href="add_user.php" class="btn btn-primary">
             <i class="fas fa-user-plus me-1"></i> Add New User
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header admin-header">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i> Existing Users
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> No users found in the database.
            </div>
        <?php else: ?>
             <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                             <th>Full Name</th>
                            <th>Email</th>
                            <th>Created At</th>
                             <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                                 <td><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                 <td><?php echo htmlspecialchars($user['updated_at']); ?></td>
                                <td>
                                    <?php // Link to Edit User page (will need edit_user.php) ?>
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-secondary me-1" title="Edit User">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    <?php // Delete User Form - posts back to admin.php ?>
                                    <?php // Only show delete if NOT the current logged in user and has super admin permission ?>
                                    <?php if ($user['role'] !== 'Super_admin'): ?>
                                    <form action="delete_user.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                   <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                  <button type="submit" class="btn btn-sm btn-outline-danger">
                                   <i class="fas fa-trash" style="color: red;"></i> Delete
                                        </button>
                                        </form>
                                    <?php endif; ?> 
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
             </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Note: This file is included within admin.php, so no full HTML structure needed.
// JavaScript for delete confirmation should be in the main admin.php script block.
?>
