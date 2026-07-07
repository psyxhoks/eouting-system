<?php

include '../config/error.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

include '../config/db.php';
include '../config/session.php';

if(
    !isset($_SESSION['user_id'])
    ||
    $_SESSION['role']!='admin'
)
{
    header(
        "Location: ../login.php"
    );
    exit();
}

if(
    !isset($_GET['id'])
)
{
    header(
        "Location: user_management.php"
    );
    exit();
}

$id =
(int)
$_GET['id'];

$sql =
"
SELECT *
FROM users
WHERE id=?
";

$stmt =
mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute(
    $stmt
);

$result =
mysqli_stmt_get_result(
    $stmt
);

$row =
mysqli_fetch_assoc(
    $result
);

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

?>

<h2 class="fw-bold mt-3">

Edit User

</h2>

<p class="text-muted">

Update student information.

</p>

<hr>

<form
method="POST"
action="update_user.php">

<input
type="hidden"
name="id"
value="<?= $row['id'] ?>">

<div class="mb-3">

<label class="form-label">

Full Name

</label>

<input
type="text"
name="fullname"
class="form-control"
value="<?= $row['fullname'] ?>"
required>

</div>

<div class="mb-3">

<label class="form-label">

Email

</label>

<input
type="email"
name="email"
class="form-control"
value="<?= $row['email'] ?>"
required>

</div>

<div class="mb-3">

<label class="form-label">

Programme

</label>

<select
name="programme"
class="form-select">

<option
value="CT103"
<?= ($row['programme']=="CT103") ? "selected" : "" ?>>

CT103 - Information Technology

</option>

<option
value="CT108"
<?= ($row['programme']=="CT108") ? "selected" : "" ?>>

CT108 - Graphic Design

</option>

<option
value="AB107"
<?= ($row['programme']=="AB107") ? "selected" : "" ?>>

AB107 - Business Administration

</option>

<option
value="CC104"
<?= ($row['programme']=="CC104") ? "selected" : "" ?>>

CC104 - Computer Science

</option>

</select>

</div>

<div class="mb-3">

<label class="form-label">

Intake

</label>

<input
type="text"
name="cohort"
class="form-control"
value="<?= $row['cohort'] ?>">

</div>

<div class="mb-3">

<label class="form-label">

Status

</label>

<select
name="status"
class="form-select">

<option
value="Active"
<?= ($row['status']=="Active") ? "selected" : "" ?>>

Active

</option>

<option
value="Graduated"
<?= ($row['status']=="Graduated") ? "selected" : "" ?>>

Graduated

</option>

<option
value="Inactive"
<?= ($row['status']=="Inactive") ? "selected" : "" ?>>

Inactive

</option>

</select>

</div>

<button
type="submit"
class="btn btn-primary">

Save Changes

</button>

<a
href="user_management.php"
class="btn btn-secondary">

Cancel

</a>

</form>

<?php include '../includes/footer.php'; ?>