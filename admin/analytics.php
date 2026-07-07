<?php

include '../config/error.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

include '../config/db.php';
include '../config/session.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin')
{
    header("Location: ../login.php");
    exit();
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

$total_outing = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) total FROM outing_request"
    )
)['total'];

$total_sos = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) total FROM sos_alert"
    )
)['total'];

$total_compound = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) total FROM compound"
    )
)['total'];

$total_outside = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) total
        FROM outing_request
        WHERE current_status='Outside'"
    )
)['total'];

?>

<div class="row g-4 mb-4">

<div class="col-md-3">

<div class="card">

<div class="card-body text-center">

<i
class="bi bi-calendar-check"
style="
font-size:45px;
color:#3498DB;
">
</i>

<h1 class="mt-3">

<?= $total_outing ?>

</h1>

<p class="text-muted">

Total Outings

</p>

</div>

</div>

</div>



<div class="col-md-3">

<div class="card">

<div class="card-body text-center">

<i
class="bi bi-exclamation-triangle"
style="
font-size:45px;
color:#E74C3C;
">
</i>

<h1 class="mt-3">

<?= $total_sos ?>

</h1>

<p class="text-muted">

SOS Cases

</p>

</div>

</div>

</div>



<div class="col-md-3">

<div class="card">

<div class="card-body text-center">

<i
class="bi bi-file-earmark-text"
style="
font-size:45px;
color:#F39C12;
">
</i>

<h1 class="mt-3">

<?= $total_compound ?>

</h1>

<p class="text-muted">

Compounds

</p>

</div>

</div>

</div>



<div class="col-md-3">

<div class="card">

<div class="card-body text-center">

<i
class="bi bi-people-fill"
style="
font-size:45px;
color:#27AE60;
">
</i>

<h1 class="mt-3">

<?= $total_outside ?>

</h1>

<p class="text-muted">

Students Outside

</p>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>