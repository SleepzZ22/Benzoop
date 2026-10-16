<?php
/**
 * Category Model Class
 * Lost & Found Campus System
 */

class Category {
    private PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /**
     * Get all categories with item counts
     */
    public function getAll(): array {
        $sql = "SELECT c.*, COUNT(i.id) AS total_items 
                FROM categories c
                LEFT JOIN items i ON c.id = i.category_id
                GROUP BY c.id
                ORDER BY c.id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get single category by ID
     */
    public function getById(int $id): ?array {
        $sql = "SELECT * FROM categories WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Create a new category
     */
    public function create(array $data): int {
        $sql = "INSERT INTO categories (name, icon, description) 
                VALUES (:name, :icon, :description)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => trim($data['name']),
            ':icon' => !empty($data['icon']) ? trim($data['icon']) : 'bi-tag',
            ':description' => !empty($data['description']) ? trim($data['description']) : null
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing category
     */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE categories 
                SET name = :name, icon = :icon, description = :description 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':name' => trim($data['name']),
            ':icon' => !empty($data['icon']) ? trim($data['icon']) : 'bi-tag',
            ':description' => !empty($data['description']) ? trim($data['description']) : null
        ]);
    }

    /**
     * Delete a category (checks if items are associated first)
     */
    public function delete(int $id): bool {
        // Check if there are items using this category
        $count = $this->getItemCount($id);
        if ($count > 0) {
            throw new Exception("ไม่สามารถลบหมวดหมู่นี้ได้เนื่องจากมีรายการสิ่งของที่เชื่อมโยงอยู่ ({$count} รายการ)");
        }

        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get count of items in a category
     */
    public function getItemCount(int $id): int {
        $sql = "SELECT COUNT(*) FROM items WHERE category_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Total number of categories
     */
    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    }
}
