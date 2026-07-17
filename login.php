<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config/db.php';
include 'config/session.php';

$error = "";    

if(isset($_POST['login']))
{
    $email = trim($_POST['email']); 
    $password = $_POST['password'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM users WHERE email=?"
    );

    if(!$stmt)
    {
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $email
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result)==1)
    {
        $user = mysqli_fetch_assoc($result);

        if(password_verify($password,$user['password']))
        {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_picture'] = $user['profile_picture'];

                if($user['role']=="student")
        {
            header("Location: student/dashboard.php");
        }
        elseif($user['role']=="warden")
        {
            header("Location: warden/dashboard.php");
        }
        elseif($user['role']=="guard")
        {
            header("Location: guard/dashboard.php");
        }
        else
        {
            header("Location: admin/dashboard.php");
        }

        exit();
        }
        else
        {
            $error = "Invalid email or password.";
        }
    }
    else
    {
        $error = "Invalid email or password.";
    }
}

?>

<!DOCTYPE html>

<html>

<head>

<title>

KPTM Non Resident /Resident Outing System Management

</title>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">

<style>

body
{

    /* Set the path to your new background image */
    background-image: url('assets/images/login-bg.png'); 
    
    /* Make sure the image covers the entire screen without stretching */
    background-size: cover; 
    
    /* Keep the image perfectly centered */
    background-position: center; 
    
    /* Prevent the image from repeating like tiles */
    background-repeat: no-repeat; 
    
    font-family: 'Segoe UI';
}


.login-card
{
    border:none;
    border-radius:30px;
    overflow:hidden;

    box-shadow:
    0 8px 25px rgba(0,0,0,.08);
}

.left-panel
{
     background: linear-gradient(
     135deg,
     #D86A6A,
     #C94B4B
        );

    color:white;
    padding:50px;
}

.right-panel
{
    background:white;
    padding:50px;
}

.logo
{
    width:300px;
    height: auto;
    margin-bottom:20px;
}

.btn-login
{
    background:#C94B4B;
    color:white;
    border:none;
    border-radius:12px;
    transition:.3s;
}

.btn-login:hover
{
    background:#B83E3E;
    transform:translateY(-2px);
}

@media (max-width: 767px)
{
    .left-panel,
    .right-panel
    {
        padding: 30px 24px;
    }

    .logo
    {
        width: 150px;
        margin-bottom: 12px;
    }

    .left-panel h2
    {
        font-size: 22px;
    }

    .left-panel p
    {
        font-size: 14px;
    }
}

</style>

</head>

<body>

<div class="container">

<div class="row justify-content-center align-items-center vh-100">

<div class="col-lg-10">

<div class="card login-card">

<div class="row g-0">

<div class="col-md-6 left-panel text-center">

<img
src="assets/images/logo2.png"
class="logo">

<h2 class="mt-4">

Non Resident /Resident Outing System Management

</h2>

<p class="mt-3">

Secure QR Monitoring System

</p>

</div>

<div class="col-md-6 right-panel">

<h3 class="mb-4">

Sign In

</h3>

<?php if($error!=""){ ?>

<div class="alert alert-danger">

<?php echo $error; ?>

</div>

<?php } ?>

<form method="POST">

<div class="mb-3">

<label>

Email

</label>

<div class="input-group">

<span class="input-group-text">

<i class="bi bi-envelope"></i>

</span>

<input
type="email"
name="email"
class="form-control"
required>

</div>

</div>

<div class="mb-4">

<label>

Password

</label>

<div class="input-group">

<span class="input-group-text">

<i class="bi bi-lock"></i>

</span>

<input
type="password"
name="password"
class="form-control"
required>

</div>

</div>

<button
type="submit"
name="login"
class="btn btn-login w-100">

Sign In

</button>

</form>

</div>

</div>

</div>

</div>

</div>

</div>

</body>

</html>