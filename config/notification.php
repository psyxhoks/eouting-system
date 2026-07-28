<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Emails a single staff member (warden/admin) about an SOS alert.
 * Any failure is caught and logged with error_log() so it never breaks
 * the SOS submission flow for the student.
 */
function sendSosEmail(
    $staff_email,
    $staff_name,
    $student_name,
    $student_matric,
    $contact_number,
    $message,
    $latitude,
    $longitude
)
{
    global $mail_host, $mail_port, $mail_username, $mail_password,
           $mail_encryption, $mail_from_email, $mail_from_name;

    if(empty($staff_email))
    {
        return false;
    }

    $mail = new PHPMailer(true);

    try
    {
        $mail->isSMTP();
        $mail->Host       = $mail_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $mail_username;
        $mail->Password   = $mail_password;
        $mail->SMTPSecure = $mail_encryption === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $mail_port;

        $mail->setFrom($mail_from_email, $mail_from_name);
        $mail->addAddress($staff_email, $staff_name);

        $mail->isHTML(true);
        $mail->Subject = "SOS Emergency Alert - " . $student_name;

        $map_line = "Location not shared by student.";
        if(!empty($latitude) && !empty($longitude))
        {
            $map_url  = "https://www.google.com/maps?q=" . $latitude . "," . $longitude;
            $map_line = "<a href=\"" . htmlspecialchars($map_url) . "\">View location on Google Maps</a>";
        }

        $contact_line = !empty($contact_number)
            ? htmlspecialchars($contact_number)
            : "Not provided";

        $mail->Body =
            "<h2 style='color:#dc3545;'>SOS Emergency Alert</h2>" .
            "<p><strong>Student:</strong> " . htmlspecialchars($student_name) . " (" . htmlspecialchars($student_matric) . ")</p>" .
            "<p><strong>Student contact number:</strong> " . $contact_line . "</p>" .
            "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>" .
            "<p><strong>Location:</strong> " . $map_line . "</p>" .
            "<p style='color:#666;font-size:13px;'>Please log in to the E-Outing system for full details and to mark this alert as resolved.</p>";

        $mail->AltBody =
            "SOS Emergency Alert\n" .
            "Student: " . $student_name . " (" . $student_matric . ")\n" .
            "Student contact number: " . ($contact_number ?: "Not provided") . "\n" .
            "Message: " . $message . "\n" .
            (!empty($latitude) && !empty($longitude)
                ? "Location: https://www.google.com/maps?q=" . $latitude . "," . $longitude . "\n"
                : "Location: Not shared by student.\n");

        $mail->send();
        return true;
    }
    catch(Exception $e)
    {
        error_log("SOS email to {$staff_email} failed: " . $mail->ErrorInfo);
        return false;
    }
}

function createNotification(
    $conn,
    $user_id,
    $title,
    $message
)
{
    $sql =
    "
    INSERT INTO notifications
    (
        user_id,
        title,
        message
    )
    VALUES
    (
        ?,
        ?,
        ?
    )
    ";

    $stmt =
    mysqli_prepare(
        $conn,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "iss",
        $user_id,
        $title,
        $message
    );

    mysqli_stmt_execute(
        $stmt
    );
}

?>