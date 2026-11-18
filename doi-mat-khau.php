<?php
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Controllers/tai_khoan.php';

$viewData = tai_khoan_doi_mat_khau($conn);

require __DIR__ . '/app/Views/account/password.php';
