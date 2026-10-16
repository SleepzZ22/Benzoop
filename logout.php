<?php
/**
 * Logout Handler
 * Lost & Found Campus System
 */
require_once __DIR__ . '/config/config.php';

$auth = new Auth();
$auth->logout();

setFlash('info', 'คุณได้ออกจากระบบเรียบร้อยแล้ว');
redirect('index.php');
