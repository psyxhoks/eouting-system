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

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

if(isset($_POST['submit']))
{
    $student_id = $_SESSION['user_id'];

    $message = trim($_POST['message']);

    $latitude = (isset($_POST['latitude']) && $_POST['latitude'] !== '') ? $_POST['latitude'] : null;
    $longitude = (isset($_POST['longitude']) && $_POST['longitude'] !== '') ? $_POST['longitude'] : null;

    if(empty($message))
    {
        echo "<div class='alert alert-danger'>
        Message cannot be empty.
        </div>";
    }
    else
    {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO sos_alert
            (student_id, message, latitude, longitude)
            VALUES
            (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $student_id,
            $message,
            $latitude,
            $longitude
        );

        if(mysqli_stmt_execute($stmt))
        {
            // Notify all wardens and admins
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
}

?>

<h1 class="mt-3">

SOS Emergency

</h1>

<p class="text-muted">

Use this only for genuine emergencies. Your location and message will be sent immediately to the warden and admin team.

</p>

<hr>

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

Emergency Message

</label>

<textarea
name="message"
class="form-control"
rows="5"
placeholder="Briefly describe your emergency (e.g. medical emergency, safety threat, accident)..."
required></textarea>

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

<script>
(function() {
    var latField = document.getElementById('latitude');
    var lngField = document.getElementById('longitude');
    var statusBox = document.getElementById('location-status');
    var statusText = document.getElementById('location-text');

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
