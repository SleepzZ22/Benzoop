<?php
/**
 * Item Detail View with Smart Match Recommendation
 * Lost & Found Campus System
 */
require_once __DIR__ . '/config/config.php';

$itemId = (int)($_GET['id'] ?? 0);
if ($itemId <= 0) {
    setFlash('danger', 'ไม่พบรหัสรายการสิ่งของที่ระบุ');
    redirect('search.php');
}

$itemModel   = new Item();
$matchFinder = new MatchFinder();

// Fetch item with relations
$item = $itemModel->getById($itemId);
if (!$item) {
    setFlash('danger', 'ไม่พบข้อมูลรายการสิ่งของนี้ในระบบ หรืออาจถูกลบไปแล้ว');
    redirect('search.php');
}

// Check authorization for status change (owner or admin)
$currentUserId = Auth::id();
$isAdmin       = Auth::isAdmin();
$isOwner       = ($currentUserId && (int)$item['user_id'] === $currentUserId);
$canManage     = ($isAdmin || $isOwner);

// Handle direct quick status change (e.g. mark as returned)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'ความปลอดภัยผิดพลาด (CSRF token หมดอายุ)');
        redirect("detail.php?id={$itemId}");
    }

    if (!$canManage) {
        setFlash('danger', 'คุณไม่มีสิทธิ์ในการจัดการรายการนี้');
        redirect("detail.php?id={$itemId}");
    }

    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, ['active', 'returned', 'pending', 'rejected'])) {
        $itemModel->updateStatus($itemId, $newStatus);
        setFlash('success', 'อัปเดตสถานะของรายการสำเร็จเรียบร้อย');
        redirect("detail.php?id={$itemId}");
    }
}

// Find Smart Matches (AI / Multi-factor algorithm)
$matches = $matchFinder->findMatches($item, 4);

