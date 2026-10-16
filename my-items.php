<?php
/**
 * User My Reported Items Page
 * Lost & Found Campus System
 */
require_once __DIR__ . '/config/config.php';

Auth::requireLogin('login.php');

$userId = Auth::id();
$itemModel = new Item();

// Handle status change by owner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'CSRF token หมดอายุ');
        redirect('my-items.php');
    }

    $itemId = (int)($_POST['item_id'] ?? 0);
    $item = $itemModel->getById($itemId);

    if ($item && (int)$item['user_id'] === $userId) {
        $action = $_POST['action'];
        if ($action === 'mark_returned') {
            $itemModel->updateStatus($itemId, 'returned');
            setFlash('success', 'ปรับปรุงสถานะเป็น "ส่งคืนสำเร็จ" เรียบร้อยแล้ว');
        } elseif ($action === 'delete') {
            $itemModel->delete($itemId);
            setFlash('info', 'ลบรายการสิ่งของเรียบร้อยแล้ว');
        }
    }
    redirect('my-items.php');
}

$myItems = $itemModel->getAll(['user_id' => $userId, 'include_all_status' => true], 50, 0);

$pageTitle = 'รายการที่ฉันแจ้ง';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-journal-check text-primary me-2"></i>รายการที่ฉันแจ้ง</h2>
            <p class="text-muted small mb-0">จัดการรายการของหายและพบของที่คุณได้บันทึกไว้ในระบบ</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <a href="<?= BASE_URL ?>/report.php?type=lost" class="btn btn-sm btn-danger px-3">
                <i class="bi bi-plus-circle me-1"></i>แจ้งของหายใหม่
            </a>
            <a href="<?= BASE_URL ?>/report.php?type=found" class="btn btn-sm btn-success px-3">
                <i class="bi bi-plus-circle me-1"></i>แจ้งพบของใหม่
            </a>
        </div>
    </div>

    <?php if (empty($myItems)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4">
            <i class="bi bi-folder2-open fs-1 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-dark">ยังไม่มีรายการที่คุณแจ้งไว้</h5>
            <p class="text-muted small mb-4">คุณสามารถแจ้งของหายหรือของที่พบภายในมหาวิทยาลัยได้ทันที</p>
            <div>
                <a href="<?= BASE_URL ?>/report.php" class="btn btn-primary px-4">
                    <i class="bi bi-plus-lg me-1"></i>เริ่มแจ้งรายการแรก
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($myItems as $item): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                        <div class="position-relative" style="height: 180px; overflow: hidden; background: #f1f5f9;">
                            <img src="<?= itemImageUrl($item['image']) ?>" alt="<?= e($item['title']) ?>" class="w-100 h-100" style="object-fit: cover;">
                            <div class="position-absolute top-0 start-0 m-2 d-flex gap-1">
                                <?= typeBadge($item['type']) ?>
                                <?= statusBadge($item['status']) ?>
                            </div>
                        </div>

                        <div class="card-body p-3 d-flex flex-column">
                            <div class="text-muted small mb-1">
                                <i class="bi bi-calendar3 me-1"></i><?= formatDateThai($item['item_date']) ?>
                                &bull; <i class="bi <?= e($item['category_icon']) ?> me-1"></i><?= e($item['category_name']) ?>
                            </div>
                            <h5 class="fw-bold fs-6 mb-2">
                                <a href="<?= BASE_URL ?>/detail.php?id=<?= $item['id'] ?>" class="text-dark text-decoration-none">
                                    <?= e($item['title']) ?>
                                </a>
                            </h5>
                            <p class="small text-muted mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= e($item['description']) ?>
                            </p>

                            <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                <a href="<?= BASE_URL ?>/detail.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>ดูรายละเอียด
                                </a>

                                <div class="d-flex gap-1">
                                    <?php if ($item['status'] !== 'returned'): ?>
                                        <form action="<?= BASE_URL ?>/my-items.php" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="mark_returned">
                                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="ส่งคืนเรียบร้อยแล้ว">
                                                <i class="bi bi-check2"></i> ได้คืนแล้ว
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form action="<?= BASE_URL ?>/my-items.php" method="POST" class="d-inline" onsubmit="return confirm('คุณต้องการลบรายการนี้ใช่หรือไม่?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบรายการ">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
