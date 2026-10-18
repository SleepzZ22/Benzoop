<?php
/**
 * Database Configuration
 * Lost & Found Campus System
 * 
 * รองรับทั้ง:
 * - Supabase (PostgreSQL / pgsql)
 * - Local XAMPP (MySQL)
 */

// เลือกระบบฐานข้อมูล: 'pgsql' สำหรับ Supabase, 'mysql' สำหรับ Local XAMPP
define('DB_DRIVER', 'pgsql');

if (DB_DRIVER === 'pgsql') {
    // ==========================================
    // ตั้งค่าการเชื่อมต่อ Supabase (PostgreSQL)
    // Project Ref: asjqmkqyupwapbrjmear
    // Region: Northeast Asia (Seoul) ap-northeast-2
    // ==========================================
    define('DB_HOST', 'aws-0-ap-northeast-2.pooler.supabase.com');
    define('DB_PORT', '6543'); // Pooler port (รองรับ IPv4)
    define('DB_NAME', 'postgres');
    define('DB_USER', 'postgres.asjqmkqyupwapbrjmear');
    define('DB_PASS', '0RVk2zN9o2RJIv3d');
    define('DB_SSLMODE', 'require');
} else {
    // ==========================================
    // ข้อมูลสำหรับ Local XAMPP (MySQL เดิม)
    // ==========================================
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'lost_found_campus');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}
