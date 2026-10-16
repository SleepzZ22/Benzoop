<?php
/**
 * Admin Category Management
 * Lost & Found Campus System
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireAdmin('login.php');

$categoryModel = new Category();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'CSRF token หมดอายุ');
        redirect('admin/categories.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-tag');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            setFlash('danger', 'กรุณาระบุชื่อหมวดหมู่');
        } else {
            $categoryModel->create([
                'name' => $name,
                'icon' => $icon,
                'description' => $description
            ]);
            setFlash('success', 'เพิ่มหมวดหมู่ใหม่สำเร็จแล้ว');
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-tag');
        $description = trim($_POST['description'] ?? '');

        if ($id && !empty($name)) {
            $categoryModel->update($id, [
                'name' => $name,
                'icon' => $icon,
                'description' => $description
            ]);
            setFlash('success', 'ปรับปรุงข้อมูลหมวดหมู่สำเร็จแล้ว');
        } else {
            setFlash('danger', 'ข้อมูลไม่ถูกต้อง');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['category_id'] ?? 0);
        if ($id) {
            try {
                $categoryModel->delete($id);
                setFlash('info', 'ลบหมวดหมู่สำเร็จเรียบร้อย');
            } catch (Exception $e) {
                setFlash('danger', $e->getMessage());
            }
        }
    }

    redirect('admin/categories.php');
}

$categories = $categoryModel->getAll();

// If editing a category
$editCat = null;
if (!empty($_GET['edit_id'])) {
    $editCat = $categoryModel->getById((int)$_GET['edit_id']);
}

$pageTitle = 'จัดการหมวดหมู่สิ่งของ (Admin)';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="container-fluid py-4 px-md-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/index.php" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item active" aria-current="page">จัดการหมวดหมู่</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0"><i class="bi bi-tags-fill text-primary me-2"></i>จัดการหมวดหมู่สิ่งของ</h2>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-speedometer2 me-1"></i>กลับไปแดชบอร์ด
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Form (Add or Edit) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold mb-3 text-dark">
                    <?= $editCat ? '<i class="bi bi-pencil-square text-warning me-2"></i>แก้ไขหมวดหมู่' : '<i class="bi bi-plus-circle text-primary me-2"></i>เพิ่มหมวดหมู่ใหม่' ?>
                </h5>

                <form action="<?= BASE_URL ?>/admin/categories.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="<?= $editCat ? 'update' : 'create' ?>">
                    <?php if ($editCat): ?>
                        <input type="hidden" name="category_id" value="<?= $editCat['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= e($editCat['name'] ?? '') ?>" placeholder="เช่น อุปกรณ์อิเล็กทรอนิกส์" required>
                    </div>

                    <div class="mb-3">
                        <label for="icon" class="form-label">ไอคอน Bootstrap Icon Class</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi <?= e($editCat['icon'] ?? 'bi-tag') ?>" id="iconPreview"></i></span>
                            <input type="text" class="form-control" id="icon" name="icon" value="<?= e($editCat['icon'] ?? 'bi-tag') ?>" placeholder="เช่น bi-laptop, bi-bag, bi-key" oninput="document.getElementById('iconPreview').className='bi ' + this.value">
                        </div>
                        <small class="text-muted">ตัวอย่าง: bi-laptop, bi-bag, bi-key, bi-card-heading, bi-pencil</small>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">คำอธิบายหมวดหมู่</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="ระบุประเภทสิ่งของในหมวดหมู่นี้..."><?= e($editCat['description'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <?= $editCat ? '<i class="bi bi-check-lg me-1"></i>บันทึกการแก้ไข' : '<i class="bi bi-plus-lg me-1"></i>เพิ่มหมวดหมู่' ?>
                        </button>
                        <?php if ($editCat): ?>
                            <a href="<?= BASE_URL ?>/admin/categories.php" class="btn btn-outline-secondary">
                                ยกเลิก
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Category List Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-3">
                    <span class="fw-bold text-dark">หมวดหมู่ทั้งหมด (<?= count($categories) ?> หมวดหมู่)</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#ID</th>
                                <th style="width: 60px;">ไอคอน</th>
                                <th>ชื่อหมวดหมู่</th>
                                <th>คำอธิบาย</th>
                                <th>จำนวนสิ่งของ</th>
                                <th class="text-end" style="width: 140px;">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><strong>#<?= $cat['id'] ?></strong></td>
                                    <td>
                                        <div class="bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 1.1rem;">
                                            <i class="bi <?= e($cat['icon']) ?>"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= e($cat['name']) ?></div>
                                    </td>
                                    <td class="text-muted"><?= e($cat['description'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= $cat['total_items'] ?> รายการ
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/admin/categories.php?edit_id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary py-1 px-2" title="แก้ไข">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form action="<?= BASE_URL ?>/admin/categories.php" method="POST" class="d-inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบหมวดหมู่นี้?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="ลบ" <?= ($cat['total_items'] > 0) ? 'disabled title="ไม่สามารถลบได้เนื่องจากมีรายการใช้งานอยู่"' : '' ?>>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
