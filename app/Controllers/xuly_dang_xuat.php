<?php
require_once __DIR__ . '/../Core/khoitao.php';
session_unset();
session_destroy();
chuyen_trang('../../index.php');