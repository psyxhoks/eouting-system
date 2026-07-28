<?php

// SMTP settings for outgoing email notifications (e.g. SOS alerts).
// Same pattern as config/db.php: values come from environment variables
// where possible, with local fallback defaults for development.
//
// If you use Gmail: turn on 2-Step Verification on the sending account,
// then create an "App Password" (Google Account > Security > App passwords)
// and use that as MAIL_PASSWORD -- your normal Gmail password will not work.

$mail_host       = getenv('MAIL_HOST') ?: "smtp.gmail.com";
$mail_port       = getenv('MAIL_PORT') ?: 587;
$mail_username   = getenv('MAIL_USERNAME') ?: "youraccount@gmail.com";
$mail_password   = getenv('MAIL_PASSWORD') ?: "";
$mail_encryption = getenv('MAIL_ENCRYPTION') ?: "tls"; // 'tls' or 'ssl'
$mail_from_email = getenv('MAIL_FROM_EMAIL') ?: $mail_username;
$mail_from_name   = getenv('MAIL_FROM_NAME') ?: "E-Outing System";

?>
