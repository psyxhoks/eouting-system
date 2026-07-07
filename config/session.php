<?php

if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}

$timeout = 1800; // 30 minutes

if (!isset($_SESSION['created']))
{
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

if (
    isset($_SESSION['last_activity'])
    &&
    (time() - $_SESSION['last_activity']) > $timeout
)
{
    session_unset();
    session_destroy();

    header("Location: /outing-app/login.php");
    exit();
}

$_SESSION['last_activity'] = time();
