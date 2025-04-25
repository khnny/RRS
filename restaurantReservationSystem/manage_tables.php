<?php
/**
 * Savory Haven Restaurant - Admin Panel - Tables Management Page
 * Displays and handles CRUD operations for the table_mapping table.
 */


// --- FETCH TABLES FOR DISPLAY ---
$tables = [];
// Assuming $conn is open and authenticated check has passed
if ($conn) {
    $sql = "SELECT id, physical_table_id, capacity, location, availability FROM table_mapping ORDER BY id ASC";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $tables[] = $row;
            }
        }
        $result->free();
    } else {
        echo "<div class='alert alert-danger'>Error fetching tables: " . $conn->error . "</div>";
    }
}

// --- HTML for Tables Management Page ---
// This HTML will be included directly into the main-content div of admin.php
?>

<div class="row justify-content-between align-items-center mb-4">
    <div class="col-md-6">
        <h1 class="admin-title">Tables Management</h1>
        <p class="text-muted">Add, edit, and remove tables from your restaurant layout</p>
    </div>
    <div class="col-md-6 text-md-end">
        </div>
</div>

<div class="card mb-4">
    <div class="card-header admin-header">
        <h5 class="mb-0">
            <i class="fas fa-plus-circle me-2"></i> Add New Table
        </h5>
    </div>
    <div class="card-body">
        <?php if (check_permission('admin')): ?>
            <form action="" method="post">
                <input type="hidden" name="action" value="add_table">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="new_physical_table_id" class="form-label">Physical Table ID</label>
                        <input type="number" class="form-control" id="new_physical_table_id" name="new_physical_table_id" required min="1">
                    </div>
                    <div class="col-md-3">
                        <label for="new_capacity" class="form-label">Capacity</label>
                        <input type="number" class="form-control" id="new_capacity" name="new_capacity" required min="1">
                    </div>
                    <div class="col-md-3">
                        <label for="new_location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="new_location" name="new_location" placeholder="e.g., Window, Patio, Booth" required>
                    </div>
                    <div class="col-md-3">
                        <label for="new_availability" class="form-label">Initial Availability</label>
                        <select class="form-select" id="new_availability" name="new_availability" required>
                            <option value="available">Available</option>
                            <option value="taken">Taken</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle me-1"></i> Add Table</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i> Insufficient permissions to add tables.
            </div>
        <?php endif; ?>
    </div>
</div>


 <div class="card mb-4">
    <div class="card-header admin-header">
        <h5 class="mb-0">
            <i class="fas fa-chair me-2"></i> Existing Tables
        </h5>
    </div>
    <div class="card-body">
         <?php if (empty($tables)): ?>
             <div class="alert alert-info">
                 <i class="fas fa-info-circle me-2"></i> No tables found in the database.
             </div>
        <?php else: ?>
             <div class="table-list">
                 <?php foreach ($tables as $table): ?>
                     <div class="card table-card">
                         <div class="card-body">
                             <div class="row align-items-center">
                                 <div class="col-md-8">
                                     <p class="mb-1">
                                         <strong>Table ID: <?php echo htmlspecialchars($table['id']); ?></strong> (Physical ID: <?php echo htmlspecialchars($table['physical_table_id']); ?>)
                                     </p>
                                      <p class="mb-1 text-muted"><small>
                                          Capacity: <?php echo htmlspecialchars($table['capacity']); ?> |
                                          Location: <?php echo htmlspecialchars($table['location']); ?>
                                      </small></p>
                                      <p class="mb-0"><small>
                                           Availability: <span class="availability-<?php echo strtolower($table['availability']); ?>"><?php echo htmlspecialchars(ucfirst($table['availability'])); ?></span>
                                      </small></p>
                                 </div>
                                 <div class="col-md-4 text-end">
                                     <a href="edit_table.php?id=<?php echo $table['id']; ?>" class="btn btn-sm btn-outline-secondary me-1" title="Edit Table">
                                         <i class="fas fa-edit"></i> Edit
                                     </a>

                                     <?php if (check_permission('admin')): ?>
                                         <form action="" method="post" class="d-inline delete-table-form">
                                             <input type="hidden" name="action" value="delete_table">
                                             <input type="hidden" name="table_id" value="<?php echo $table['id']; ?>">
                                              <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Table">
                                                  <i class="fas fa-trash-alt"></i> Delete
                                              </button>
                                         </form>
                                     <?php endif; ?>
                                 </div>
                             </div>
                         </div>
                     </div>
                 <?php endforeach; ?>
             </div>
        <?php endif; ?>
    </div>
</div>

<?php // No closing </body> or </html> tag here, as this file is included in admin.php ?>
<?php // JavaScript for delete confirmation is in the main admin.php script block ?>