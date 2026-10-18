-- ==============================================================================
-- Supabase (PostgreSQL) Schema: Lost & Found Campus System
-- สำหรับนำไปรันใน Supabase -> SQL Editor -> New query -> Paste & Run
-- ==============================================================================

-- 1. ลบตารางเดิมถ้ามี (Cascade foreign keys)
DROP TABLE IF EXISTS items CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- 2. ตารางผู้ใช้งาน (users)
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user' CHECK (role IN ('admin', 'user')),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 3. ตารางหมวดหมู่ (categories)
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) NOT NULL DEFAULT 'bi-tag',
    description VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 4. ตารางสิ่งของ (items)
CREATE TABLE items (
    id SERIAL PRIMARY KEY,
    user_id INT DEFAULT NULL,
    category_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    type VARCHAR(20) NOT NULL CHECK (type IN ('lost', 'found')),
    location VARCHAR(150) NOT NULL,
    item_date DATE NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    contact_name VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    contact_email VARCHAR(100) DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('pending', 'active', 'returned', 'rejected')),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_items_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_items_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
);

-- ฟังก์ชันและ Trigger สำหรับอัปเดตฟิลด์ updated_at อัตโนมัติเมื่อมีการแก้ไขข้อมูล
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

DROP TRIGGER IF EXISTS trigger_items_updated_at ON items;
CREATE TRIGGER trigger_items_updated_at
    BEFORE UPDATE ON items
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- สร้าง Indexes เพื่อเพิ่มประสิทธิภาพการค้นหา
CREATE INDEX idx_items_category ON items(category_id);
CREATE INDEX idx_items_user ON items(user_id);
CREATE INDEX idx_items_type ON items(type);
CREATE INDEX idx_items_status ON items(status);
CREATE INDEX idx_items_date ON items(item_date);

-- ==============================================================================
-- ข้อมูลเริ่มต้น (Sample Data)
-- ==============================================================================

-- 1. ผู้ใช้งานเริ่มต้น
-- Admin: username=admin, password=admin123
-- Student 1: username=student1, password=student123
-- Student 2: username=student2, password=student123
INSERT INTO users (id, username, password, fullname, email, phone, role) VALUES
(1, 'admin', '$2y$10$xsV4frcgt5yFUCKQTDM6VOXBe6x/9hsDZoIkeNOyXdTSqkS.aZ7dG', 'ผู้ดูแลระบบกลาง (Admin)', 'admin@campus.ac.th', '02-123-4567', 'admin'),
(2, 'student1', '$2y$10$5DNy3uY3UzmTNhKN7XQKn.W9kX6swV6lV.o5GOHcJxj3c41cqgZwG', 'นายสมชาย ใจดี', 'somchai.j@campus.ac.th', '081-234-5678', 'user'),
(3, 'student2', '$2y$10$5DNy3uY3UzmTNhKN7XQKn.W9kX6swV6lV.o5GOHcJxj3c41cqgZwG', 'นางสาวสุดา แสนสวย', 'suda.s@campus.ac.th', '089-876-5432', 'user');

-- 2. หมวดหมู่เริ่มต้น
INSERT INTO categories (id, name, icon, description) VALUES
(1, 'อุปกรณ์อิเล็กทรอนิกส์', 'bi-laptop', 'โทรศัพท์มือถือ, แท็บเล็ต, แล็ปท็อป, หูฟัง, สายชาร์จ, พาวเวอร์แบงก์'),
(2, 'เอกสารและบัตรสำคัญ', 'bi-card-heading', 'บัตรนักศึกษา, บัตรประชาชน, กระเป๋าสตางค์, สมุดเลคเชอร์, หนังสือตำรา'),
(3, 'กระเป๋าและสัมภาระ', 'bi-bag', 'กระเป๋าสะพายหลัง, กระเป๋าถือ, ถุงผ้า, กระเป๋าใส่เอกสาร'),
(4, 'เสื้อผ้าและเครื่องแต่งกาย', 'bi-sunglasses', 'เสื้อช็อป, เสื้อกันหนาว, หมวก, แว่นตา, ผ้าพันคอ'),
(5, 'กุญแจและยานพาหนะ', 'bi-key', 'กุญแจห้องพัก, คีย์การ์ดคอนโด, กุญแจรถจักรยานยนต์, หมวกกันน็อก'),
(6, 'เครื่องเขียนและของใช้ทั่วไป', 'bi-pencil', 'กระบอกน้ำเก็บอุณหภูมิ, ร่มพับ, กล่องดินสอ, เครื่องคิดเลขวิทยาศาสตร์'),
(7, 'อื่นๆ', 'bi-question-circle', 'สิ่งของอื่นๆ ที่ไม่อยู่ในหมวดหมู่ข้างต้น');

