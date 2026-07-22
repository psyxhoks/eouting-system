<?php
include '../config/error.php';
error_reporting(E_ALL);
ini_set('display_errors',1);

include '../config/db.php';
include '../config/session.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

$search = trim($_GET['search'] ?? "");
$status_filter = trim($_GET['status'] ?? "");

$sql = "SELECT * FROM compound WHERE 1=1";
$params = [];
$types = "";

if(!empty($search)) {
    $sql .= " AND student_id LIKE ? ";
    $params[] = "%".$search."%";
    $types .= "s";
}

if(!empty($status_filter)) {
    $sql .= " AND status=? ";
    $params[] = $status_filter;
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC ";
$stmt = mysqli_prepare($conn, $sql);

if(!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<h2 class="fw-bold mt-3">Compound Management</h2>
<p class="text-muted">Manage student compounds and payments.</p>

<?php if(isset($_GET['success']) && $_GET['success'] == 'delete') { ?>
<div class="alert alert-danger">Compound record deleted.</div>
<?php } ?>

<a href="issue_compound.php" class="btn btn-danger mb-3">
    <i class="bi bi-file-earmark-plus"></i> Issue Compound
</a>

<hr>

<form method="GET" class="mb-4">
    <div class="row g-2">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Search Student ID..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="Pending" <?= ($status_filter=="Pending") ? "selected" : "" ?>>Pending</option>
                <option value="Paid" <?= ($status_filter=="Paid") ? "selected" : "" ?>>Paid</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
        <div class="col-md-2">
            <a href="compound.php" class="btn btn-secondary">Reset</a>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>ID</th><th>Student ID</th><th>Reason</th><th>Document</th><th>Warning Given</th><th>Amount</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['student_id']) ?></td>
                            <td><?= htmlspecialchars($row['reason']) ?></td>
                            <td>
                                <?php if(!empty($row['evidence'])): ?>
                                    <a href="../uploads/<?= htmlspecialchars($row['evidence']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-text"></i> View
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['warning_given']): ?>
                                    <span class="badge bg-success">Yes, <?= htmlspecialchars($row['warning_date']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No</span>
                                <?php endif; ?>
                            </td>
                            <td>RM <?= number_format($row['amount'], 2) ?></td>
                            <td>
                                <?php if($row['status'] == "Pending"): ?>
                                    <span class="badge bg-warning text-dark px-3 py-2">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-success px-3 py-2">Paid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2 align-items-center">
                                    <?php if($row['status'] == "Pending"): ?>
                                        <a href="pay_compound.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Mark Paid</a>
                                    <?php else: ?>
                                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    <?php endif; ?>
                                    <a href="delete_compound.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this compound record? This cannot be undone.');">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>