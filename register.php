<?php
/**
 * Student Registration Page
 * Lost & Found Campus System
 */
require_once __DIR__ . '/config/config.php';

// If already logged in, redirect
if (Auth::check()) {
    redirect('index.php');
}

$userModel = new User();
$errors = [];
$formData = [
    'username' => '',
    'fullname' => '',
    'email'    => '',
    'phone'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'ระบบความปลอดภัยผิดพลาด (CSRF token ไม่ถูกต้อง)';
    }

    $formData['username'] = trim($_POST['username'] ?? '');
    $formData['fullname'] = trim($_POST['fullname'] ?? '');
    $formData['email']    = trim($_POST['email'] ?? '');
    $formData['phone']    = trim($_POST['phone'] ?? '');
    $password             = $_POST['password'] ?? '';
    $passwordConfirm      = $_POST['password_confirm'] ?? '';

    // Validations
    if (empty($formData['username'])) {
        $errors[] = 'กรุณาระบุชื่อผู้ใช้';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $formData['username'])) {
        $errors[] = 'ชื่อผู้ใช้ต้องเป็นตัวอักษรภาษาอังกฤษ ตัวเลข หรือขีดล่าง ความยาว 3-30 ตัวอักษร';
    } elseif ($userModel->findByUsername($formData['username'])) {
        $errors[] = 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว กรุณาใช้ชื่ออื่น';
    }

    if (empty($formData['fullname'])) {
        $errors[] = 'กรุณาระบุชื่อ-นามสกุล';
    }

    if (empty($formData['email'])) {
        $errors[] = 'กรุณาระบุอีเมล';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
    } elseif ($userModel->findByEmail($formData['email'])) {
        $errors[] = 'อีเมลนี้ถูกลงทะเบียนไว้แล้ว';
    }

    if (empty($password)) {
        $errors[] = 'กรุณาระบุรหัสผ่าน';
    } elseif (strlen($password) < 6) {
        $errors[] = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = 'การยืนยันรหัสผ่านไม่ตรงกัน';
    }

    // Save user
    if (empty($errors)) {
        try {
            $newUserId = $userModel->create([
                'username' => $formData['username'],
                'password' => $password,
                'fullname' => $formData['fullname'],
                'email'    => $formData['email'],
                'phone'    => $formData['phone'],
                'role'     => 'user'
            ]);

            setFlash('success', 'ลงทะเบียนสำเร็จเรียบร้อย! กรุณาเข้าสู่ระบบด้วยบัญชีของคุณ');
            redirect('login.php');
        } catch (Exception $e) {
            $errors[] = 'เกิดข้อผิดพลาดในการลงทะเบียน: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'ลงทะเบียนสมาชิกใหม่';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="bg-primary text-white p-4 text-center" style="background: linear-gradient(135deg, #1e3a8a, #2563eb) !important;">
                    <div class="brand-icon-box mx-auto mb-2 bg-white text-primary" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h3 class="fw-bold mb-1">ลงทะเบียนสมาชิกใหม่</h3>
                    <p class="small text-white-50 mb-0">สำหรับนักศึกษาและบุคลากรภายในมหาวิทยาลัย</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger shadow-sm mb-4 small" style="border-radius: 10px;">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= e($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/register.php" method="POST">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="username" class="form-label">ชื่อผู้ใช้ (Username) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= e($formData['username']) ?>" placeholder="เช่น somchai_j (ภาษาอังกฤษ/ตัวเลข 3-30 ตัว)" required>
                        </div>

                        <div class="mb-3">
                            <label for="fullname" class="form-label">ชื่อ - นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fullname" name="fullname" value="<?= e($formData['fullname']) ?>" placeholder="เช่น นายสมชาย ใจดี" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">อีเมล <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= e($formData['email']) ?>" placeholder="name@campus.ac.th" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?= e($formData['phone']) ?>" placeholder="เช่น 081-234-5678">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="password" class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="อย่างน้อย 6 ตัวอักษร" required>
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirm" class="form-label">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="กรอกรหัสผ่านซ้ำอีกครั้ง" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fs-6 shadow-sm mb-3">
                            <i class="bi bi-check-circle me-1"></i>สร้างบัญชีผู้ใช้งาน
                        </button>

                        <div class="text-center small text-muted">
                            มีบัญชีผู้ใช้งานอยู่แล้ว? <a href="<?= BASE_URL ?>/login.php" class="text-primary fw-semibold text-decoration-none">เข้าสู่ระบบ</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
