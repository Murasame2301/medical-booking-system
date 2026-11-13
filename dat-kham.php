<?php
session_start();

require_once __DIR__ . '/include/mysqlConnect.php';
require_once __DIR__ . '/app/Models/LichKham.php';
require_once __DIR__ . '/app/Controllers/LichKhamController.php';

$lichKham = new LichKham($conn);
$lichKhamController = new LichKhamController($lichKham);
$viewData = $lichKhamController->hienThi();

require __DIR__ . '/app/Views/appointments/search.php';
