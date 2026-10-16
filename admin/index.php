<?php
/**
 * Admin Dashboard
 * Lost & Found Campus System
 */
require_once dirname(__DIR__) . '/config/config.php';

// Authorization check: Only Admin allowed
Auth::requireAdmin('login.php');

$itemModel     = new Item();
$categoryModel = new Category();
$userModel     = new User();

// Handle quick status update from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_status') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'CSRF token หมดอายุ');
        redirect('admin/index.php');
    }

    $itemId = (int)($_POST['item_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    if ($itemId && in_array($newStatus, ['active', 'returned', 'pending', 'rejected'])) {
        $itemModel->updateStatus($itemId, $newStatus);
        setFlash('success', "อัปเดตสถานะรายการ #{$itemId} สำเร็จแล้ว");
    }
    redirect('admin/index.php');
}

// Fetch Stats
$stats = $itemModel->getStats();
$totalUsers = $userModel->count();
$totalCategories = $categoryModel->count();

// Fetch Pending items needing admin attention
$pendingItems = $itemModel->getAll(['status' => 'pending', 'include_all_status' => true], 5, 0);

// Fetch Latest 8 items overall
$recentItems = $itemModel->getAll(['include_all_status' => true], 8, 0);

// Fetch Categories for breakdown
$categories = $categoryModel->getAll();

