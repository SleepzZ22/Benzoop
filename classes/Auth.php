<?php
/**
 * Authentication and Authorization Class
 * Lost & Found Campus System
 */

class Auth {
    private User $userModel;

    public function __construct(?User $userModel = null) {
        $this->userModel = $userModel ?? new User();
    }

    /**
     * Authenticate user with username and password
     */
    public function login(string $username, string $password): bool {
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // Prevent Session Fixation attack
        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        // Store user in session
        $_SESSION['user_id']   = (int)$user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['fullname']  = $user['fullname'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['logged_in'] = true;

        return true;
    }

    /**
     * Terminate user session
     */
    public function logout(): void {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        session_start();
    }

    /**
     * Check if a user is logged in
     */
    public static function check(): bool {
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }

    /**
     * Check if the current user is an Administrator
     */
    public static function isAdmin(): bool {
        return self::check() && ($_SESSION['role'] ?? '') === 'admin';
    }

    /**
     * Get current user ID
     */
    public static function id(): ?int {
        return self::check() ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Get current user basic information
     */
    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }
        return [
            'id'       => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'fullname' => $_SESSION['fullname'],
            'email'    => $_SESSION['email'] ?? '',
            'role'     => $_SESSION['role']
        ];
    }

    /**
     * Require authentication; redirect if not logged in
     */
    public static function requireLogin(string $redirectTo = 'login.php'): void {
        if (!self::check()) {
            setFlash('warning', 'กรุณาเข้าสู่ระบบก่อนทำรายการ');
            redirect($redirectTo);
        }
    }

    /**
     * Require admin authorization; redirect if not admin
     */
    public static function requireAdmin(string $redirectTo = 'login.php'): void {
        if (!self::isAdmin()) {
            setFlash('danger', 'ขออภัย เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถเข้าถึงส่วนนี้ได้');
            redirect($redirectTo);
        }
    }
}
