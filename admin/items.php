<?php
/**
 * Admin Item Management
 * Lost & Found Campus System
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::requireAdmin('login.php');

$itemModel     = new Item();
$categoryModel = new Category();
$categories    = $categoryModel->getAll();

// Handle Actions (Status Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'CSRF token หมดอายุ');
        redirect('admin/items.php');
    }

    $action = $_POST['action'] ?? '';
    $itemId = (int)($_POST['item_id'] ?? 0);

    if ($action === 'update_status') {
        $status = $_POST['status'] ?? '';
        if ($itemId && in_array($status, ['pending', 'active', 'returned', 'rejected'])) {
            $itemModel->updateStatus($itemId, $status);
            setFlash('success', "อัปเดตสถานะของรายการ #{$itemId} สำเร็จแล้ว");
        }
    } elseif ($action === 'delete') {
        if ($itemId) {
            $itemModel->delete($itemId);
            setFlash('info', "ลบรายการ #{$itemId} เรียบร้อยแล้ว");
        }
    }

    redirect('admin/items.php');
}

// Parse Filters
$filters = [
    'q'                  => trim($_GET['q'] ?? ''),
    'type'               => in_array($_GET['type'] ?? '', ['lost', 'found']) ? $_GET['type'] : '',
    'category_id'        => !empty($_GET['category_id']) ? (int)$_GET['category_id'] : '',
    'status'             => in_array($_GET['status'] ?? '', ['pending', 'active', 'returned', 'rejected']) ? $_GET['status'] : '',
    'include_all_status' => true // Admin sees all statuses
];

$perPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$totalItems = $itemModel->count($filters);
$totalPages = max(1, ceil($totalItems / $perPage));
$offset = ($page - 1) * $perPage;

$items = $itemModel->getAll($filters, $perPage, $offset);

function buildAdminQuery(array $params = []): string {
    $current = $_GET;
    foreach ($params as $k => $v) {
        $current[$k] = $v;
    }
    return http_build_query($current);
}

$pageTitle = 'จัดการรายการสิ่งของ (Admin)';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="container-fluid py-4 px-md-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/index.php" class="text-decoration-none">แดชบอร์ด</a></li>
                    <li class="breadcrumb-item active" aria-current="page">จัดการรายการสิ่งของ</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0"><i class="bi bi-box-seam text-primary me-2"></i>จัดการรายการสิ่งของทั้งหมด</h2>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <a href="<?= BASE_URL ?>/report.php" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-plus-lg me-1"></i>เพิ่มประกาศใหม่
            </a>
            <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-speedometer2 me-1"></i>กลับไปแดชบอร์ด
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="<?= BASE_URL ?>/admin/items.php" method="GET">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-4 col-md-12">
                        <label class="form-label small fw-bold text-muted">ค้นหา</label>
                        <input type="text" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="ชื่อสิ่งของ, รายละเอียด, สถานที่...">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label class="form-label small fw-bold text-muted">ประเภท</label>
                        <select name="type" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="lost" <?= $filters['type'] === 'lost' ? 'selected' : '' ?>>ของหาย</option>
                            <option value="found" <?= $filters['type'] === 'found' ? 'selected' : '' ?>>พบของ</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label class="form-label small fw-bold text-muted">สถานะ</label>
                        <select name="status" class="form-select">
                            <option value="">ทุกสถานะ</option>
                            <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>รอตรวจสอบ (Pending)</option>
                            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>กำลังดำเนินการ (Active)</option>
                            <option value="returned" <?= $filters['status'] === 'returned' ? 'selected' : '' ?>>ส่งคืนสำเร็จ (Returned)</option>
                            <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>ปิดรายการ (Rejected)</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4 col-8">
                        <label class="form-label small fw-bold text-muted">หมวดหมู่</label>
                        <select name="category_id" class="form-select">
                            <option value="">ทุกหมวดหมู่</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ((string)$filters['category_id'] === (string)$cat['id']) ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-2 col-4 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100" title="ค้นหา">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="<?= BASE_URL ?>/admin/items.php" class="btn btn-outline-secondary" title="ล้าง">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark">
                รายการสิ่งของทั้งหมด (<?= number_format($totalItems) ?> รายการ)
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#ID</th>
                        <th style="width: 60px;">รูปภาพ</th>
                        <th>ชื่อสิ่งของ</th>
                        <th>ประเภท</th>
                        <th>หมวดหมู่</th>
                        <th>สถานที่</th>
                        <th>วันที่</th>
                        <th>ผู้ติดต่อ</th>
                        <th>สถานะ</th>
                        <th class="text-end" style="width: 180px;">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                ไม่พบข้อมูลรายการที่ตรงกับเงื่อนไข
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><strong>#<?= $item['id'] ?></strong></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/detail.php?id=<?= $item['id'] ?>" target="_blank">
                                        <img src="<?= itemImageUrl($item['image']) ?>" alt="thumb" class="rounded-2" style="width: 48px; height: 48px; object-fit: cover;">
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= e($item['title']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem; max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= e($item['description']) ?>
                                    </div>
                                </td>
                                <td><?= typeBadge($item['type']) ?></td>
                                <td>
                                    <span class="text-secondary">
                                        <i class="bi <?= e($item['category_icon']) ?> me-1"></i><?= e($item['category_name']) ?>
                                    </span>
                                </td>
                                <td><?= e($item['location']) ?></td>
                                <td><?= formatDateThai($item['item_date']) ?></td>
                                <td>
                                    <div><?= e($item['contact_name']) ?></div>
                                    <small class="text-muted"><?= e($item['contact_phone']) ?></small>
                                </td>
                                <td>
                                    <!-- Inline Quick Status Form -->
                                    <form action="<?= BASE_URL ?>/admin/items.php" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <select name="status" class="form-select form-select-sm" style="font-size: 0.78rem; padding: 0.2rem 0.5rem;" onchange="this.form.submit()">
                                            <option value="pending" <?= $item['status'] === 'pending' ? 'selected' : '' ?>>รอตรวจสอบ (Pending)</option>
                                            <option value="active" <?= $item['status'] === 'active' ? 'selected' : '' ?>>กำลังดำเนินการ (Active)</option>
                                            <option value="returned" <?= $item['status'] === 'returned' ? 'selected' : '' ?>>ส่งคืนสำเร็จ (Returned)</option>
                                            <option value="rejected" <?= $item['status'] === 'rejected' ? 'selected' : '' ?>>ปฏิเสธ (Rejected)</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/detail.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary py-1 px-2" title="ดูหน้ารายละเอียด" target="_blank">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    
                                    <form action="<?= BASE_URL ?>/admin/items.php" method="POST" class="d-inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้อย่างถาวร? การกระทำนี้ไม่สามารถย้อนกลับได้');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="ลบรายการนี้">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white p-3 border-top">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= buildAdminQuery(['page' => $page - 1]) ?>">&laquo; ก่อนหน้า</a>
                    </li>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= ($p === $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= buildAdminQuery(['page' => $p]) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= buildAdminQuery(['page' => $page + 1]) ?>">ถัดไป &raquo;</a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
