# 🎓 Lost & Found Campus: ระบบแจ้งของหายและค้นหาสิ่งของภายในมหาวิทยาลัย

> **โครงงานพัฒนาระบบเว็บแอปพลิเคชันด้วย PHP 8+ Object-Oriented Programming (OOP), MySQL (PDO), HTML5, CSS3, Bootstrap 5 และ JavaScript**
> พัฒนาขึ้นโดยตรงสำหรับใช้งานบนสภาพแวดล้อม **XAMPP (Apache + MySQL/MariaDB)** โดยไม่มีการใช้เฟรมเวิร์กภายนอก (No Laravel, No React, No Node.js) เพื่อความเข้าใจง่าย ถูกหลักวิชาการ และรองรับการนำเสนอผลงานระดับมหาวิทยาลัย

---

## 🌟 จุดเด่นและฟีเจอร์สำคัญของระบบ

1. **หน้าแรก (Landing & Dashboard Overview)**
   - แสดงสถิติสิ่งของแบบ Real-time: ของหายทั้งหมด, สิ่งของที่พบ, ส่งคืนสำเร็จ, และอัตราความสำเร็จ (%)
   - แถบหมวดหมู่ด่วน (Category Pills) พร้อมจำนวนสิ่งของในแต่ละหมวด
   - แถบแท็บสลับดูรายการสิ่งของล่าสุด (ทั้งหมด / ของหายล่าสุด / พบของล่าสุด)
   - แนะนำขั้นตอนการใช้งานระบบ 3 ขั้นตอน เข้าใจง่าย

2. **ระบบแจ้งของหาย & แจ้งพบของ (Report System)**
   - สลับประเภทการแจ้งได้สะดวก (ของหาย vs พบของ)
   - ชิปสถานที่ยอดนิยม (Quick Location Chips) เช่น หอสมุดกลาง, โรงอาหารกลาง, อาคารเรียนรวม 3, ลานจอดรถ เพื่อคลิกเติมสถานที่ได้ทันที
   - อัปโหลดภาพถ่ายสิ่งของ พร้อมระบบ **Instant Image Preview** ก่อนส่งฟอร์ม
   - ตรวจสอบความถูกต้องของข้อมูลทั้งฝั่ง Client และ Server (Validation)

3. **ระบบค้นหาและกรองข้อมูลขั้นสูง (Search & Filtering)**
   - ค้นหาด้วยคำสำคัญ (ชื่อสิ่งของ, รายละเอียด, สถานที่)
   - กรองตามประเภท (ทั้งหมด, ของหาย, พบของ)
   - กรองตามหมวดหมู่สิ่งของ
   - กรองตามสถานที่ และช่วงวันที่เกิดเหตุ
   - กรองตามสถานะ (กำลังดำเนินการ, ส่งคืนสำเร็จแล้ว)
   - เรียงลำดับตามวันที่ประกาศ หรือวันที่เกิดเหตุ
   - ระบบแบ่งหน้า (Pagination) ใช้งานง่าย รองรับข้อมูลจำนวนมาก

4. **หน้ารายละเอียดสิ่งของ (Item Detail View)**
   - แสดงภาพถ่ายขนาดใหญ่, ป้ายสถานะ, ข้อมูลสถานที่, วันที่ และข้อมูลติดต่อ
   - ปุ่มพิมพ์ใบประกาศสิ่งของหาย/พบของ (Print Notice)
   - ปุ่มคัดลอกลิงก์สำหรับแชร์ต่อ (Share to Clipboard)
   - แผงควบคุมด่วนสำหรับเจ้าของรายการหรือแอดมิน (สามารถปรับสถานะเป็น "ส่งคืนสำเร็จ" ได้ทันที)

5. **✨ ระบบจับคู่อัจฉริยะ (Smart Match Recommendation)**
   - อัลกอริทึมจับคู่สิ่งของที่ตรงข้ามกัน (ของหาย ↔ พบของ) โดยคำนวณคะแนนความน่าจะเป็น (0 - 100%)
   - เกณฑ์การให้คะแนน:
     - **หมวดหมู่เดียวกัน (Category Match):** 40 คะแนน
     - **สถานที่ตรงกันหรือใกล้เคียง (Location Proximity):** สูงสุด 25 คะแนน
     - **ช่วงเวลาเกิดเหตุใกล้เคียงกัน (Date Proximity):** สูงสุด 25 คะแนน
     - **คำสำคัญในชื่อและรายละเอียดตรงกัน (Keyword Overlap):** สูงสุด 10 คะแนน
   - แสดงป้ายเหตุผลการจับคู่ที่เข้าใจง่าย เช่น "หมวดหมู่เดียวกัน", "สถานที่ตรงกันมาก", "ห่างกันเพียง 1 วัน"

