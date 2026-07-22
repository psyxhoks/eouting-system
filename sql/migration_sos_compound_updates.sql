-- Migration: SOS staff contacts, per-student SOS disable, compound delete
-- Run this against the existing database before using the updated pages.

-- 1. Table to store KPTM staff phone numbers shown on the student SOS page.
CREATE TABLE IF NOT EXISTS staff_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone_number VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Column so admin can disable SOS for individual students (cases they choose
--    to have the student settle on their own instead of via SOS).
ALTER TABLE users
ADD COLUMN IF NOT EXISTS sos_disabled TINYINT(1) NOT NULL DEFAULT 0;

-- Note: admin/compound.php's new "Delete" button removes a compound record
-- with a hard DELETE FROM compound WHERE id=?, so no schema change is needed
-- for that feature.
