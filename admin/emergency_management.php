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

    header("Location: emergency_management.php");
    exit();
}

// Handle adding a staff phone number
if(isset($_POST['add_contact']))
{
    $contact_name = trim($_POST['contact_name'] ?? "");
    $contact_phone = trim($_POST['contact_phone'] ?? "");

    if(!empty($contact_name) && !empty($contact_phone))
    {
        $add_stmt = mysqli_prepare(
            $conn,
            "INSERT INTO staff_contacts (name, phone_number) VALUES (?, ?)"
        );
        mysqli_stmt_bind_param($add_stmt, "ss", $contact_name, $contact_phone);
        mysqli_stmt_execute($add_stmt);
    }

    header("Location: emergency_management.php#staff-contacts");
    exit();
}

// Handle deleting a staff phone number
if(isset($_POST['delete_contact_id']))
{
    $delete_contact_id = (int)$_POST['delete_contact_id'];

    $delete_stmt = mysqli_prepare(
        $conn,
        "DELETE FROM staff_contacts WHERE id=?"
    );
    mysqli_stmt_bind_param($delete_stmt, "i", $delete_contact_id);
    mysqli_stmt_execute($delete_stmt);

    header("Location: emergency_management.php#staff-contacts");
    exit();
}

// Handle turning SOS on/off for a specific student
if(isset($_POST['toggle_sos_id']))
{
    $toggle_id = (int)$_POST['toggle_sos_id'];
    $new_state = (int)$_POST['new_state'];

    $toggle_stmt = mysqli_prepare(
        $conn,
        "UPDATE users SET sos_disabled=? WHERE id=? AND role='student'"
    );
    mysqli_stmt_bind_param($toggle_stmt, "ii", $new_state, $toggle_id);
    mysqli_stmt_execute($toggle_stmt);

    $redirect_search = isset($_POST['student_search']) ? "&student_search=" . urlencode($_POST['student_search']) : "";
    header("Location: emergency_management.php" . "?ok=1" . $redirect_search . "#sos-access");
    exit();
}

// Handle adding an SOS notification email
if(isset($_POST['add_notify_email']))
{
    $notify_label = trim($_POST['notify_label'] ?? "");
    $notify_email = trim($_POST['notify_email'] ?? "");

    if(!empty($notify_label) && filter_var($notify_email, FILTER_VALIDATE_EMAIL))
    {
        $add_email_stmt = mysqli_prepare(
            $conn,
            "INSERT INTO sos_notification_emails (label, email) VALUES (?, ?)"
        );
        mysqli_stmt_bind_param($add_email_stmt, "ss", $notify_label, $notify_email);
        mysqli_stmt_execute($add_email_stmt);
    }

    header("Location: emergency_management.php#notify-emails");
    exit();
}

