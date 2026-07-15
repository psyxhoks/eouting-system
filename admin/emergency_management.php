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

?>

<h1 class="mt-3">

Emergency Management

</h1>

<hr>

<div class="card">

<div class="card-body">

<h4>

Emergency Response Center

</h4>

<p>

This module will be used to:

</p>

<ul>

<li>

Monitor SOS alerts.

</li>

<li>

Manage emergency situations.

</li>

<li>

Track active cases.

</li>

<li>

Coordinate emergency responses.

</li>

<li>

Maintain emergency records.

</li>

</ul>

<div class="alert alert-warning">

Emergency Management module is currently under development.

</div>

</div>

</div>

<?php

include '../includes/footer.php';

?>