-- 3. สิ่งของเริ่มต้น (สำหรับทดสอบระบบจับคู่อัจฉริยะ Smart Match)
INSERT INTO items (id, user_id, category_id, title, type, location, item_date, description, image, contact_name, contact_phone, contact_email, status) VALUES
(1, 2, 1, 'ทำหูฟัง AirPods Pro 2 หายพร้อมเคสสีฟ้า', 'lost', 'หอสมุดกลาง ชั้น 3 โซนอ่านหนังสือเงียบ', '2026-10-14', 'ลืมหูฟัง AirPods Pro รุ่นที่ 2 ใส่เคสซิลิโคนสีฟ้าอ่อน มีสติกเกอร์รูปแมวด้านหลัง ลืมไว้บนโต๊ะริมหน้าต่างช่วงเวลาประมาณ 14:00 - 16:00 น.', NULL, 'สมชาย ใจดี', '081-234-5678', 'somchai.j@campus.ac.th', 'active'),
(2, 3, 1, 'พบหูฟังไร้สายสีขาวพร้อมเคสซิลิโคนสีฟ้า', 'found', 'หอสมุดกลาง ชั้น 3 แถวโซนคอมพิวเตอร์', '2026-10-14', 'พบหูฟังสีขาวคล้าย AirPods อยู่ในเคสซิลิโคนสีฟ้า เก็บมาฝากไว้ที่เคาน์เตอร์บรรณารักษ์ ชั้น 1 ติดต่อขอรับได้', NULL, 'สุดา แสนสวย', '089-876-5432', 'suda.s@campus.ac.th', 'active'),
(3, 2, 2, 'บัตรนักศึกษาหาย ชื่อ นายสมชาย ใจดี รหัส 64xxxxxx', 'lost', 'โรงอาหารกลาง (โรงชาย) โต๊ะกลาง', '2026-10-13', 'ทำบัตรนักศึกษามหาวิทยาลัยหาย คาดว่าหล่นตอนหยิบเงินจ่ายค่าอาหาร มีสายคล้องคอสีน้ำเงินสกรีนตรามหาวิทยาลัย', NULL, 'สมชาย ใจดี', '081-234-5678', 'somchai.j@campus.ac.th', 'active'),
(4, NULL, 2, 'เก็บบัตรนักศึกษาได้ที่โรงอาหารกลาง', 'found', 'โรงอาหารกลาง (โรงชาย)', '2026-10-13', 'เก็บบัตรนักศึกษาพร้อมสายคล้องสีน้ำเงินได้ ฝากไว้ที่ร้านป้าสมรข้าวราดแกง ล็อค 5', NULL, 'ป้าสมร (แม่ค้า)', '082-999-1122', NULL, 'active'),
(5, NULL, 5, 'กุญแจรถมอเตอร์ไซค์ Honda พร้อมพวงกุญแจหมี', 'lost', 'ลานจอดรถจักรยานยนต์ ข้างตึกวิศวกรรมศาสตร์', '2026-10-15', 'ทำพวงกุญแจรถฮอนด้าหล่นหาย มีพวงกุญแจตุ๊กตาหมีสีน้ำตาล Rilakkuma ตัวเล็กๆ ผูกอยู่', NULL, 'ธนกฤต มั่นคง', '085-555-1234', 'thanakrit@campus.ac.th', 'active'),
(6, NULL, 6, 'ลืมกระบอกน้ำ Hydro Flask สีเขียว Sage', 'lost', 'อาคารเรียนรวม 3 ห้อง 3201', '2026-10-12', 'ลืมกระบอกน้ำเก็บความเย็นสีเขียวพาสเทล มีรอยขีดข่วนเล็กน้อยที่ก้นขวด ลืมไว้หลังเลิกคลาส PHY101', NULL, 'กานต์ธิดา พรสวรรค์', '086-777-8899', 'karnthida@campus.ac.th', 'returned'),
(7, NULL, 3, 'พบกระเป๋าสะพายผ้าสีดำ ยี่ห้อ Canvas', 'found', 'ศาลาที่พักผู้โดยสาร ประตู 1 ฝั่งหอพักนักศึกษา', '2026-10-15', 'พบกระเป๋าผ้าสีดำ ด้านในมีสมุดโน้ตวิชาสถิติและกล่องดินสอ ไม่พบเงินหรือเอกสารระบุตัวตน นำส่งไว้ที่ป้อม รปภ. ประตู 1', NULL, 'รปภ. สมเกียรติ', '02-123-4500', NULL, 'active'),
(8, NULL, 4, 'เสื้อกันหนาวมีฮู้ดสีเทาเข้ม ยี่ห้อ Uniqlo', 'lost', 'ห้องปฏิบัติการคอมพิวเตอร์ อาคาร 4 ชั้น 2', '2026-10-11', 'ลืมเสื้อกันหนาวมีซิปสีเทาเข้ม แขวนไว้ที่พนักพิงเก้าอี้แถวหน้าสุด', NULL, 'วีรภัทร ชาญชัย', '083-444-2211', 'weerapat@campus.ac.th', 'pending');

-- ตั้งค่า Sequence ให้ตรงกับ ID ล่าสุด เพื่อป้องกัน Primary Key Collision ในการ INSERT ครั้งถัดไป
SELECT setval('users_id_seq', (SELECT COALESCE(MAX(id), 1) FROM users));
SELECT setval('categories_id_seq', (SELECT COALESCE(MAX(id), 1) FROM categories));
SELECT setval('items_id_seq', (SELECT COALESCE(MAX(id), 1) FROM items));
