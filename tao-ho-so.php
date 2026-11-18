<?php
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Controllers/ho_so.php';

$viewData = ho_so_hien_thi($conn);

require __DIR__ . '/app/Views/profile/edit.php';