6. **ระบบจัดการสำหรับผู้ดูแลระบบ (Admin Panel & Dashboard)**
   - แดชบอร์ดสรุปสถิติสำคัญและกราฟเปอร์เซ็นต์ตามหมวดหมู่
   - คิวตรวจสอบรายการที่รออนุมัติ (Pending Queue) พร้อมปุ่มกดอนุมัติ/ปฏิเสธทันที
   - ตารางจัดการรายการสิ่งของทั้งหมด (Admin Items Management) พร้อมระบบเปลี่ยนสถานะแบบ Inline Dropdown และปุ่มลบรายการถาวร
   - ตารางจัดการหมวดหมู่สิ่งของ (Category CRUD) พร้อมระบบป้องกันความปลอดภัยหากมีสิ่งของผูกอยู่กับหมวดหมู่นั้น

---

## 👥 การแบ่งงาน 2 คนตามโครงสร้างความรับผิดชอบ

เพื่อให้การทำงานร่วมกันเป็นไปอย่างราบรื่น สมาชิกทั้งสองคนใช้โครงสร้างฐานข้อมูลและชื่อ Method เดียวกันตามสัญญาข้อตกลง (Interface Contract) ดังนี้:

### คนที่ 1: Backend Developer (ฐานข้อมูล, คลาส OOP และ Logic)
- **หน้าที่รับผิดชอบ:**
  - ออกแบบฐานข้อมูลเชิงสัมพันธ์ `lost_found_campus` (ตาราง `users`, `categories`, `items`)
  - พัฒนาคลาสเชื่อมต่อฐานข้อมูล `Database` (Singleton Pattern, PDO Prepared Statements)
  - พัฒนาคลาสโมเดลหลัก: `User`, `Category`, `Item`, `Auth`
  - พัฒนาอัลกอริทึมคำนวณคะแนนจับคู่ในคลาส `MatchFinder`
  - สร้างระบบความปลอดภัย: การเข้ารหัสผ่านด้วย `password_hash()`, การตรวจสอบ Session, และการตรวจไฟล์อัปโหลด
- **ไฟล์ที่ดูแล:**
  - `database.sql`
  - `config/database.php`
  - `config/config.php`
  - `classes/Database.php`
  - `classes/Category.php`
  - `classes/Item.php`
  - `classes/User.php`
  - `classes/Auth.php`
  - `classes/MatchFinder.php`

### คนที่ 2: Frontend & UI Developer (หน้าเว็บ, Admin UI และ CSS)
- **หน้าที่รับผิดชอบ:**
  - ออกแบบ Design System และชุดสีตามมาตรฐาน Modern Web (Google Fonts 'Kanit', Bootstrap 5, Custom Badges, Glassmorphism Cards)
  - พัฒนาหน้าแสดงผลฝั่งผู้ใช้: หน้าแรก, ค้นหา, แจ้งของ, รายละเอียด, เข้าสู่ระบบ, ลงทะเบียน
  - พัฒนาระบบแผงควบคุมผู้ดูแลระบบ (Admin Dashboard, Admin Items, Admin Categories)
  - พัฒนา JavaScript ช่วยเหลือ: การพรีวิวรูปภาพก่อนอัปโหลด, การยืนยันก่อนลบข้อมูล (Confirmation Modals)
- **ไฟล์ที่ดูแล:**
  - `assets/css/style.css`
  - `assets/js/main.js`
  - `assets/img/placeholder.svg`
  - `includes/header.php`, `navbar.php`, `item_card.php`, `alert.php`, `footer.php`
  - `index.php`, `report.php`, `search.php`, `detail.php`, `login.php`, `register.php`, `logout.php`, `my-items.php`
  - `admin/index.php`, `admin/items.php`, `admin/categories.php`

---

## 📁 โครงสร้างโฟลเดอร์ของโปรเจกต์

