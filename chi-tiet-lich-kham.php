<?php
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Controllers/lich_kham.php';

$viewData = lich_kham_chi_tiet($conn);

require __DIR__ . '/app/Views/appointments/detail.php';
