<?php
/**
 * User & Admin Login Page
 * Lost & Found Campus System
 */
require_once __DIR__ . '/config/config.php';

// If already logged in, redirect
if (Auth::check()) {
    if (Auth::isAdmin()) {
        redirect('admin/index.php');
    } else {
        redirect('index.php');
    }
}

$error = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'ระบบความปลอดภัยผิดพลาด (CSRF Token ไม่ถูกต้อง)';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่านให้ครบถ้วน';
        } else {
            $auth = new Auth();
            if ($auth->login($username, $password)) {
                setFlash('success', 'เข้าสู่ระบบสำเร็จ ยินดีต้อนรับคุณ ' . e($_SESSION['fullname']));
                
                // Redirect admin to dashboard, student to homepage or target
                if (Auth::isAdmin()) {
                    redirect('admin/index.php');
                } else {
                    $redirectUrl = $_SESSION['redirect_after_login'] ?? 'index.php';
                    unset($_SESSION['redirect_after_login']);
                    redirect($redirectUrl);
                }
            } else {
                $error = 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง';
            }
        }
    }
}

$pageTitle = 'เข้าสู่ระบบ';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="bg-primary text-white p-4 text-center" style="background: linear-gradient(135deg, #1e3a8a, #2563eb) !important;">
                    <div class="brand-icon-box mx-auto mb-2 bg-white text-primary" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h3 class="fw-bold mb-1">เข้าสู่ระบบ</h3>
                    <p class="small text-white-50 mb-0">ระบบแจ้งของหายและค้นหาสิ่งของ มหาวิทยาลัย</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center mb-4 small" style="border-radius: 10px;">
                            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                            <div><?= e($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/login.php" method="POST">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="username" class="form-label">ชื่อผู้ใช้ (Username)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="username" name="username" value="<?= e($username) ?>" placeholder="กรอกชื่อผู้ใช้..." required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">รหัสผ่าน (Password)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="กรอกรหัสผ่าน..." required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fs-6 shadow-sm mb-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i>เข้าสู่ระบบ
                        </button>

                        <div class="text-center small text-muted">
                            ยังไม่มีบัญชีผู้ใช้? <a href="<?= BASE_URL ?>/register.php" class="text-primary fw-semibold text-decoration-none">ลงทะเบียนใหม่</a>
                        </div>
                    </form>
                </div>

                <!-- Testing Demo Credentials Helper -->
                <div class="card-footer bg-light p-3 border-top small">
                    <div class="fw-bold text-dark mb-1 d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-info me-1"></i>บัญชีสำหรับทดสอบระบบ (Demo Accounts):
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span><strong>ผู้ดูแล (Admin):</strong> admin / admin123</span>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;" onclick="fillLogin('admin', 'admin123')">กรอกทันที</button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><strong>นักศึกษา (Student):</strong> student1 / student123</span>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;" onclick="fillLogin('student1', 'student123')">กรอกทันที</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillLogin(user, pass) {
    document.getElementById('username').value = user;
    document.getElementById('password').value = pass;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