```text
c:\xampp\htdocs\PJoop\
├── admin/                      # ส่วนของผู้ดูแลระบบ
│   ├── categories.php          # จัดการหมวดหมู่สิ่งของ
│   ├── index.php               # แดชบอร์ดสรุปภาพรวมและคิวตรวจสอบ
│   └── items.php               # ตารางจัดการสิ่งของและเปลี่ยนสถานะ
├── assets/                     # ไฟล์ทรัพยากรหน้าเว็บ
│   ├── css/
│   │   └── style.css           # สไตล์ชีตหลัก (ฟอนต์ Kanit, โทนสีกรมท่า-น้ำเงิน)
│   ├── img/
│   │   └── placeholder.svg     # ภาพสำรองกรณีไม่มีรูปภาพแนบ
│   └── js/
│       └── main.js             # JavaScript พรีวิวรูป, คัดลอกลิงก์, แจ้งเตือน
├── classes/                    # คลาสเชิงวัตถุ (OOP Classes)
│   ├── Auth.php                # จัดการเข้าสู่ระบบ, สิทธิ์ Admin, Session
│   ├── Category.php            # จัดการข้อมูลหมวดหมู่
│   ├── Database.php            # เชื่อมต่อฐานข้อมูล PDO แบบ Singleton
│   ├── Item.php                # จัดการรายการสิ่งของ CRUD และการอัปโหลดไฟล์
│   ├── MatchFinder.php         # อัลกอริทึม Smart Match จับคู่สิ่งของ
│   └── User.php                # จัดการข้อมูลผู้ใช้งาน
├── config/                     # ไฟล์การตั้งค่าระบบ
│   ├── config.php              # ค่าคงที่, Session, ตัวช่วย CSRF, XSS Escape
│   └── database.php            # ข้อมูลการเชื่อมต่อฐานข้อมูล MySQL
├── includes/                   # ส่วนประกอบหน้าเว็บที่ใช้ร่วมกัน
│   ├── alert.php               # กล่องแจ้งเตือน Flash Messages
│   ├── footer.php              # ส่วนท้ายของหน้าเว็บและโหลด Scripts
│   ├── header.php              # ส่วนหัว HTML, Meta Tags และโหลด CSS
│   ├── item_card.php           # การ์ดแสดงผลสิ่งของที่นำมาใช้ซ้ำได้
│   └── navbar.php              # แถบเมนูด้านบนที่ปรับตามสิทธิ์ผู้ใช้งาน
├── uploads/                    # ไดเรกทอรีจัดเก็บไฟล์ที่อัปโหลด
│   └── items/                  # ภาพสิ่งของที่ผู้ใช้แนบเข้ามา
├── database.sql                # โครงสร้างฐานข้อมูลและข้อมูลตัวอย่าง
├── detail.php                  # หน้ารายละเอียดสิ่งของและ Smart Match
├── index.php                   # หน้าแรกของระบบ
├── login.php                   # หน้าเข้าสู่ระบบ (มีปุ่มกรอกบัญชีทดสอบด่วน)
├── logout.php                  # สคริปต์ออกจากระบบ
├── my-items.php                # หน้าดูและจัดการรายการที่ตนเองแจ้ง
├── register.php                # หน้าลงทะเบียนสมาชิกใหม่สำหรับนักศึกษา
├── report.php                  # แบบฟอร์มแจ้งของหาย / แจ้งพบของ
├── search.php                  # หน้าค้นหาและกรองสิ่งของพร้อมระบบแบ่งหน้า
└── README.md                   # คู่มือการติดตั้งและคำอธิบายระบบฉบับนี้
```

---

## 🗄️ โครงสร้างฐานข้อมูล (Database Schema)

ฐานข้อมูลชื่อ: `lost_found_campus` (เข้ารหัส `utf8mb4` รองรับภาษาไทย 100%)

### 1. ตาราง `users` (ผู้ใช้งานระบบ)
| ฟิลด์ | ชนิดข้อมูล | คุณสมบัติ | คำอธิบาย |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, AUTO_INCREMENT | รหัสผู้ใช้ |
| `username` | VARCHAR(50) | NOT NULL, UNIQUE | ชื่อผู้ใช้งาน |
| `password` | VARCHAR(255) | NOT NULL | รหัสผ่านที่แฮชด้วย `password_hash()` |
| `fullname` | VARCHAR(100) | NOT NULL | ชื่อ - นามสกุล |
| `email` | VARCHAR(100) | NOT NULL | ที่อยู่อีเมล |
| `phone` | VARCHAR(20) | NULL | เบอร์โทรศัพท์ |
| `role` | ENUM('admin', 'user') | NOT NULL, DEFAULT 'user' | ระดับสิทธิ์ |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | วันที่สร้างบัญชี |

