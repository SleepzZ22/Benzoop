<?php
/**
 * Report Lost or Found Item Form
 * Lost & Found Campus System
 */
require_once __DIR__ . '/config/config.php';

$categoryModel = new Category();
$itemModel     = new Item();
$categories    = $categoryModel->getAll();

$currentUser = Auth::user();
$defaultType = ($_GET['type'] ?? 'lost') === 'found' ? 'found' : 'lost';

$errors = [];
$formData = [
    'type'          => $defaultType,
    'title'         => '',
    'category_id'   => '',
    'location'      => '',
    'item_date'     => date('Y-m-d'),
    'description'   => '',
    'contact_name'  => $currentUser['fullname'] ?? '',
    'contact_phone' => '',
    'contact_email' => $currentUser['email'] ?? '',
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validate CSRF token
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'ความปลอดภัยล้มเหลว (CSRF token ไม่ถูกต้องหรือหมดอายุ) กรุณาลองใหม่อีกครั้ง';
    }

    $formData['type']          = in_array($_POST['type'] ?? '', ['lost', 'found']) ? $_POST['type'] : 'lost';
    $formData['title']         = trim($_POST['title'] ?? '');
    $formData['category_id']   = (int)($_POST['category_id'] ?? 0);
    $formData['location']      = trim($_POST['location'] ?? '');
    $formData['item_date']     = trim($_POST['item_date'] ?? '');
    $formData['description']   = trim($_POST['description'] ?? '');
    $formData['contact_name']  = trim($_POST['contact_name'] ?? '');
    $formData['contact_phone'] = trim($_POST['contact_phone'] ?? '');
    $formData['contact_email'] = trim($_POST['contact_email'] ?? '');

    // 2. Field Validations
    if (empty($formData['title'])) {
        $errors[] = 'กรุณาระบุชื่อสิ่งของ';
    } elseif (mb_strlen($formData['title']) > 150) {
        $errors[] = 'ชื่อสิ่งของต้องไม่เกิน 150 ตัวอักษร';
    }

    if (empty($formData['category_id'])) {
        $errors[] = 'กรุณาเลือกหมวดหมู่สิ่งของ';
    }

    if (empty($formData['location'])) {
        $errors[] = 'กรุณาระบุสถานที่ที่ทำหายหรือสถานที่ที่พบสิ่งของ';
    }

    if (empty($formData['item_date'])) {
        $errors[] = 'กรุณาระบุวันที่ทำหายหรือวันที่พบสิ่งของ';
    }

    if (empty($formData['description'])) {
        $errors[] = 'กรุณาระบุรายละเอียด เช่น สี ยี่ห้อ หรือตำหนิของสิ่งของ';
    }

    if (empty($formData['contact_name'])) {
        $errors[] = 'กรุณาระบุชื่อผู้ติดต่อ';
    }

    if (empty($formData['contact_phone'])) {
        $errors[] = 'กรุณาระบุเบอร์โทรศัพท์สำหรับติดต่อ';
    }

    // 3. Image Upload Handling
    $imageFilename = null;
    if (empty($errors) && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $imageFilename = $itemModel->handleUpload($_FILES['image']);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    // 4. Save to Database
    if (empty($errors)) {
        try {
            $insertData = [
                'user_id'       => Auth::id(),
                'category_id'   => $formData['category_id'],
                'title'         => $formData['title'],
                'type'          => $formData['type'],
                'location'      => $formData['location'],
                'item_date'     => $formData['item_date'],
                'description'   => $formData['description'],
                'image'         => $imageFilename,
                'contact_name'  => $formData['contact_name'],
                'contact_phone' => $formData['contact_phone'],
                'contact_email' => $formData['contact_email'],
                'status'        => 'active' // Public items default to active immediately
            ];

            $newItemId = $itemModel->create($insertData);

            $typeName = ($formData['type'] === 'lost') ? 'แจ้งของหาย' : 'แจ้งพบของ';
            setFlash('success', "บันทึกรายการ{$typeName}สำเร็จเรียบร้อย! ระบบกำลังค้นหาข้อมูลที่อาจตรงกัน");
            redirect("detail.php?id={$newItemId}");
        } catch (Exception $e) {
            $errors[] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage();
        }
    }
}

