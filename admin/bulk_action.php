<?php

include '../config/error.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

include '../config/db.php';
include '../config/session.php';

if(
    !isset($_SESSION['user_id'])
    ||
    $_SESSION['role']!='admin'
)
{
    header("Location: ../login.php");
    exit();
}

$user_ids = $_POST['user_ids'] ?? [];
$bulk_action = $_POST['bulk_action'] ?? '';

if(empty($user_ids) || !is_array($user_ids) || empty($bulk_action))
{
    header("Location: user_management.php");
    exit();
}

// Only allow known actions, mapped to the same status values used by the single-user actions
$allowed_actions = [
    'activate'   => "UPDATE users SET status='Active' WHERE id=?",
    'deactivate' => "UPDATE users SET status='Inactive' WHERE id=?",
    'graduate'   => "UPDATE users SET status='Graduated' WHERE id=?",
];

if($bulk_action === 'reset_password')
{
    $password = password_hash("student123", PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");

    foreach($user_ids as $id)
    {
        $id = (int)$id;
        mysqli_stmt_bind_param($stmt, "si", $password, $id);
        mysqli_stmt_execute($stmt);
    }

    header("Location: user_management.php?success=bulk_reset");
    exit();
}

if(array_key_exists($bulk_action, $allowed_actions))
{
    $stmt = mysqli_prepare($conn, $allowed_actions[$bulk_action]);

    foreach($user_ids as $id)
    {
        $id = (int)$id;
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
    }

    header("Location: user_management.php?success=bulk_" . $bulk_action);
    exit();
}

header("Location: user_management.php");
exit();

?>
