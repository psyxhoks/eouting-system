<?php

include '../config/error.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

include '../config/db.php';
include '../config/session.php';
include '../config/notification.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$errors = [];
$success = false;

if(isset($_POST['submit']))
{
    $student_id = trim($_POST['student_id'] ?? "");
    $reason = trim($_POST['reason'] ?? "");
    $amount = trim($_POST['amount'] ?? "");
    $warning_given = isset($_POST['warning_given']) ? 1 : 0;
    $warning_date = trim($_POST['warning_date'] ?? "");

    // Validate student exists
    $student_row = null;
    if(!empty($student_id)) {
        $check_stmt = mysqli_prepare($conn, "SELECT id, fullname, student_id FROM users WHERE student_id=? AND role='student'");
        mysqli_stmt_bind_param($check_stmt, "s", $student_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $student_row = mysqli_fetch_assoc($check_result);
    }

    if(empty($student_id) || !$student_row) {
        $errors[] = "Please select a valid student.";
    }
    if(empty($reason)) {
        $errors[] = "Reason is required.";
    }
    if(empty($amount) || !is_numeric($amount) || $amount <= 0) {
        $errors[] = "Please enter a valid compound amount.";
    }
    if($warning_given != 1) {
        $errors[] = "You must confirm that a verbal warning has already been given before a compound can be issued.";
    }
    if($warning_given == 1 && empty($warning_date)) {
        $errors[] = "Please enter the date the verbal warning was given.";
    }

    $evidence_filename = null;
    if(isset($_FILES['evidence']) && $_FILES['evidence']['error'] == 0)
    {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext = strtolower(pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION));

        if(!in_array($ext, $allowed_ext)) {
            $errors[] = "Document must be a JPG, PNG, or PDF file.";
        } elseif($_FILES['evidence']['size'] > 5 * 1024 * 1024) {
            $errors[] = "Document must be smaller than 5MB.";
        } else {
            $evidence_filename = time() . "_" . basename($_FILES['evidence']['name']);
            $target = "../uploads/" . $evidence_filename;
            if(!move_uploaded_file($_FILES['evidence']['tmp_name'], $target)) {
                $errors[] = "Failed to upload document. Please try again.";
                $evidence_filename = null;
            }
        }
    }

    if(empty($errors))
    {
        $insert_stmt = mysqli_prepare(
            $conn,
            "INSERT INTO compound (student_id, reason, evidence, amount, warning_given, warning_date)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $insert_stmt,
            "sssdis",
            $student_id,
            $reason,
            $evidence_filename,
            $amount,
            $warning_given,
            $warning_date
        );

        if(mysqli_stmt_execute($insert_stmt))
        {
            createNotification(
                $conn,
                $student_row['id'],
                "Compound Issued",
                "You have been issued a compound of RM " . number_format($amount, 2) . ". Reason: " . $reason
            );

            $success = true;
        }
        else
        {
            $errors[] = "Failed to save compound record. Please try again.";
        }
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

// Get all students for the searchable dropdown
$students_result = mysqli_query($conn, "SELECT student_id, fullname, programme FROM users WHERE role='student' ORDER BY fullname ASC");

?>

<h2 class="fw-bold mt-3">Issue Compound</h2>
<p class="text-muted">Create a new disciplinary compound record for a student.</p>
<hr>

<?php if($success) { ?>
<div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i>
    Compound issued successfully. The student has been notified.
</div>
<div class="mb-4">
    <a href="issue_compound.php" class="btn btn-outline-primary btn-sm">Issue Another</a>
    <a href="compound.php" class="btn btn-primary btn-sm">Back to Compound Management</a>
</div>
<?php } ?>

<?php if(!empty($errors)) { ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach($errors as $error) { ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php } ?>
    </ul>
</div>
<?php } ?>

<?php if(!$success) { ?>

<div class="card border-0 shadow-sm rounded-4">
<div class="card-body">

<form method="POST" enctype="multipart/form-data">

<div class="mb-3">
    <label class="form-label fw-semibold">Student</label>
    <input list="studentList" name="student_id" class="form-control" placeholder="Search by student ID or name..." value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>" required>
    <datalist id="studentList">
        <?php while($s = mysqli_fetch_assoc($students_result)) { ?>
            <option value="<?= htmlspecialchars($s['student_id']) ?>">
                <?= htmlspecialchars($s['fullname']) ?> &mdash; <?= htmlspecialchars($s['programme']) ?>
            </option>
        <?php } ?>
    </datalist>
    <small class="text-muted">Type the student's ID exactly as shown, or select from the suggestions.</small>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Reason</label>
    <textarea name="reason" class="form-control" rows="3" required placeholder="Describe the offense..."><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Amount (RM)</label>
    <input type="number" step="0.01" min="0" name="amount" class="form-control" value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>" required>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Supporting Document (optional)</label>
    <input type="file" name="evidence" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
    <small class="text-muted">JPG, PNG, or PDF. Max 5MB. E.g. photo evidence or the compound notice itself.</small>
</div>

<hr>

<div class="alert alert-warning">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="warning_given" id="warningGiven" value="1" <?= (isset($_POST['warning_given'])) ? 'checked' : '' ?> required>
        <label class="form-check-label fw-semibold" for="warningGiven">
            I confirm this student has already received a verbal warning for this offense before this compound is being issued.
        </label>
    </div>
    <div class="mt-3">
        <label class="form-label fw-semibold">Date Verbal Warning Was Given</label>
        <input type="date" name="warning_date" class="form-control" style="max-width:250px;" value="<?= htmlspecialchars($_POST['warning_date'] ?? '') ?>">
    </div>
</div>

<button type="submit" name="submit" class="btn btn-danger">
    <i class="bi bi-file-earmark-plus me-1"></i> Issue Compound
</button>

<a href="compound.php" class="btn btn-secondary">Cancel</a>

</form>

</div>
</div>

<?php } ?>

<?php include '../includes/footer.php'; ?>