$pageTitle = 'แผงควบคุมผู้ดูแลระบบ (Admin Dashboard)';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="container-fluid py-4 px-md-5">
    <!-- Admin Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger px-2 py-1">ADMINISTRATOR</span>
                <h2 class="fw-bold mb-0">แผงควบคุมระบบ (Dashboard)</h2>
            </div>
            <p class="text-muted small mb-0">ยินดีต้อนรับ <?= e($_SESSION['fullname']) ?> &bull; ภาพรวมระบบจัดการของหายและค้นหาสิ่งของ</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/items.php" class="btn btn-outline-primary btn-sm px-3">
                <i class="bi bi-box-seam me-1"></i>จัดการรายการทั้งหมด
            </a>
            <a href="<?= BASE_URL ?>/admin/categories.php" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-tags me-1"></i>จัดการหมวดหมู่
            </a>
            <a href="<?= BASE_URL ?>/report.php" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-plus-lg me-1"></i>เพิ่มประกาศใหม่
            </a>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-medium">รายการทั้งหมด</span>
                    <span class="badge bg-primary-subtle text-primary p-2 rounded-circle"><i class="bi bi-collection"></i></span>
                </div>
                <h3 class="fw-bold text-dark mb-0"><?= number_format($stats['total']) ?></h3>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-medium">แจ้งของหาย</span>
                    <span class="badge bg-danger-subtle text-danger p-2 rounded-circle"><i class="bi bi-exclamation-octagon"></i></span>
                </div>
                <h3 class="fw-bold text-danger mb-0"><?= number_format($stats['total_lost']) ?></h3>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-medium">แจ้งพบของ</span>
                    <span class="badge bg-success-subtle text-success p-2 rounded-circle"><i class="bi bi-gift"></i></span>
                </div>
                <h3 class="fw-bold text-success mb-0"><?= number_format($stats['total_found']) ?></h3>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-medium">รอการตรวจสอบ</span>
                    <span class="badge bg-warning-subtle text-warning p-2 rounded-circle"><i class="bi bi-clock-history"></i></span>
                </div>
                <h3 class="fw-bold text-warning mb-0"><?= number_format($stats['total_pending']) ?></h3>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-medium">ส่งคืนสำเร็จ</span>
                    <span class="badge bg-info-subtle text-info p-2 rounded-circle"><i class="bi bi-check2-all"></i></span>
                </div>
                <h3 class="fw-bold text-primary mb-0"><?= number_format($stats['total_returned']) ?></h3>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-medium">อัตราส่งคืน</span>
                    <span class="badge bg-warning-subtle text-dark p-2 rounded-circle"><i class="bi bi-trophy"></i></span>
                </div>
                <h3 class="fw-bold text-dark mb-0"><?= $stats['success_rate'] ?>%</h3>
            </div>
        </div>
    </div>

    <!-- Alert for Pending Items if any -->
    <?php if (!empty($pendingItems)): ?>
        <div class="card border-warning border-opacity-50 bg-warning-subtle rounded-4 mb-4 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-exclamation-circle-fill text-warning me-2"></i>มีรายการที่รอการตรวจสอบ (Pending) <?= count($pendingItems) ?> รายการ
                    </h5>
                    <a href="<?= BASE_URL ?>/admin/items.php?status=pending" class="btn btn-sm btn-warning">
                        ดูทั้งหมด
                    </a>
                </div>

                <div class="table-responsive bg-white rounded-3">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>ชื่อรายการ</th>
                                <th>ประเภท</th>
                                <th>สถานที่</th>
                                <th>วันที่</th>
                                <th>ผู้ติดต่อ</th>
                                <th class="text-end">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingItems as $pItem): ?>
                                <tr>
                                    <td><strong>#<?= $pItem['id'] ?></strong></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/detail.php?id=<?= $pItem['id'] ?>" class="text-dark fw-semibold text-decoration-none">
                                            <?= e($pItem['title']) ?>
                                        </a>
                                    </td>
                                    <td><?= typeBadge($pItem['type']) ?></td>
                                    <td><?= e($pItem['location']) ?></td>
                                    <td><?= formatDateThai($pItem['item_date']) ?></td>
                                    <td><?= e($pItem['contact_name']) ?> (<?= e($pItem['contact_phone']) ?>)</td>
                                    <td class="text-end">
                                        <form action="<?= BASE_URL ?>/admin/index.php" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="quick_status">
                                            <input type="hidden" name="item_id" value="<?= $pItem['id'] ?>">
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-sm btn-success py-1 px-2" title="อนุมัติเผยแพร่">
                                                <i class="bi bi-check-lg"></i> อนุมัติ
                                            </button>
                                        </form>
                                        <form action="<?= BASE_URL ?>/admin/index.php" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="quick_status">
                                            <input type="hidden" name="item_id" value="<?= $pItem['id'] ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="ปฏิเสธรายการ">
                                                <i class="bi bi-x-lg"></i>
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
    <?php endif; ?>

    <!-- Recent Items & Category Breakdown Row -->
    <div class="row g-4">
        <!-- Recent Items Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-clock-history text-primary me-2"></i>รายการอัปเดตล่าสุด
                    </h5>
                    <a href="<?= BASE_URL ?>/admin/items.php" class="small text-primary text-decoration-none fw-medium">
                        ดูรายการทั้งหมด (<?= $stats['total'] ?>) <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">ภาพ</th>
                                <th>ชื่อรายการ</th>
                                <th>ประเภท</th>
                                <th>หมวดหมู่</th>
                                <th>สถานะ</th>
                                <th>วันที่แจ้ง</th>
                                <th class="text-end">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentItems as $rItem): ?>
                                <tr>
                                    <td>
                                        <img src="<?= itemImageUrl($rItem['image']) ?>" alt="thumb" class="rounded-2" style="width: 44px; height: 44px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/detail.php?id=<?= $rItem['id'] ?>" class="text-dark fw-semibold text-decoration-none">
                                            <?= e($rItem['title']) ?>
                                        </a>
                                        <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-geo-alt me-1"></i><?= e($rItem['location']) ?></div>
                                    </td>
                                    <td><?= typeBadge($rItem['type']) ?></td>
                                    <td><span class="text-muted"><i class="bi <?= e($rItem['category_icon']) ?> me-1"></i><?= e($rItem['category_name']) ?></span></td>
                                    <td><?= statusBadge($rItem['status']) ?></td>
                                    <td><?= formatDateThai($rItem['created_at']) ?></td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/detail.php?id=<?= $rItem['id'] ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" title="ดูรายละเอียด">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/items.php?edit_id=<?= $rItem['id'] ?>" class="btn btn-sm btn-outline-primary py-1 px-2" title="แก้ไขสถานะ">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Categories & Quick Overview -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-tags-fill text-primary me-2"></i>สถิติตามหมวดหมู่
                    </h5>
                    <a href="<?= BASE_URL ?>/admin/categories.php" class="small text-primary text-decoration-none">จัดการ</a>
                </div>

                <div class="card-body p-3">
                    <div class="list-group list-group-flush">
                        <?php foreach ($categories as $cat): ?>
                            <?php 
                                $percent = $stats['total'] > 0 ? round(($cat['total_items'] / $stats['total']) * 100) : 0;
                            ?>
                            <div class="list-group-item px-0 py-2 border-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-medium text-dark">
                                        <i class="bi <?= e($cat['icon']) ?> text-primary me-1"></i><?= e($cat['name']) ?>
                                    </span>
                                    <span class="badge bg-light text-dark border"><?= $cat['total_items'] ?> รายการ</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="p-3 bg-light rounded-3 mt-3">
                        <h6 class="fw-bold mb-1 small text-dark"><i class="bi bi-info-circle text-info me-1"></i>บันทึกความช่วยเหลือ</h6>
                        <p class="small text-muted mb-0">
                            ผู้ดูแลระบบสามารถปรับสถานะของรายการเป็น "ส่งคืนสำเร็จ (Returned)" เมื่อเจ้าของได้รับสิ่งของเรียบร้อยแล้ว เพื่อบันทึกลงในสถิติความสำเร็จของมหาวิทยาลัย
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
