<?php

$host = getenv('DB_HOST') ?: "eouting-db-student-b5d7.k.aivencloud.com";
$port = getenv('DB_PORT') ?: 17311;
$user = getenv('DB_USER') ?: "avnadmin";
$password = getenv('DB_PASSWORD') ?: "";
$dbname = getenv('DB_NAME') ?: "defaultdb";

$conn = mysqli_connect($host, $user, $password, $dbname, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>