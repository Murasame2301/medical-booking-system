<?php
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Controllers/auth.php';

$viewData = auth_dang_ky($conn);

require __DIR__ . '/app/Views/auth/register.php';
