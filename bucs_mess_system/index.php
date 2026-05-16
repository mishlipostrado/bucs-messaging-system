<?php
require_once 'includes/config.php';
header('Location: ' . (isset($_SESSION['user']) ? 'dashboard.php' : 'login.php'));
exit;
