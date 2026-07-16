<?php
include '../config/error.php';
error_reporting(E_ALL);
ini_set('display_errors',1);

include '../config/db.php';
include '../config/session.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin') {
    header("Location: ../login.php");
    exit();
}

$search = trim($_GET['search'] ?? "");
$programme = trim($_GET['programme'] ?? "");
$cohort = trim($_GET['cohort'] ?? "");

$limit = 20;
$page = (int)($_GET['page'] ?? 1);
$offset = max(0, ($page - 1) * $limit);

$keyword = "%".$search."%";
$programme_filter = "%".$programme."%";
$cohort_filter = "%".$cohort."%";

// Main Query
$sql = "SELECT id, student_id, fullname, email, role, programme, cohort, status FROM users WHERE (student_id LIKE ? OR fullname LIKE ?) AND programme LIKE ? AND cohort LIKE ? ORDER BY fullname LIMIT ?, ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssii", $keyword, $keyword, $programme_filter, $cohort_filter, $offset, $limit);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Count Query
$count_sql = "SELECT COUNT(*) AS total FROM users WHERE (student_id LIKE ? OR fullname LIKE ?) AND programme LIKE ? AND cohort LIKE ?";
$count_stmt = mysqli_prepare($conn, $count_sql);
mysqli_stmt_bind_param($count_stmt, "ssss", $keyword, $keyword, $programme_filter, $cohort_filter);
mysqli_stmt_execute($count_stmt);
$total_rows = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];
$total_pages = max(1, ceil($total_rows / $limit));

// Dropdowns
$programme_result = mysqli_query($conn, "SELECT DISTINCT programme FROM users WHERE programme <> '' ORDER BY programme");
$cohort_result = mysqli_query($conn, "SELECT DISTINCT cohort FROM users WHERE cohort <> '' ORDER BY cohort");

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<h2 class="fw-bold mt-3">User Management</h2>
<p class="text-muted">Manage student and system users.</p>
<hr>

<?php if(isset($_GET['success'])): ?>
    <?php if($_GET['success'] == "reset"): ?> <div class="alert alert-success">Password reset to <b>student123</b>.</div>
    <?php elseif($_GET['success'] == "delete"): ?> <div class="alert alert-danger">User deleted.</div>
    <?php elseif($_GET['success'] == "deactivate"): ?> <div class="alert alert-warning">User marked Inactive.</div>
    <?php elseif($_GET['success'] == "graduate"): ?> <div class="alert alert-secondary">Student marked Graduated.</div>
    <?php elseif($_GET['success'] == "batch_activate"): ?> <div class="alert alert-success">Intake marked Active.</div>
    <?php elseif($_GET['success'] == "batch_graduate"): ?> <div class="alert alert-secondary">Intake marked Graduated.</div>
    <?php elseif($_GET['success'] == "bulk_activate"): ?> <div class="alert alert-success">Selected users marked Active.</div>
    <?php elseif($_GET['success'] == "bulk_deactivate"): ?> <div class="alert alert-warning">Selected users marked Inactive.</div>
    <?php elseif($_GET['success'] == "bulk_graduate"): ?> <div class="alert alert-secondary">Selected users marked Graduated.</div>
    <?php elseif($_GET['success'] == "bulk_reset"): ?> <div class="alert alert-success">Password reset for selected users.</div>
    <?php elseif($_GET['success'] == "update"): ?> <div class="alert alert-success">User updated.</div>
    <?php endif; ?>
<?php endif; ?>

<form method="GET">
    <div class="row mb-4">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Search ID or Name" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="programme" class="form-select">
                <option value="">All Programmes</option>
                <?php while($prog_row = mysqli_fetch_assoc($programme_result)): ?>
                    <option value="<?= $prog_row['programme'] ?>" <?= ($programme==$prog_row['programme']) ? "selected" : "" ?>><?= $prog_row['programme'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="cohort" class="form-select">
                <option value="">All Intake</option>
                <?php while($coh_row = mysqli_fetch_assoc($cohort_result)): ?>
                    <option value="<?= $coh_row['cohort'] ?>" <?= ($cohort==$coh_row['cohort']) ? "selected" : "" ?>><?= $coh_row['cohort'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
                <a href="graduate_batch.php?programme=<?= urlencode($programme) ?>&cohort=<?= urlencode($cohort) ?>" class="btn btn-secondary" onclick="return confirm('Graduate selected?')">Graduate Intake</a>
                <a href="activate_batch.php?programme=<?= urlencode($programme) ?>&cohort=<?= urlencode($cohort) ?>" class="btn btn-success" onclick="return confirm('Activate selected?')">Activate Intake</a>
            </div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">

        <form method="POST" action="bulk_action.php" id="bulkForm" onsubmit="return validateBulkForm()">

        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="submit" name="bulk_action" value="activate" class="btn btn-sm btn-success">
                <i class="bi bi-check-circle"></i> Activate Selected
            </button>
            <button type="submit" name="bulk_action" value="deactivate" class="btn btn-sm btn-danger">
                <i class="bi bi-slash-circle"></i> Deactivate Selected
            </button>
            <button type="submit" name="bulk_action" value="graduate" class="btn btn-sm btn-secondary">
                <i class="bi bi-mortarboard"></i> Graduate Selected
            </button>
            <button type="submit" name="bulk_action" value="reset_password" class="btn btn-sm btn-info">
                <i class="bi bi-key"></i> Reset Password for Selected
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes(this)"></th>
                        <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Programme</th><th>Intake</th><th>Status</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><input type="checkbox" name="user_ids[]" value="<?= $row['id'] ?>" class="row-checkbox"></td>
                            <td><?= htmlspecialchars($row['student_id'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['fullname'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                            <td><?= ucfirst(htmlspecialchars($row['role'] ?? '')) ?></td>
                            <td><?= htmlspecialchars($row['programme'] ?? '-') ?></td>
                            <td>
                                <?php if(empty($row['cohort'])): ?><span class="badge bg-danger">No Intake</span>
                                <?php else: echo htmlspecialchars($row['cohort']); endif; ?>
                            </td>
                            <td>
                                <?php if(($row['status'] ?? '') == "Active"): ?><span class="badge bg-success">Active</span>
                                <?php elseif(($row['status'] ?? '') == "Graduated"): ?><span class="badge bg-secondary">Graduated</span>
                                <?php else: ?><span class="badge bg-danger">Inactive</span><?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                    <a href="reset_password.php?id=<?= $row['id'] ?>" class="btn btn-outline-info"><i class="bi bi-key"></i></a>
                                    <a href="graduate_user.php?id=<?= $row['id'] ?>" class="btn btn-outline-secondary"><i class="bi bi-mortarboard"></i></a>
                                    <a href="delete_user.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <?php if($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if($page > 1): ?><li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>">Prev</a></li><?php endif; ?>
                    <?php for($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
                        <li class="page-item <?= ($page==$i) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <?php if($page < $total_pages): ?><li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>">Next</a></li><?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>

        </form>

    </div>
</div>

<script>
function toggleAllCheckboxes(source) {
    var checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(function(cb) { cb.checked = source.checked; });
}

function validateBulkForm() {
    var checked = document.querySelectorAll('.row-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one user first.');
        return false;
    }
    return confirm('Apply this action to ' + checked.length + ' selected user(s)?');
}
</script>

<?php include '../includes/footer.php'; ?>