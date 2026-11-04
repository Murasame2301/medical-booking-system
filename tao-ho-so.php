<?php
session_start();
require_once __DIR__ . "/include/mysqlConnect.php";

$manguoidung = $_SESSION['manguoidung'] ?? null;

$message = "";
?>