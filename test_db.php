<?php
/**
 * Test Database Connection (Supabase / MySQL)
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ทดสอบการเชื่อมต่อฐานข้อมูล | Lost & Found Campus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8f9fa; padding: 40px 15px; }
        .card { border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
    </style>
</head>
<body>
<div class="container" style="max-width: 650px;">
    <div class="card p-4">
        <h4 class="fw-bold mb-3"><i class="bi bi-database me-2 text-primary"></i>สถานะการเชื่อมต่อฐานข้อมูล</h4>
        <div class="mb-3">
            <span class="badge bg-secondary me-1">Driver: <?= htmlspecialchars(DB_DRIVER) ?></span>
            <span class="badge bg-light text-dark border">Host: <?= htmlspecialchars(DB_HOST) ?></span>
            <span class="badge bg-light text-dark border">Port: <?= htmlspecialchars(DB_PORT) ?></span>
            <span class="badge bg-light text-dark border">DB: <?= htmlspecialchars(DB_NAME) ?></span>
        </div>

        <?php
        try {
            $db = Database::getInstance()->getConnection();
            echo '<div class="alert alert-success d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                    <div><strong>เชื่อมต่อฐานข้อมูลสำเร็จ!</strong> เชื่อมต่อไปยัง ' . htmlspecialchars(DB_DRIVER) . ' ได้อย่างสมบูรณ์</div>
                  </div>';

            // Check tables
            $tables = [];
            if (DB_DRIVER === 'pgsql') {
                $stmt = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $stmt = $db->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            echo '<h6 class="fw-bold mt-3 mb-2">ตารางที่พบในฐานข้อมูล:</h6>';
            if (empty($tables)) {
                echo '<div class="alert alert-warning mb-0">ยังไม่พบตารางในฐานข้อมูล กรุณารันคำสั่งจากไฟล์ <code>supabase_schema.sql</code> ใน Supabase SQL Editor</div>';
            } else {
                echo '<ul class="list-group mb-3">';
                foreach ($tables as $t) {
                    $cnt = $db->query("SELECT COUNT(*) FROM " . $t)->fetchColumn();
                    echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-table me-2 text-muted"></i>' . htmlspecialchars($t) . '</span>
                            <span class="badge bg-primary rounded-pill">' . $cnt . ' แถว</span>
                          </li>';
                }
                echo '</ul>';
            }

        } catch (Exception $e) {
            echo '<div class="alert alert-danger d-flex align-items-start mb-3">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-2 flex-shrink-0"></i>
                    <div>
                        <strong>เชื่อมต่อไม่สำเร็จ:</strong><br>
                        <code>' . htmlspecialchars($e->getMessage()) . '</code>
                        <div class="mt-2 small text-muted">
                            ตรวจสอบค่าในไฟล์ <code>config/database.php</code> ว่าได้ใส่ Host, User, Password ของ Supabase ถูกต้องหรือไม่
                        </div>
                    </div>
                  </div>';
        }
        ?>

        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-house me-1"></i>ไปที่หน้าหลัก</a>
        </div>
    </div>
</div>
</body>
</html>