$pageTitle = $item['title'];
$imageUrl = itemImageUrl($item['image']);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">หน้าแรก</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/search.php" class="text-decoration-none">ค้นหาสิ่งของ</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/search.php?category_id=<?= $item['category_id'] ?>" class="text-decoration-none"><?= e($item['category_name']) ?></a></li>
            <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 250px;"><?= e($item['title']) ?></li>
        </ol>
    </nav>

    <!-- Main Item Header & Grid -->
    <div class="row g-4 mb-5">
        <!-- Left: Image -->
        <div class="col-lg-5">
            <div class="detail-image-box position-relative">
                <img src="<?= $imageUrl ?>" alt="<?= e($item['title']) ?>" id="mainItemImage">
                <div class="position-absolute top-0 start-0 m-3 d-flex gap-2">
                    <?= typeBadge($item['type']) ?>
                    <?= statusBadge($item['status']) ?>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="d-flex gap-2 mt-3">
                <button type="button" class="btn btn-outline-secondary w-50" id="shareItemBtn">
                    <i class="bi bi-share me-1"></i>แชร์ประกาศ
                </button>
                <button type="button" class="btn btn-outline-secondary w-50" onclick="window.print();">
                    <i class="bi bi-printer me-1"></i>พิมพ์ใบประกาศ
                </button>
            </div>

            <!-- Owner / Admin Controls -->
            <?php if ($canManage): ?>
                <div class="card border-primary border-opacity-50 bg-primary-subtle rounded-4 mt-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-bold small">
                            <i class="bi bi-gear-fill"></i> แผงจัดการรายการ (เจ้าของ / ผู้ดูแล)
                        </div>
                        <form action="<?= BASE_URL ?>/detail.php?id=<?= $itemId ?>" method="POST" class="d-flex gap-2 align-items-center">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update_status">
                            <select name="status" class="form-select form-select-sm">
                                <option value="active" <?= $item['status'] === 'active' ? 'selected' : '' ?>>กำลังดำเนินการ (Active)</option>
                                <option value="returned" <?= $item['status'] === 'returned' ? 'selected' : '' ?>>ส่งคืนสำเร็จ (Returned)</option>
                                <option value="pending" <?= $item['status'] === 'pending' ? 'selected' : '' ?>>รอตรวจสอบ (Pending)</option>
                                <option value="rejected" <?= $item['status'] === 'rejected' ? 'selected' : '' ?>>ปิดรายการ (Rejected)</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                                อัปเดตสถานะ
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Information & Contact -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <!-- Meta Tags -->
                <div class="d-flex flex-wrap align-items-center gap-2 text-muted small mb-2">
                    <span class="badge bg-light text-dark border">
                        <i class="bi <?= e($item['category_icon']) ?> me-1 text-primary"></i><?= e($item['category_name']) ?>
                    </span>
                    <span>&bull;</span>
                    <span><i class="bi bi-calendar3 me-1"></i><?= ($item['type'] === 'lost' ? 'วันที่ทำหาย: ' : 'วันที่พบ: ') . formatDateThai($item['item_date']) ?></span>
                    <span>&bull;</span>
                    <span><i class="bi bi-clock me-1"></i>ลงประกาศ: <?= formatDateThai($item['created_at'], true) ?></span>
                </div>

                <h2 class="fw-bold text-dark mb-3"><?= e($item['title']) ?></h2>

                <!-- Location Highlight -->
                <div class="p-3 bg-light rounded-3 mb-4 d-flex align-items-start gap-3">
                    <i class="bi bi-geo-alt-fill text-danger fs-4 mt-1"></i>
                    <div>
                        <div class="small text-muted fw-medium"><?= $item['type'] === 'lost' ? 'สถานที่คาดว่าทำหาย' : 'สถานที่ที่พบสิ่งของ' ?></div>
                        <div class="fs-6 fw-semibold text-dark"><?= e($item['location']) ?></div>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <h5 class="fw-bold text-dark mb-2">รายละเอียดสิ่งของ</h5>
                    <div class="text-secondary p-3 bg-light-subtle rounded-3" style="line-height: 1.7; white-space: pre-line;">
                        <?= e($item['description']) ?>
                    </div>
                </div>

                <!-- Contact Card -->
                <div class="contact-box mt-auto">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-telephone-inbound-fill text-success me-2"></i>ข้อมูลสำหรับติดต่อรับสิ่งของ
                        </h6>
                        <span class="badge bg-success-subtle text-success small">พร้อมติดต่อ</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">ชื่อผู้ติดต่อ / ผู้แจ้ง</small>
                            <span class="fw-semibold text-dark"><i class="bi bi-person me-1"></i><?= e($item['contact_name']) ?></span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">เบอร์โทรศัพท์</small>
                            <a href="tel:<?= e($item['contact_phone']) ?>" class="fw-bold text-primary text-decoration-none fs-6">
                                <i class="bi bi-telephone-fill me-1"></i><?= e($item['contact_phone']) ?>
                            </a>
                        </div>
                        <?php if (!empty($item['contact_email'])): ?>
                            <div class="col-12">
                                <small class="text-muted d-block">อีเมล</small>
                                <a href="mailto:<?= e($item['contact_email']) ?>" class="text-secondary text-decoration-none small">
                                    <i class="bi bi-envelope-fill me-1"></i><?= e($item['contact_email']) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart Match Recommendations (Highlight Feature) -->
    <div class="mt-5">
        <div class="smart-match-card">
            <div class="smart-match-header">
                <div>
                    <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-stars fs-4 text-warning"></i>
                        ระบบ Smart Match : สิ่งของที่อาจตรงกัน
                    </h4>
                    <p class="small mb-0 opacity-75">
                        ระบบวิเคราะห์จากหมวดหมู่ สถานที่ วันที่ และคำสำคัญเพื่อแนะนำรายการที่อาจเป็นของชิ้นเดียวกัน
                    </p>
                </div>
                <span class="match-score-badge d-none d-sm-inline-block">
                    <i class="bi bi-cpu me-1"></i>อัลกอริทึมจับคู่อัจฉริยะ
                </span>
            </div>

            <div class="p-4 bg-light">
                <?php if (empty($matches)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-search fs-2 mb-2 d-block opacity-50"></i>
                        <p class="mb-0">ยังไม่พบรายการที่ตรงกับเงื่อนไขในขณะนี้ เมื่อมีผู้แจ้งข้อมูลใหม่ ระบบจะทำการจับคู่อัตโนมัติ</p>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($matches as $match): ?>
                            <div class="col-lg-6">
                                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                                    <div class="row g-0 h-100">
                                        <div class="col-4 position-relative bg-light">
                                            <img src="<?= itemImageUrl($match['image']) ?>" alt="<?= e($match['title']) ?>" class="w-100 h-100" style="object-fit: cover; min-height: 140px;">
                                            <div class="position-absolute top-0 start-0 m-2">
                                                <span class="badge bg-dark bg-opacity-75 text-white" style="font-size: 0.75rem;">
                                                    ตรงกัน <?= $match['match_score'] ?>%
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-8">
                                            <div class="card-body p-3 d-flex flex-column h-100">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <?= typeBadge($match['type']) ?>
                                                    <small class="text-muted"><?= formatDateThai($match['item_date']) ?></small>
                                                </div>

                                                <h6 class="fw-bold mb-1 text-truncate">
                                                    <a href="<?= BASE_URL ?>/detail.php?id=<?= $match['id'] ?>" class="text-dark text-decoration-none">
                                                        <?= e($match['title']) ?>
                                                    </a>
                                                </h6>

                                                <div class="small text-muted text-truncate mb-2">
                                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= e($match['location']) ?>
                                                </div>

                                                <!-- Match Reasons Badges -->
                                                <div class="d-flex flex-wrap gap-1 mb-2">
                                                    <?php foreach ($match['match_reasons'] as $reason): ?>
                                                        <span class="badge <?= $reason['class'] ?>" style="font-size: 0.7rem;">
                                                            <i class="bi <?= $reason['icon'] ?> me-1"></i><?= $reason['label'] ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>

                                                <div class="mt-auto text-end">
                                                    <a href="<?= BASE_URL ?>/detail.php?id=<?= $match['id'] ?>" class="btn btn-sm btn-outline-primary py-1 px-3" style="font-size: 0.78rem;">
                                                        ตรวจสอบรายการนี้ <i class="bi bi-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
