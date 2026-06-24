<?php
require_once __DIR__ . '/../config/auth-functions.php';
auth_logout();
header('Location: login.php');
exit;
