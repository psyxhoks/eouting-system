-- Migration: student contact number on SOS alerts + email notification support
-- Run this against the existing database before using the updated pages.

-- 1. Column so the student can leave a phone number for admin/warden to call
--    back on. This is stored per-alert (not on the users table) so it always
--    reflects the number the student was reachable on at the time of that
--    specific emergency.
--
--    Plain ALTER TABLE is used here (no "IF NOT EXISTS") because that syntax
--    isn't supported on all MySQL/MariaDB versions and can also fail in some
--    web SQL consoles. If you get a "Duplicate column name" error, it just
--    means you already ran this once before -- safe to ignore and move on.
ALTER TABLE sos_alert
ADD COLUMN contact_number VARCHAR(20) NULL AFTER message;

-- 2. Table for admin to maintain a list of REAL email addresses that should
--    receive SOS alert emails. This is deliberately separate from
--    users.email, because login accounts (e.g. admin@kptm.edu.my,
--    warden@kptm.edu.my) may just be system credentials rather than real,
--    monitored inboxes. Admin adds/removes real staff emails here instead.
CREATE TABLE IF NOT EXISTS sos_notification_emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Note: no schema change is needed for the email notification feature itself
-- beyond the table above. SMTP settings are configured via environment
-- variables, see config/mail.php.
