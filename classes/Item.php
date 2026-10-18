<?php
/**
 * Item Model Class
 * Lost & Found Campus System
 */

class Item {
    private PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /**
     * Create a new item report
     */
    public function create(array $data): int {
        $sql = "INSERT INTO items (
                    user_id, category_id, title, type, location, item_date, 
                    description, image, contact_name, contact_phone, contact_email, status
                ) VALUES (
                    :user_id, :category_id, :title, :type, :location, :item_date, 
                    :description, :image, :contact_name, :contact_phone, :contact_email, :status
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id'       => !empty($data['user_id']) ? (int)$data['user_id'] : null,
            ':category_id'   => (int)$data['category_id'],
            ':title'         => trim($data['title']),
            ':type'          => in_array($data['type'], ['lost', 'found']) ? $data['type'] : 'lost',
            ':location'      => trim($data['location']),
            ':item_date'     => $data['item_date'],
            ':description'   => trim($data['description']),
            ':image'         => !empty($data['image']) ? $data['image'] : null,
            ':contact_name'  => trim($data['contact_name']),
            ':contact_phone' => trim($data['contact_phone']),
            ':contact_email' => !empty($data['contact_email']) ? trim($data['contact_email']) : null,
            ':status'        => in_array($data['status'] ?? 'active', ['pending', 'active', 'returned', 'rejected']) ? $data['status'] : 'active'
        ]);

        return Database::getLastInsertId($this->db, 'items_id_seq');
    }

    /**
     * Update an existing item
     */
    public function update(int $id, array $data): bool {
        $fields = [
            'category_id = :category_id',
            'title = :title',
            'type = :type',
            'location = :location',
            'item_date = :item_date',
            'description = :description',
            'contact_name = :contact_name',
            'contact_phone = :contact_phone',
            'contact_email = :contact_email',
            'status = :status'
        ];

        $params = [
            ':id'            => $id,
            ':category_id'   => (int)$data['category_id'],
            ':title'         => trim($data['title']),
            ':type'          => in_array($data['type'], ['lost', 'found']) ? $data['type'] : 'lost',
            ':location'      => trim($data['location']),
            ':item_date'     => $data['item_date'],
            ':description'   => trim($data['description']),
            ':contact_name'  => trim($data['contact_name']),
            ':contact_phone' => trim($data['contact_phone']),
            ':contact_email' => !empty($data['contact_email']) ? trim($data['contact_email']) : null,
            ':status'        => in_array($data['status'], ['pending', 'active', 'returned', 'rejected']) ? $data['status'] : 'active'
        ];

        // Optional image update
        if (array_key_exists('image', $data) && $data['image'] !== null) {
            $fields[] = 'image = :image';
            $params[':image'] = $data['image'];
        }

        $sql = "UPDATE items SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Update only the status of an item
     */
    public function updateStatus(int $id, string $status): bool {
        if (!in_array($status, ['pending', 'active', 'returned', 'rejected'])) {
            return false;
        }

        $sql = "UPDATE items SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':status' => $status]);
    }

    /**
     * Delete an item and its associated photo file
     */
    public function delete(int $id): bool {
        // Find existing image to delete
        $item = $this->getById($id);
        if ($item && !empty($item['image'])) {
            $filePath = UPLOAD_DIR . $item['image'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        $sql = "DELETE FROM items WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get item by ID with joined category and user details
     */
    public function getById(int $id): ?array {
        $sql = "SELECT i.*, 
                       c.name AS category_name, 
                       c.icon AS category_icon,
                       u.username AS author_username,
                       u.fullname AS author_fullname
                FROM items i
                JOIN categories c ON i.category_id = c.id
                LEFT JOIN users u ON i.user_id = u.id
                WHERE i.id = :id
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get all items matching criteria with pagination
     */
    public function getAll(array $filters = [], int $limit = 20, int $offset = 0): array {
        $where = [];
        $params = [];

        // Filter: Keyword (title, description, location)
        if (!empty($filters['q'])) {
            $where[] = "(LOWER(i.title) LIKE :q_title OR LOWER(i.description) LIKE :q_desc OR LOWER(i.location) LIKE :q_loc)";
            $keyword = '%' . mb_strtolower(trim($filters['q']), 'UTF-8') . '%';
            $params[':q_title'] = $keyword;
            $params[':q_desc']  = $keyword;
            $params[':q_loc']   = $keyword;
        }

        // Filter: Type (lost/found)
        if (!empty($filters['type']) && in_array($filters['type'], ['lost', 'found'])) {
            $where[] = "i.type = :type";
            $params[':type'] = $filters['type'];
        }

        // Filter: Category
        if (!empty($filters['category_id']) && is_numeric($filters['category_id'])) {
            $where[] = "i.category_id = :category_id";
            $params[':category_id'] = (int)$filters['category_id'];
        }

        // Filter: Location keyword
        if (!empty($filters['location'])) {
            $where[] = "i.location LIKE :loc";
            $params[':loc'] = '%' . trim($filters['location']) . '%';
        }

        // Filter: Status
        if (!empty($filters['status']) && in_array($filters['status'], ['pending', 'active', 'returned', 'rejected'])) {
            $where[] = "i.status = :status";
            $params[':status'] = $filters['status'];
        } elseif (empty($filters['include_all_status'])) {
            // Default public view does not show pending or rejected unless explicitly requested (e.g. by admin)
            $where[] = "i.status IN ('active', 'returned')";
        }

        // Filter: Date range
        if (!empty($filters['date_from'])) {
            $where[] = "i.item_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "i.item_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        // Filter: Specific User ID
        if (!empty($filters['user_id'])) {
            $where[] = "i.user_id = :user_id";
            $params[':user_id'] = (int)$filters['user_id'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Order clause
        $sort = $filters['sort'] ?? 'newest';
        $orderClause = "ORDER BY i.created_at DESC";
        if ($sort === 'oldest') {
            $orderClause = "ORDER BY i.created_at ASC";
        } elseif ($sort === 'date_desc') {
            $orderClause = "ORDER BY i.item_date DESC, i.created_at DESC";
        }

        $sql = "SELECT i.*, 
                       c.name AS category_name, 
                       c.icon AS category_icon
                FROM items i
                JOIN categories c ON i.category_id = c.id
                {$whereClause}
                {$orderClause}
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count items matching filters
     */
    public function count(array $filters = []): int {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = "(LOWER(i.title) LIKE :q_title OR LOWER(i.description) LIKE :q_desc OR LOWER(i.location) LIKE :q_loc)";
            $keyword = '%' . mb_strtolower(trim($filters['q']), 'UTF-8') . '%';
            $params[':q_title'] = $keyword;
            $params[':q_desc']  = $keyword;
            $params[':q_loc']   = $keyword;
        }

        if (!empty($filters['type']) && in_array($filters['type'], ['lost', 'found'])) {
            $where[] = "i.type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['category_id']) && is_numeric($filters['category_id'])) {
            $where[] = "i.category_id = :category_id";
            $params[':category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['location'])) {
            $where[] = "i.location LIKE :loc";
            $params[':loc'] = '%' . trim($filters['location']) . '%';
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['pending', 'active', 'returned', 'rejected'])) {
            $where[] = "i.status = :status";
            $params[':status'] = $filters['status'];
        } elseif (empty($filters['include_all_status'])) {
            $where[] = "i.status IN ('active', 'returned')";
        }

        if (!empty($filters['date_from'])) {
            $where[] = "i.item_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "i.item_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = "i.user_id = :user_id";
            $params[':user_id'] = (int)$filters['user_id'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) FROM items i {$whereClause}";
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get recent items for homepage
     */
    public function getRecent(int $limit = 6, ?string $type = null): array {
        $filters = [
            'status' => 'active'
        ];
        if ($type !== null) {
            $filters['type'] = $type;
        }
        return $this->getAll($filters, $limit, 0);
    }

    /**
     * Get statistics summary for dashboard and homepage
     */
    public function getStats(): array {
        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN type = 'lost' THEN 1 ELSE 0 END) AS total_lost,
                    SUM(CASE WHEN type = 'found' THEN 1 ELSE 0 END) AS total_found,
                    SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) AS total_returned,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS total_active,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS total_pending
                FROM items";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();

        $totalReturned = (int)($row['total_returned'] ?? 0);
        $totalAll = (int)($row['total'] ?? 0);
        $successRate = $totalAll > 0 ? round(($totalReturned / $totalAll) * 100) : 0;

        return [
            'total'          => $totalAll,
            'total_lost'     => (int)($row['total_lost'] ?? 0),
            'total_found'    => (int)($row['total_found'] ?? 0),
            'total_returned' => $totalReturned,
            'total_active'   => (int)($row['total_active'] ?? 0),
            'total_pending'  => (int)($row['total_pending'] ?? 0),
            'success_rate'   => $successRate
        ];
    }

    /**
     * Handle safe file upload with MIME type & extension validation
     */
    public function handleUpload(array $file): ?string {
        if (!isset($file['error']) || is_array($file['error'])) {
            return null;
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // No file uploaded, perfectly valid for optional image
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("เกิดข้อผิดพลาดในการอัปโหลดไฟล์ (รหัส: " . $file['error'] . ")");
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception("ขนาดไฟล์ภาพต้องไม่เกิน 5 MB");
        }

        // Extension check
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            throw new Exception("อนุญาตเฉพาะไฟล์นามสกุล .jpg, .jpeg, .png, .webp เท่านั้น");
        }

        // Real MIME type check using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, ALLOWED_MIME_TYPES)) {
            throw new Exception("ประเภทไฟล์ไม่ถูกต้องตามที่กำหนด");
        }

        // Ensure upload directory exists
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0777, true);
        }

        // Generate cryptographically secure unique filename
        $newFilename = 'item_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetPath = UPLOAD_DIR . $newFilename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("ไม่สามารถบันทึกไฟล์ภาพลงเซิร์ฟเวอร์ได้");
        }

        return $newFilename;
    }
}
