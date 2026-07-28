<?php

include '../config/error.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

include '../config/db.php';
include '../config/session.php';
include '../config/notification.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../login.php");
    exit();
}

// Check whether admin has disabled SOS for this student
$sos_disabled = false;
$check_stmt = mysqli_prepare($conn, "SELECT sos_disabled FROM users WHERE id=?");
mysqli_stmt_bind_param($check_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);
if($check_row = mysqli_fetch_assoc($check_result)) {
    $sos_disabled = !empty($check_row['sos_disabled']);
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

if(isset($_POST['submit']) && !$sos_disabled)
{
    $student_id = $_SESSION['user_id'];

    $message = trim($_POST['message'] ?? "");

    if(empty($message))
    {
        $message = "(No reason provided by student)";
    }

    $latitude = (isset($_POST['latitude']) && $_POST['latitude'] !== '') ? $_POST['latitude'] : null;
    $longitude = (isset($_POST['longitude']) && $_POST['longitude'] !== '') ? $_POST['longitude'] : null;
    $contact_number = trim($_POST['contact_number'] ?? "");

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO sos_alert
        (student_id, message, contact_number, latitude, longitude)
        VALUES
        (?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $student_id,
        $message,
        $contact_number,
        $latitude,
        $longitude
    );

    if(mysqli_stmt_execute($stmt))
    {
        // In-app notification: goes to every warden/admin login account
        $staff_sql =
        "
        SELECT id
        FROM users
        WHERE role='warden' OR role='admin'
        ";

        $staff_result =
        mysqli_query(
            $conn,
            $staff_sql
        );

        while(
            $staff =
            mysqli_fetch_assoc(
                $staff_result
            )
        )
        {
            createNotification(
                $conn,
                $staff['id'],
                "SOS Emergency",
                $_SESSION['fullname'] . " has sent an SOS emergency alert."
            );
        }

        // Email notification: goes to the real email addresses admin has
        // registered on the "SOS Notification Emails" list (Emergency
        // Management page), since login accounts like admin@kptm.edu.my
        // are not necessarily real inboxes.
        $notify_email_sql = "SELECT email, label FROM sos_notification_emails ORDER BY id";
        $notify_email_result = mysqli_query($conn, $notify_email_sql);

        while($notify_row = mysqli_fetch_assoc($notify_email_result))
        {
            sendSosEmail(
                $notify_row['email'],
                $notify_row['label'],
                $_SESSION['fullname'],
                $_SESSION['student_id'] ?? '',
                $contact_number,
                $message,
                $latitude,
                $longitude
            );
        }

        echo "<div class='alert alert-success'>
        SOS alert sent successfully. Help is on the way.
        </div>";
    }
    else
    {
        echo "<div class='alert alert-danger'>
        Failed to send SOS alert. Please try again or contact the hostel office directly.
        </div>";
    }
}
elseif(isset($_POST['submit']) && $sos_disabled)
{
    echo "<div class='alert alert-warning'>
    SOS reporting has been disabled for your account by the admin office. Please settle this matter directly with the hostel office or warden.
    </div>";
}

// Fetch KPTM staff emergency phone numbers to display to the student
$staff_contacts_result = mysqli_query($conn, "SELECT name, phone_number FROM staff_contacts ORDER BY name");

?>

<h1 class="mt-3">

SOS Emergency

</h1>

<p class="text-muted">

Use this only for genuine emergencies. Your location and message will be sent immediately to the warden and admin team.

</p>

<hr>

<div class="card mb-4">

<div class="card-body">

<h5 class="card-title mb-3"><i class="bi bi-telephone-fill text-danger me-2"></i>KPTM Staff Emergency Numbers</h5>

<?php if(mysqli_num_rows($staff_contacts_result) === 0) { ?>
<p class="text-muted mb-0">No staff phone numbers have been added yet.</p>
<?php } else { ?>
<ul class="list-group list-group-flush">
<?php while($contact = mysqli_fetch_assoc($staff_contacts_result)) { ?>
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <span><?php echo htmlspecialchars($contact['name']); ?></span>
        <a href="tel:<?php echo htmlspecialchars($contact['phone_number']); ?>" class="fw-semibold text-decoration-none">
            <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($contact['phone_number']); ?>
        </a>
    </li>
<?php } ?>
</ul>
<?php } ?>

</div>

</div>

<?php if($sos_disabled) { ?>

<div class="alert alert-secondary d-flex align-items-center gap-2">
    <i class="bi bi-info-circle"></i>
    SOS reporting is currently turned off for your account. Please contact the hostel office/warden directly, or use the staff numbers above, to settle this matter.
</div>

<?php } else { ?>

<div class="card border-danger">

<div class="card-body">

<div id="location-status" class="alert alert-secondary d-flex align-items-center gap-2">
<i class="bi bi-geo-alt"></i>
<span id="location-text">Getting your location...</span>
</div>

<form method="POST" id="sosForm">

<input type="hidden" name="latitude" id="latitude">
<input type="hidden" name="longitude" id="longitude">

<div class="mb-3">

<label class="form-label">

Your Contact Number <span class="text-danger">*</span>

</label>

<input
type="tel"
name="contact_number"
class="form-control"
placeholder="e.g. 012-3456789"
required>

<small class="text-muted">The admin/warden will use this number to call you back.</small>

</div>

<div class="mb-3">

<label class="form-label">

Emergency Message <span class="text-muted fw-normal">(optional)</span>

</label>

<textarea
name="message"
class="form-control"
rows="5"
placeholder="Briefly describe your emergency (e.g. medical emergency, safety threat, accident)... you may leave this blank and send SOS immediately."></textarea>

</div>

<button
type="submit"
name="submit"
class="btn btn-danger btn-lg w-100"
style="font-weight:700; letter-spacing:1px;">

<i class="bi bi-exclamation-triangle-fill me-2"></i>SEND SOS

</button>

</form>

</div>

</div>

<?php } ?>

<script>
(function() {
    var latField = document.getElementById('latitude');
    var lngField = document.getElementById('longitude');
    var statusBox = document.getElementById('location-status');
    var statusText = document.getElementById('location-text');

    if (!latField || !lngField || !statusBox || !statusText) {
        // SOS form is hidden (SOS disabled for this student)
        return;
    }

    if (!navigator.geolocation) {
        statusBox.classList.remove('alert-secondary');
        statusBox.classList.add('alert-warning');
        statusText.textContent = 'Location not supported on this device. You can still send SOS without location.';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            latField.value = position.coords.latitude;
            lngField.value = position.coords.longitude;
            statusBox.classList.remove('alert-secondary');
            statusBox.classList.add('alert-success');
            statusText.textContent = 'Location captured. It will be sent with your SOS alert.';
        },
        function(error) {
            statusBox.classList.remove('alert-secondary');
            statusBox.classList.add('alert-warning');
            statusText.textContent = 'Location unavailable (permission denied or unsupported). You can still send SOS without location.';
        },
        { enableHighAccuracy: true, timeout: 8000 }
    );
})();
</script>

<?php

include '../includes/footer.php';

?>
