<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] === 'admin') {
    header("Location: admin.php");
    exit;
}

if ($_SESSION['role'] === 'murid') {
    header("Location: murid.php");
    exit;
}

session_unset();
session_destroy();

header("Location: login.php");
exit;
?>