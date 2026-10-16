<?php
/**
 * Header Template
 * Lost & Found Campus System
 */
if (!defined('APP_NAME')) {
    require_once dirname(__DIR__) . '/config/config.php';
}

$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME . ' - ' . APP_SUBTITLE;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="ระบบแจ้งของหายและค้นหาสิ่งของภายในมหาวิทยาลัย Lost & Found Campus เชื่อมต่อผู้ทำของหายและผู้พบของด้วยระบบจับคู่อัจฉริยะ">
    
    <!-- Google Fonts: Kanit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/navbar.php'; ?>
<main>
<?php require_once __DIR__ . '/alert.php'; ?>
