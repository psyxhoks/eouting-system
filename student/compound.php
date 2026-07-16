<?php

include '../config/error.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

include '../config/db.php';
include '../config/session.php';

if(
!isset($_SESSION['user_id'])
||
$_SESSION['role']!='student'
)
{
    header("Location: ../login.php");
    exit();
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';



// Outstanding compound amount

$sql_total = "
SELECT
SUM(amount) total
FROM compound
WHERE student_id=?
AND status='Pending'
";

$stmt_total = mysqli_prepare(
    $conn,
    $sql_total
);

mysqli_stmt_bind_param(
    $stmt_total,
    "s",
    $_SESSION['student_id']
);

mysqli_stmt_execute(
    $stmt_total
);

$result_total =
mysqli_stmt_get_result(
    $stmt_total
);

$row_total =
mysqli_fetch_assoc(
    $result_total
);

$total_unpaid =
$row_total['total'] ?? 0;



// Compound history

$sql = "
SELECT *
FROM compound
WHERE student_id=?
ORDER BY created_at DESC
";

$stmt = mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $_SESSION['student_id']
);

mysqli_stmt_execute(
    $stmt
);

$result =
mysqli_stmt_get_result(
    $stmt
);

?>

<h2 class="fw-bold mt-3">

My Compound

</h2>

<p class="text-muted">

View disciplinary records and payment status.

</p>

<hr>



<div class="row mb-4">

<div class="col-md-4">

<div class="card border-0 shadow-sm rounded-4">

<div class="card-body text-center">

<h1
style="
color:
<?= ($total_unpaid>0)
?
'#DC3545'
:
'#28A745'
?>
">

RM <?= number_format($total_unpaid,2) ?>

</h1>

<p class="text-muted mb-0">

Outstanding Compound

</p>

</div>

</div>

</div>

</div>



<div class="card border-0 shadow-sm rounded-4">

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-light">

<tr>

<th>ID</th>

<th>Reason</th>

<th>Evidence</th>

<th>Amount</th>

<th>Status</th>

<th>Date</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result)) { ?>

<tr>

<td>

#<?= $row['id'] ?>

</td>

<td>

<?= $row['reason'] ?>

</td>

<td>

<?php if(!empty($row['evidence'])) { ?>
<a href="../uploads/<?= htmlspecialchars($row['evidence']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
<i class="bi bi-file-earmark-text"></i> View
</a>
<?php } else { ?>
<span class="text-muted">None</span>
<?php } ?>

</td>

<td>

RM <?= number_format($row['amount'],2) ?>

</td>

<td>

<?php

if($row['status']=="Pending")
{
?>

<span class="badge bg-danger">

Pending

</span>

<?php
}
else
{
?>

<span class="badge bg-success">

Paid

</span>

<?php
}

?>

</td>

<td>

<?= $row['created_at'] ?>

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