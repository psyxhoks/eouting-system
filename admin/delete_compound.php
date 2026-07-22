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
    header(
        "Location: ../login.php"
    );
    exit();
}

if(
    !isset($_GET['id'])
)
{
    header(
        "Location: compound.php"
    );
    exit();
}

$id =
(int)
$_GET['id'];

$sql =
"
DELETE FROM compound
WHERE id=?
";

$stmt =
mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute(
    $stmt
);

header(
    "Location: compound.php?success=delete"
);

exit();

?>