// Handle deleting an SOS notification email
if(isset($_POST['delete_notify_email_id']))
{
    $delete_email_id = (int)$_POST['delete_notify_email_id'];

    $delete_email_stmt = mysqli_prepare(
        $conn,
        "DELETE FROM sos_notification_emails WHERE id=?"
    );
    mysqli_stmt_bind_param($delete_email_stmt, "i", $delete_email_id);
    mysqli_stmt_execute($delete_email_stmt);

    header("Location: emergency_management.php#notify-emails");
    exit();
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

$open_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sos_alert WHERE status='Open'");
$open_count = mysqli_fetch_assoc($open_count_result)['total'];

$sql = "
SELECT sos_alert.*, users.fullname, users.student_id AS matric_no, users.programme
FROM sos_alert
JOIN users ON sos_alert.student_id = users.id
ORDER BY (sos_alert.status = 'Open') DESC, sos_alert.id DESC
";

$result = mysqli_query($conn,$sql);

// Staff emergency contacts
$contacts_result = mysqli_query($conn, "SELECT * FROM staff_contacts ORDER BY name");

// SOS notification emails (real inboxes that receive SOS alert emails)
$notify_emails_result = mysqli_query($conn, "SELECT * FROM sos_notification_emails ORDER BY id");

// Students for SOS access control
$student_search = trim($_GET['student_search'] ?? "");
$student_keyword = "%".$student_search."%";

$students_stmt = mysqli_prepare(
    $conn,
    "SELECT id, fullname, student_id, sos_disabled
    FROM users
    WHERE role='student' AND (fullname LIKE ? OR student_id LIKE ?)
    ORDER BY fullname
    LIMIT 20"
);
mysqli_stmt_bind_param($students_stmt, "ss", $student_keyword, $student_keyword);
mysqli_stmt_execute($students_stmt);
$students_result = mysqli_stmt_get_result($students_stmt);

?>

<h1 class="mt-3">

Emergency Management

</h1>

<p class="text-muted">

Monitor and respond to SOS alerts submitted by students, including those living off-campus (rumah sewa).

</p>

<hr>

<?php if($open_count > 0) { ?>
<div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong><?php echo $open_count; ?></strong> emergency alert<?php echo $open_count > 1 ? 's' : ''; ?> currently unresolved.
</div>
<?php } else { ?>
<div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i>
    No open emergency alerts right now.
</div>
<?php } ?>

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

<?php if(mysqli_num_rows($result) === 0) { ?>
<tr>
    <td colspan="8" class="text-center text-muted py-4">No SOS alerts have been submitted yet.</td>
</tr>
<?php } ?>

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

<hr class="my-4">

<div id="staff-contacts" class="card mb-4">

<div class="card-body">

<h4 class="fw-bold mb-1">KPTM Staff Emergency Numbers</h4>
<p class="text-muted">These numbers are shown to students on the SOS page.</p>

<?php if(isset($_GET['ok'])) { ?>
<div class="alert alert-success py-2">Saved.</div>
<?php } ?>

<div class="table-responsive mb-3">
<table class="table align-middle">
<thead>
<tr><th>Name</th><th>Phone Number</th><th>Action</th></tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($contacts_result) === 0) { ?>
<tr><td colspan="3" class="text-center text-muted py-3">No staff phone numbers added yet.</td></tr>
<?php } ?>
<?php while($contact = mysqli_fetch_assoc($contacts_result)) { ?>
<tr>
    <td><?php echo htmlspecialchars($contact['name']); ?></td>
    <td><?php echo htmlspecialchars($contact['phone_number']); ?></td>
    <td>
        <form method="POST" onsubmit="return confirm('Delete this phone number?');">
            <input type="hidden" name="delete_contact_id" value="<?php echo $contact['id']; ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash"></i> Delete
            </button>
        </form>
    </td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<form method="POST" class="row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label">Staff Name</label>
        <input type="text" name="contact_name" class="form-control" placeholder="e.g. Warden Ahmad" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Phone Number</label>
        <input type="text" name="contact_phone" class="form-control" placeholder="e.g. 013-3535488" required>
    </div>
    <div class="col-md-3">
        <button type="submit" name="add_contact" class="btn btn-primary w-100">
            <i class="bi bi-plus-circle"></i> Add Number
        </button>
    </div>
</form>

</div>

</div>

<div id="notify-emails" class="card mb-4">

<div class="card-body">

<h4 class="fw-bold mb-1">SOS Notification Emails</h4>
<p class="text-muted">
Real email addresses that receive an email whenever a student sends an SOS alert.
This is separate from staff login accounts (e.g. <code>admin@kptm.edu.my</code>) since
those may just be system credentials rather than inboxes anyone actually checks &mdash;
add the real staff emails that should be alerted here.
</p>

<?php if(isset($_GET['ok'])) { ?>
<div class="alert alert-success py-2">Saved.</div>
<?php } ?>

<div class="table-responsive mb-3">
<table class="table align-middle">
<thead>
<tr><th>Label / Staff Name</th><th>Email</th><th>Action</th></tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($notify_emails_result) === 0) { ?>
<tr><td colspan="3" class="text-center text-muted py-3">No notification emails added yet. SOS emails will not be sent to anyone until you add at least one.</td></tr>
<?php } ?>
<?php while($notify_email = mysqli_fetch_assoc($notify_emails_result)) { ?>
<tr>
    <td><?php echo htmlspecialchars($notify_email['label']); ?></td>
    <td><?php echo htmlspecialchars($notify_email['email']); ?></td>
    <td>
        <form method="POST" onsubmit="return confirm('Remove this email from SOS notifications?');">
            <input type="hidden" name="delete_notify_email_id" value="<?php echo $notify_email['id']; ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash"></i> Remove
            </button>
        </form>
    </td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<form method="POST" class="row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label">Label / Staff Name</label>
        <input type="text" name="notify_label" class="form-control" placeholder="e.g. Warden Ahmad" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Email Address</label>
        <input type="email" name="notify_email" class="form-control" placeholder="e.g. ahmad@kptm.edu.my" required>
    </div>
    <div class="col-md-3">
        <button type="submit" name="add_notify_email" class="btn btn-primary w-100">
            <i class="bi bi-plus-circle"></i> Add Email
        </button>
    </div>
</form>

</div>

</div>

<div id="sos-access" class="card mb-4">

<div class="card-body">

<h4 class="fw-bold mb-1">Student SOS Access Control</h4>
<p class="text-muted">Turn SOS off for a specific student if the case should be settled directly with the office instead. The student can still see staff phone numbers.</p>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="student_search" class="form-control" placeholder="Search by name or student ID..." value="<?php echo htmlspecialchars($student_search); ?>">
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-outline-primary">Search</button>
    </div>
</form>

<div class="table-responsive">
<table class="table align-middle">
<thead>
<tr><th>Student ID</th><th>Name</th><th>SOS Status</th><th>Action</th></tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($students_result) === 0) { ?>
<tr><td colspan="4" class="text-center text-muted py-3">No students found.</td></tr>
<?php } ?>
<?php while($student = mysqli_fetch_assoc($students_result)) { ?>
<tr>
    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
    <td><?php echo htmlspecialchars($student['fullname']); ?></td>
    <td>
        <?php if($student['sos_disabled']) { ?>
            <span class="badge bg-secondary">Disabled</span>
        <?php } else { ?>
            <span class="badge bg-success">Enabled</span>
        <?php } ?>
    </td>
    <td>
        <form method="POST" onsubmit="return confirm('<?php echo $student['sos_disabled'] ? 'Re-enable' : 'Disable'; ?> SOS for this student?');">
            <input type="hidden" name="toggle_sos_id" value="<?php echo $student['id']; ?>">
            <input type="hidden" name="new_state" value="<?php echo $student['sos_disabled'] ? 0 : 1; ?>">
            <input type="hidden" name="student_search" value="<?php echo htmlspecialchars($student_search); ?>">
            <?php if($student['sos_disabled']) { ?>
                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-circle"></i> Enable SOS</button>
            <?php } else { ?>
                <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-slash-circle"></i> Disable SOS</button>
            <?php } ?>
        </form>
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