$pageTitle = ($formData['type'] === 'lost') ? 'แจ้งของหาย' : 'แจ้งพบของ';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">หน้าแรก</a></li>
                    <li class="breadcrumb-item active" aria-current="page">แจ้งรายการสิ่งของ</li>
                </ol>
            </nav>

            <div class="form-card">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle p-3 mb-2 <?= $formData['type'] === 'lost' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?>">
                        <i class="bi <?= $formData['type'] === 'lost' ? 'bi-exclamation-triangle-fill' : 'bi-gift-fill' ?> fs-1"></i>
                    </div>
                    <h2 class="fw-bold"><?= $formData['type'] === 'lost' ? 'แบบฟอร์มแจ้งของหาย' : 'แบบฟอร์มแจ้งพบของ' ?></h2>
                    <p class="text-muted small">กรุณากรอกข้อมูลตามความเป็นจริง เพื่อความสะดวกรวดเร็วในการตรวจสอบและส่งมอบสิ่งของ</p>
                </div>

                <!-- Display Validation Errors -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm mb-4" style="border-radius: 12px;">
                        <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-octagon-fill me-1"></i>ไม่สามารถบันทึกข้อมูลได้:</h6>
                        <ul class="mb-0 small ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/report.php" method="POST" enctype="multipart/form-data" id="reportForm">
                    <?= csrf_field() ?>

                    <!-- Type Selector Buttons -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">ประเภทการแจ้ง <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="type" id="typeLost" value="lost" <?= $formData['type'] === 'lost' ? 'checked' : '' ?> onchange="window.location.href='report.php?type=lost'">
                                <label class="btn btn-outline-danger w-100 py-3 d-flex flex-column align-items-center gap-1 rounded-3" for="typeLost">
                                    <i class="bi bi-exclamation-circle-fill fs-4"></i>
                                    <span class="fw-bold">ฉันทำของหาย (Lost)</span>
                                    <small class="text-muted" style="font-size: 0.75rem;">ตามหาสิ่งของที่ทำหล่นหาย</small>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="type" id="typeFound" value="found" <?= $formData['type'] === 'found' ? 'checked' : '' ?> onchange="window.location.href='report.php?type=found'">
                                <label class="btn btn-outline-success w-100 py-3 d-flex flex-column align-items-center gap-1 rounded-3" for="typeFound">
                                    <i class="bi bi-gift-fill fs-4"></i>
                                    <span class="fw-bold">ฉันเก็บของได้ / พบของ (Found)</span>
                                    <small class="text-muted" style="font-size: 0.75rem;">ตามหาเจ้าของสิ่งของที่พบ</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-info-circle-fill text-primary me-2"></i>ข้อมูลสิ่งของ
                    </h5>

                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">ชื่อสิ่งของ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="title" name="title" value="<?= e($formData['title']) ?>" placeholder="เช่น หูฟัง AirPods Pro 2 ในเคสสีฟ้า, บัตรนักศึกษา, กระเป๋าสตางค์สีดำ" required>
                    </div>

                    <!-- Category & Date -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">หมวดหมู่สิ่งของ <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">-- กรุณาเลือกหมวดหมู่ --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ((string)$formData['category_id'] === (string)$cat['id']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="item_date" class="form-label"><?= $formData['type'] === 'lost' ? 'วันที่ทำหาย' : 'วันที่พบของ' ?> <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="item_date" name="item_date" value="<?= e($formData['item_date']) ?>" max="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <!-- Location with Quick Chips -->
                    <div class="mb-3">
                        <label for="location" class="form-label"><?= $formData['type'] === 'lost' ? 'สถานที่ที่คาดว่าทำหาย' : 'สถานที่ที่พบสิ่งของ' ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="location" name="location" value="<?= e($formData['location']) ?>" placeholder="เช่น หอสมุดกลาง ชั้น 3, โรงอาหารกลาง โต๊ะแถวกลาง, ตึกวิศวะ ห้อง 402" required>
                        
                        <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
                            <span class="small text-muted me-1">จุดยอดนิยม:</span>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill small" onclick="document.getElementById('location').value='หอสมุดกลาง ชั้น 1'">หอสมุดกลาง</button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill small" onclick="document.getElementById('location').value='โรงอาหารกลาง (โรงชาย)'">โรงอาหารกลาง</button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill small" onclick="document.getElementById('location').value='อาคารเรียนรวม 3'">อาคารเรียนรวม 3</button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill small" onclick="document.getElementById('location').value='ลานจอดรถจักรยานยนต์'">ลานจอดรถ</button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill small" onclick="document.getElementById('location').value='อาคารกิจกรรมนักศึกษา'">อาคารกิจกรรม</button>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">รายละเอียดสิ่งของ (สี, ยี่ห้อ, ตำหนิ, เอกลักษณ์เด่น) <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="ระบุรายละเอียดให้ชัดเจน เช่น สีของเคส รอยขีดข่วน สติกเกอร์ที่ติด สิ่งที่อยู่ด้านในกระเป๋า ฯลฯ" required><?= e($formData['description']) ?></textarea>
                    </div>

                    <!-- Image Upload with Instant Preview -->
                    <div class="mb-4">
                        <label class="form-label">รูปภาพประกอบ (ถ้ามี)</label>
                        <div class="image-upload-wrapper" onclick="document.getElementById('imageInput').click();">
                            <div id="uploadPrompt">
                                <i class="bi bi-cloud-arrow-up text-primary fs-1"></i>
                                <h6 class="fw-semibold mt-2 mb-1">คลิกเพื่อเลือกรูปภาพ หรือ ลากไฟล์มาวางที่นี่</h6>
                                <p class="text-muted small mb-0">รองรับไฟล์ JPG, PNG, WEBP (ขนาดไม่เกิน 5 MB)</p>
                            </div>

                            <input type="file" id="imageInput" name="image" accept="image/jpeg,image/png,image/webp" style="display: none;">

                            <div id="imagePreviewContainer" class="image-preview-container">
                                <img id="imagePreview" src="" alt="Preview">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-danger rounded-pill" id="removeImageBtn">
                                        <i class="bi bi-trash me-1"></i>ยกเลิกรูปภาพนี้
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-person-lines-fill text-primary me-2"></i>ข้อมูลสำหรับติดต่อกลับ
                    </h5>

                    <!-- Contact Name -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="contact_name" class="form-label">ชื่อผู้ติดต่อ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_name" name="contact_name" value="<?= e($formData['contact_name']) ?>" placeholder="เช่น สมชาย ใจดี หรือ รปภ. ประจำตึก" required>
                        </div>
                        <div class="col-md-4">
                            <label for="contact_phone" class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="contact_phone" name="contact_phone" value="<?= e($formData['contact_phone']) ?>" placeholder="เช่น 081-234-5678" required>
                        </div>
                        <div class="col-md-4">
                            <label for="contact_email" class="form-label">อีเมล (ถ้ามี)</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?= e($formData['contact_email']) ?>" placeholder="name@campus.ac.th">
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i>ยกเลิก
                        </a>
                        <button type="submit" class="btn <?= $formData['type'] === 'lost' ? 'btn-danger' : 'btn-success' ?> btn-lg px-5 shadow">
                            <i class="bi bi-check2-circle me-1"></i>ยืนยันการแจ้งข้อมูล
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
