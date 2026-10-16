<?php
/**
 * User Model Class
 * Lost & Found Campus System
 */

class User {
    private PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?array {
        $sql = "SELECT id, username, fullname, email, phone, role, created_at 
                FROM users 
                WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by Username (includes password for authentication)
     */
    public function findByUsername(string $username): ?array {
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => trim($username)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by Email
     */
    public function findByEmail(string $email): ?array {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => trim($email)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Create a new user with secure password hash
     */
    public function create(array $data): int {
        $sql = "INSERT INTO users (username, password, fullname, email, phone, role) 
                VALUES (:username, :password, :fullname, :email, :phone, :role)";
        $stmt = $this->db->prepare($sql);
        
        $role = in_array($data['role'] ?? 'user', ['admin', 'user']) ? $data['role'] : 'user';
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt->execute([
            ':username' => trim($data['username']),
            ':password' => $hashedPassword,
            ':fullname' => trim($data['fullname']),
            ':email'    => trim($data['email']),
            ':phone'    => !empty($data['phone']) ? trim($data['phone']) : null,
            ':role'     => $role
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update user profile
     */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE users 
                SET fullname = :fullname, email = :email, phone = :phone 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'       => $id,
            ':fullname' => trim($data['fullname']),
            ':email'    => trim($data['email']),
            ':phone'    => !empty($data['phone']) ? trim($data['phone']) : null
        ]);
    }

    /**
     * Update user password
     */
    public function updatePassword(int $id, string $newPassword): bool {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'       => $id,
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Get all users
     */
    public function getAll(): array {
        $sql = "SELECT id, username, fullname, email, phone, role, created_at 
                FROM users 
                ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Count users
     */
    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }
}
