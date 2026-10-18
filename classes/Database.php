<?php
/**
 * Database Connection Class (Singleton Pattern)
 * Lost & Found Campus System
 * 
 * Supports both MySQL and PostgreSQL (Supabase)
 */

class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        try {
            $driver = defined('DB_DRIVER') ? strtolower(DB_DRIVER) : 'mysql';

            if ($driver === 'pgsql') {
                $sslMode = defined('DB_SSLMODE') ? DB_SSLMODE : 'require';
                $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=" . $sslMode;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            } else {
                $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . $charset;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $charset
                ];
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            }
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Prevent object cloning
     */
    private function __clone() {}

    /**
     * Prevent unserializing
     */
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Get the single Database instance
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Get the active PDO connection
     */
    public function getConnection(): PDO {
        return $this->connection;
    }

    /**
     * Get last inserted ID supporting both MySQL and PostgreSQL sequence
     */
    public static function getLastInsertId(PDO $db, string $sequenceName = ''): int {
        if (defined('DB_DRIVER') && strtolower(DB_DRIVER) === 'pgsql' && !empty($sequenceName)) {
            return (int)$db->lastInsertId($sequenceName);
        }
        return (int)$db->lastInsertId();
    }
}