### 2. ตาราง `categories` (หมวดหมู่สิ่งของ)
| ฟิลด์ | ชนิดข้อมูล | คุณสมบัติ | คำอธิบาย |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, AUTO_INCREMENT | รหัสหมวดหมู่ |
| `name` | VARCHAR(100) | NOT NULL | ชื่อหมวดหมู่ (เช่น อุปกรณ์อิเล็กทรอนิกส์) |
| `icon` | VARCHAR(50) | NOT NULL, DEFAULT 'bi-tag' | คลาสไอคอน Bootstrap Icon |
| `description` | VARCHAR(255) | NULL | คำอธิบายสิ่งของในหมวดหมู่ |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | วันที่สร้าง |

### 3. ตาราง `items` (รายการสิ่งของหาย/พบของ)
| ฟิลด์ | ชนิดข้อมูล | คุณสมบัติ | คำอธิบาย |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK, AUTO_INCREMENT | รหัสรายการ |
| `user_id` | INT | FK -> users(id), NULL | รหัสผู้แจ้ง (ถ้ามี) |
| `category_id` | INT | FK -> categories(id) | รหัสหมวดหมู่ |
| `title` | VARCHAR(150) | NOT NULL | ชื่อสิ่งของ |
| `type` | ENUM('lost', 'found') | NOT NULL | ประเภท: ของหาย (lost) หรือ พบของ (found) |
| `location` | VARCHAR(150) | NOT NULL | สถานที่ที่ทำหายหรือสถานที่พบ |
| `item_date` | DATE | NOT NULL | วันที่ทำหายหรือวันที่พบ |
| `description` | TEXT | NOT NULL | รายละเอียด สี ยี่ห้อ หรือตำหนิ |
| `image` | VARCHAR(255) | NULL | ชื่อไฟล์ภาพในโฟลเดอร์ uploads/items/ |
| `contact_name` | VARCHAR(100) | NOT NULL | ชื่อผู้ติดต่อ |
| `contact_phone`| VARCHAR(20) | NOT NULL | เบอร์โทรศัพท์ |
| `contact_email`| VARCHAR(100) | NULL | อีเมลติดต่อ |
| `status` | ENUM('pending', 'active', 'returned', 'rejected') | NOT NULL, DEFAULT 'active' | สถานะการดำเนินการ |
| `created_at` | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | วันที่ลงประกาศ |
| `updated_at` | DATETIME | NOT NULL, ON UPDATE CURRENT_TIMESTAMP | วันที่แก้ไขล่าสุด |

---

## 🔐 มาตรการความปลอดภัยของระบบ (Security Practices)

1. **การป้องกัน SQL Injection:** ทุกการเรียกใช้งานคำสั่ง SQL ใช้ **PDO Prepared Statements** พร้อมตัวแปรผูกค่า (Bound Parameters) ทั้งหมด 100%
2. **การป้องกัน Cross-Site Scripting (XSS):** ข้อมูลทุกตัวที่นำมาแสดงผลบน HTML จะถูกกรองด้วยฟังก์ชัน `htmlspecialchars()` (ฟังก์ชันตัวช่วย `e()`)
3. **การป้องกัน Cross-Site Request Forgery (CSRF):** ฟอร์มทุกฟอร์มในระบบมี CSRF Token ที่สุ่มสร้างด้วย `random_bytes(32)` และตรวจสอบความปลอดภัยด้วย `hash_equals()`
4. **การเก็บรหัสผ่านอย่างปลอดภัย:** ใช้ฟังก์ชันมาตรฐาน `password_hash()` อัลกอริทึม Bcrypt ที่ปลอดภัยสูง และตรวจสอบด้วย `password_verify()`
5. **การป้องกัน Session Fixation:** เรียกใช้ `session_regenerate_id(true)` ทันทีเมื่อผู้ใช้ล็อกอินสำเร็จ เพื่อสุ่มเปลี่ยน Session ID ใหม่
6. **การตรวจสอบไฟล์อัปโหลดอย่างเข้มงวด:**
   - จำกัดขนาดไฟล์ไม่เกิน 5 MB
   - อนุญาตเฉพาะนามสกุล `.jpg`, `.jpeg`, `.png`, `.webp`
   - ตรวจสอบ Content-Type จริงของไฟล์ด้วย `finfo_file(FILEINFO_MIME_TYPE)` เพื่อป้องกันการปลอมแปลงนามสกุล
   - เปลี่ยนชื่อไฟล์ใหม่ด้วยการสุ่ม Hex เพื่อป้องกันการเขียนทับไฟล์เดิมและ Directory Traversal

---

## 🛠️ ขั้นตอนการติดตั้งและเปิดใช้งานบน XAMPP

