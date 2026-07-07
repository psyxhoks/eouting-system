<?php
include '../config/error.php';
error_reporting(E_ALL);
ini_set('display_errors',1);

include '../config/db.php';
include '../config/session.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin') {
    header("Location: ../login.php");
    exit();
}

$success = "";
$imported = 0;
$duplicate = 0;
$invalid = 0;

if(isset($_POST['import'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if($_FILES['csv_file']['error']==0) {
        $handle = fopen($file, "r");
        fgetcsv($handle); // Skip header

        // OPTIMIZATION: Hash the password ONCE outside the loop so your PC doesn't timeout!
        $default_password_hashed = password_hash("student123", PASSWORD_DEFAULT);
        $role = "student";

        $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE student_id=? OR email=?");
        // OPTIMIZATION: Removed guardian columns to match your actual database!
        $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (student_id, fullname, email, password, role, programme, cohort) VALUES (?, ?, ?, ?, ?, ?, ?)");

        while(($data = fgetcsv($handle)) !== FALSE) {
            if(empty(trim($data[3] ?? ""))) {
                $invalid++;
                continue;
            }

            $fullname = trim($data[1] ?? "");
            $programme = trim($data[2] ?? "");
            $student_id = trim($data[3] ?? "");
            $cohort = trim($data[5] ?? "");
            $email = trim($data[11] ?? "");
            $password = $default_password_hashed; 

            mysqli_stmt_bind_param($check_stmt, "ss", $student_id, $email);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);

            if(mysqli_num_rows($check_result) == 0) {
                mysqli_stmt_bind_param($insert_stmt, "sssssss", $student_id, $fullname, $email, $password, $role, $programme, $cohort);
                mysqli_stmt_execute($insert_stmt);
                $imported++;
            } else {
                $duplicate++;
            }
        }
        fclose($handle);

        $success = "Imported Successfully : $imported students<br>Duplicate Records Skipped : $duplicate<br>Invalid Rows Skipped : $invalid";
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<h2 class="fw-bold mt-3">Upload Student Data</h2>
<p class="text-muted">Import student information from CSV file.</p>
<hr>

<?php if($success!=""): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Select CSV File</label>
                <input type="file" name="csv_file" class="form-control" accept=".csv" required>
            </div>
            <button type="submit" name="import" class="btn btn-primary">Import Student Data</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>