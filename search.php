<?php
/**
 * Search and Filter Page
 * Lost & Found Campus System
 */
require_once __DIR__ . '/config/config.php';

$pageTitle = 'ค้นหาสิ่งของ';

$categoryModel = new Category();
$itemModel     = new Item();

$categories = $categoryModel->getAll();

// Parse Query Parameters
$filters = [
    'q'           => trim($_GET['q'] ?? ''),
    'type'        => in_array($_GET['type'] ?? '', ['lost', 'found']) ? $_GET['type'] : '',
    'category_id' => !empty($_GET['category_id']) ? (int)$_GET['category_id'] : '',
    'location'    => trim($_GET['location'] ?? ''),
    'date_from'   => trim($_GET['date_from'] ?? ''),
    'date_to'     => trim($_GET['date_to'] ?? ''),
    'status'      => in_array($_GET['status'] ?? '', ['active', 'returned']) ? $_GET['status'] : '',
    'sort'        => in_array($_GET['sort'] ?? '', ['newest', 'oldest', 'date_desc']) ? $_GET['sort'] : 'newest',
];

// Pagination Settings
$perPage = 9;
$page = max(1, (int)($_GET['page'] ?? 1));
$totalItems = $itemModel->count($filters);
$totalPages = max(1, ceil($totalItems / $perPage));
$offset = ($page - 1) * $perPage;

// Fetch Items
$items = $itemModel->getAll($filters, $perPage, $offset);

// Helper to preserve query string when changing pages
function buildSearchQuery(array $params = []): string {
    $current = $_GET;
    foreach ($params as $k => $v) {
        $current[$k] = $v;
    }
    return http_build_query($current);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <!-- Header Title -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-search text-primary me-2"></i>ค้นหารายการสิ่งของ</h2>
            <p class="text-muted small mb-0">ค้นหาของหายหรือสิ่งของที่มีผู้พบตามหมวดหมู่ สถานที่ และช่วงเวลา</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <a href="<?= BASE_URL ?>/report.php?type=lost" class="btn btn-sm btn-danger px-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>แจ้งของหาย
            </a>
            <a href="<?= BASE_URL ?>/report.php?type=found" class="btn btn-sm btn-success px-3">
                <i class="bi bi-gift-fill me-1"></i>แจ้งพบของ
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="<?= BASE_URL ?>/search.php" method="GET">
                <div class="row g-3">
                    <!-- Main Search Input -->
                    <div class="col-lg-5 col-md-12">
                        <label class="form-label small fw-bold text-muted">คำค้นหา</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" value="<?= e($filters['q']) ?>" placeholder="ระบุชื่อสิ่งของ รายละเอียด หรือสถานที่...">
                        </div>
                    </div>

                    <!-- Type Filter -->
                    <div class="col-lg-2 col-md-4 col-6">
                        <label class="form-label small fw-bold text-muted">ประเภท</label>
                        <select name="type" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="lost" <?= $filters['type'] === 'lost' ? 'selected' : '' ?>>ของหาย</option>
                            <option value="found" <?= $filters['type'] === 'found' ? 'selected' : '' ?>>พบของ</option>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="col-lg-3 col-md-4 col-6">
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

                    <!-- Status Filter -->
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-muted">สถานะ</label>
                        <select name="status" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>กำลังดำเนินการ</option>
                            <option value="returned" <?= $filters['status'] === 'returned' ? 'selected' : '' ?>>ส่งคืนสำเร็จแล้ว</option>
                        </select>
                    </div>

                    <!-- Advanced Filters Toggle & Sub-row -->
                    <div class="col-12">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label small fw-bold text-muted">สถานที่</label>
                                <input type="text" name="location" class="form-control" value="<?= e($filters['location']) ?>" placeholder="เช่น หอสมุด, โรงอาหาร, อาคาร 3...">
                            </div>
                            <div class="col-lg-3 col-md-3 col-6">
                                <label class="form-label small fw-bold text-muted">ตั้งแต่วันที่</label>
                                <input type="date" name="date_from" class="form-control" value="<?= e($filters['date_from']) ?>">
                            </div>
                            <div class="col-lg-3 col-md-3 col-6">
                                <label class="form-label small fw-bold text-muted">ถึงวันที่</label>
                                <input type="date" name="date_to" class="form-control" value="<?= e($filters['date_to']) ?>">
                            </div>
                            <div class="col-lg-2 col-md-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-filter me-1"></i>ค้นหา
                                </button>
                                <a href="<?= BASE_URL ?>/search.php" class="btn btn-outline-secondary" title="ล้างตัวกรอง">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="fw-bold text-dark">ผลการค้นหา:</span>
            <span class="badge bg-primary rounded-pill ms-1 px-3 py-1 fs-6"><?= number_format($totalItems) ?></span> รายการ
            <?php if (!empty($filters['q'])): ?>
                <span class="text-muted small ms-2">สำหรับคำค้น "<strong><?= e($filters['q']) ?></strong>"</span>
            <?php endif; ?>
        </div>

        <!-- Sorting -->
        <div class="d-flex align-items-center gap-2">
            <label class="small text-muted d-none d-sm-block">เรียงตาม:</label>
            <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='?<?= buildSearchQuery() ?>&sort=' + this.value">
                <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>ประกาศล่าสุด</option>
                <option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>ประกาศเก่าสุด</option>
                <option value="date_desc" <?= $filters['sort'] === 'date_desc' ? 'selected' : '' ?>>วันที่เกิดเหตุล่าสุด</option>
            </select>
        </div>
    </div>

    <!-- Items Grid -->
    <div class="row g-4 mb-5">
        <?php if (empty($items)): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                    <i class="bi bi-search fs-1 text-muted opacity-50 mb-3"></i>
                    <h5 class="fw-bold text-dark">ไม่พบรายการสิ่งของที่ตรงกับเงื่อนไข</h5>
                    <p class="text-muted small mb-4">ลองปรับเปลี่ยนคำค้นหา หรือล้างตัวกรองเพื่อค้นหาใหม่อีกครั้ง</p>
                    <div>
                        <a href="<?= BASE_URL ?>/search.php" class="btn btn-outline-primary px-4">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>ล้างตัวกรองทั้งหมด
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <div class="col-md-6 col-lg-4">
                    <?php include __DIR__ . '/includes/item_card.php'; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Search pagination">
            <ul class="pagination justify-content-center">
                <!-- Previous Page -->
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= buildSearchQuery(['page' => $page - 1]) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo; ก่อนหน้า</span>
                    </a>
                </li>

                <!-- Page numbers -->
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?= ($p === $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= buildSearchQuery(['page' => $p]) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Next Page -->
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= buildSearchQuery(['page' => $page + 1]) ?>" aria-label="Next">
                        <span aria-hidden="true">ถัดไป &raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