### ขั้นตอนที่ 1: เตรียมไฟล์โปรเจกต์
1. วางโฟลเดอร์โปรเจกต์ไว้ที่ไดเรกทอรีของ XAMPP:
   ```text
   C:\xampp\htdocs\PJoop
   ```

### ขั้นตอนที่ 2: เริ่มการทำงานของ Apache และ MySQL
1. เปิดโปรแกรม **XAMPP Control Panel**
2. คลิกปุ่ม **Start** ที่โมดูล **Apache** และ **MySQL** ให้ขึ้นแถบสีเขียว

### ขั้นตอนที่ 3: นำเข้าฐานข้อมูล (Import Database)

#### วิธีที่ 3.1: นำเข้าผ่าน phpMyAdmin (แนะนำสำหรับผู้ใช้งานทั่วไป)
1. เปิดเว็บเบราว์เซอร์ไปที่: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. เมนูด้านบน เลือกแท็บ **"นำเข้า" (Import)**
3. คลิกปุ่ม **"Choose File" (เลือกไฟล์)** แล้วเลือกไฟล์:
   ```text
   C:\xampp\htdocs\PJoop\database.sql
   ```
4. ตรวจสอบชุดอักขระเป็น `utf-8` แล้วเลื่อนลงล่างสุด คลิกปุ่ม **"นำเข้า" (Import / Go)**
5. ระบบจะสร้างฐานข้อมูล `lost_found_campus` พร้อมตารางและข้อมูลตัวอย่างครบถ้วน

#### วิธีที่ 3.2: นำเข้าผ่าน Command Line (สำหรับผู้ใช้ขั้นสูง)
เปิด PowerShell หรือ Command Prompt แล้วสั่ง:
```powershell
C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 -e "source C:/xampp/htdocs/PJoop/database.sql"
```

### ขั้นตอนที่ 4: เข้าใช้งานระบบ
เปิดเบราว์เซอร์แล้วเข้าสู่ URL:
```text
http://localhost/PJoop/
```

---

## 🔑 บัญชีผู้ใช้สำหรับการทดสอบ (Demo Credentials)

ระบบได้เตรียมข้อมูลบัญชีสำหรับให้คณะกรรมการหรืออาจารย์ทดสอบไว้ในหน้า Login โดยมีปุ่ม **"กรอกทันที"** คลิกเดียวเข้าสู่ระบบได้เลย:

| สิทธิ์การใช้งาน (Role) | ชื่อผู้ใช้ (Username) | รหัสผ่าน (Password) | คำอธิบาย |
| :--- | :--- | :--- | :--- |
| **ผู้ดูแลระบบ (Admin)** | `admin` | `admin123` | เข้าถึงแดชบอร์ด, จัดการทุกรายการ, จัดการหมวดหมู่ |
| **นักศึกษา (Student 1)** | `student1` | `student123` | ผู้แจ้งหูฟัง AirPods Pro 2 หาย และบัตรนักศึกษา |
| **นักศึกษา (Student 2)** | `student2` | `student123` | ผู้แจ้งพบหูฟังไร้สายที่หอสมุดกลาง |

---

## 🧪 การทดสอบฟังก์ชันสำคัญ

1. **ทดสอบระบบจับคู่ (Smart Match):**
   - ไปที่หน้ารายการหูฟัง AirPods Pro 2 หาย: [http://localhost/PJoop/detail.php?id=1](http://localhost/PJoop/detail.php?id=1)
   - สังเกตบล็อก **"ระบบ Smart Match : สิ่งของที่อาจตรงกัน"** ด้านล่าง จะแสดงรายการพบหูฟังที่หอสมุดกลาง (ID #2) พร้อมคะแนนความตรงกันสูงถึง **93%** พร้อมป้ายบอกเหตุผลชัดเจน
2. **ทดสอบแจ้งข้อมูลใหม่:**
   - คลิกที่เมนู **"แจ้งของหาย"** หรือ **"แจ้งพบของ"**
   - สามารถคลิกชิปสถานที่ยอดนิยม เช่น **"หอสมุดกลาง"** หรือ **"โรงอาหารกลาง"** เพื่อใส่ชื่อสถานที่ได้ทันที
   - เลือกรูปภาพและทดสอบดูภาพตัวอย่างทันทีก่อนส่ง
3. **ทดสอบแผงควบคุม Admin:**
   - เข้าสู่ระบบด้วย `admin` / `admin123`
   - ไปที่ [http://localhost/PJoop/admin/index.php](http://localhost/PJoop/admin/index.php) เพื่อดูสถิติ, อนุมัติรายการ Pending, และจัดการรายการสิ่งของ
