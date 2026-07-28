<?php

include '../config/error.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

include '../config/db.php';
include '../config/session.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'warden')
{
    header("Location: ../login.php");
    exit();
}

// Handle resolving an alert
if(isset($_POST['resolve_id']))
{
    $resolve_id = $_POST['resolve_id'];
    $resolver = $_SESSION['user_id'];

    $update_stmt = mysqli_prepare(
        $conn,
        "UPDATE sos_alert SET status='Resolved', resolved_by=?, resolved_at=NOW() WHERE id=?"
    );
    mysqli_stmt_bind_param($update_stmt, "ii", $resolver, $resolve_id);
    mysqli_stmt_execute($update_stmt);

    header("Location: sos_alert.php");
    exit();
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

$sql = "
SELECT sos_alert.*, users.fullname, users.student_id AS matric_no, users.programme
FROM sos_alert
JOIN users ON sos_alert.student_id = users.id
ORDER BY (sos_alert.status = 'Open') DESC, sos_alert.id DESC
";

$result = mysqli_query($conn,$sql);

?>

<h2 class="fw-bold mt-3">

SOS Alerts

</h2>

<p class="text-muted">

Monitor emergency requests submitted by students.

</p>

<hr>

<div class="card">

<div class="card-body">

<div class="table-responsive">

<table class="table align-middle">

<thead>

<tr>

<th>ID</th>

<th>Student</th>

<th>Emergency Message</th>

<th>Contact Number</th>

<th>Location</th>

<th>Time</th>

<th>Status</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result)) { ?>

<tr>

<td>
#<?php echo $row['id']; ?>
</td>

<td>
<div class="fw-semibold"><?php echo htmlspecialchars($row['fullname']); ?></div>
<small class="text-muted"><?php echo htmlspecialchars($row['matric_no']); ?> &middot; <?php echo htmlspecialchars($row['programme']); ?></small>
</td>

<td>
<?php echo nl2br(htmlspecialchars($row['message'])); ?>
</td>

<td>
<?php if(!empty($row['contact_number'])) { ?>
    <a href="tel:<?php echo htmlspecialchars($row['contact_number']); ?>" class="fw-semibold text-decoration-none">
        <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($row['contact_number']); ?>
    </a>
<?php } else { ?>
    <span class="text-muted">Not provided</span>
<?php } ?>
</td>

<td>
<?php if(!empty($row['latitude']) && !empty($row['longitude'])) { ?>
    <a href="https://www.google.com/maps?q=<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-geo-alt-fill"></i> View Map
    </a>
<?php } else { ?>
    <span class="text-muted">Not shared</span>
<?php } ?>
</td>

<td>
<small><?php echo isset($row['created_at']) ? date('d M Y, h:i A', strtotime($row['created_at'])) : '-'; ?></small>
</td>

<td>

<?php

if($row['status']=="Open")
{
?>

<span class="badge badge-open px-3 py-2">

Open

</span>

<?php
}
else
{
?>

<span class="badge badge-closed px-3 py-2">

Resolved

</span>

<?php
}

?>

</td>

<td>
<?php if($row['status']=="Open") { ?>
    <form method="POST" onsubmit="return confirm('Mark this SOS alert as resolved?');">
        <input type="hidden" name="resolve_id" value="<?php echo $row['id']; ?>">
        <button type="submit" class="btn btn-sm btn-success">
            <i class="bi bi-check-circle"></i> Resolve
        </button>
    </form>
<?php } else { ?>
    <span class="text-muted">&mdash;</span>
<?php } ?>
</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<?php

include '../includes/footer.php';

?>
