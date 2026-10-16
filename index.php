<?php
/**
 * Homepage
 * Lost & Found Campus System
 */
require_once __DIR__ . '/config/config.php';

$pageTitle = 'หน้าแรก';

$itemModel     = new Item();
$categoryModel = new Category();

// Fetch statistics
$stats = $itemModel->getStats();

// Fetch categories for quick filter
$categories = $categoryModel->getAll();

// Fetch recent items
$recentAll   = $itemModel->getRecent(6);
$recentLost  = $itemModel->getRecent(6, 'lost');
$recentFound = $itemModel->getRecent(6, 'found');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner Section -->
<section class="hero-section">
    <div class="container position-relative text-center" style="z-index: 1;">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold mb-3 shadow-sm">
                    <i class="bi bi-shield-check me-1"></i> ระบบกลางของมหาวิทยาลัย
                </span>
                <h1 class="hero-title mb-3">
                    ทำของหาย หรือ พบสิ่งของ?<br>
                    <span class="text-warning">แจ้งข้อมูลได้ทันที</span> ที่นี่
                </h1>
                <p class="hero-subtitle mx-auto mb-2">
                    แพลตฟอร์มช่วยเหลือสำหรับนิสิต นักศึกษา และบุคลากร ในการตามหาของหายและส่งคืนสิ่งของภายในพื้นที่มหาวิทยาลัย พร้อมระบบ Smart Match จับคู่สิ่งของอัตโนมัติ
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Counters -->
<section class="py-4">
    <div class="container" style="margin-top: -30px; position: relative; z-index: 2;">
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-lost">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                    </div>
                    <div>
                        <div class="stat-value text-danger"><?= number_format($stats['total_lost']) ?></div>
                        <div class="stat-label">ของหายที่แจ้งไว้</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-found">
                        <i class="bi bi-gift-fill"></i>
                    </div>
                    <div>
                        <div class="stat-value text-success"><?= number_format($stats['total_found']) ?></div>
                        <div class="stat-label">สิ่งของที่พบ</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-returned">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div>
                        <div class="stat-value text-primary"><?= number_format($stats['total_returned']) ?></div>
                        <div class="stat-label">ส่งคืนสำเร็จแล้ว</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-rate">
                        <i class="bi bi-trophy-fill"></i>
                    </div>
                    <div>
                        <div class="stat-value text-warning"><?= $stats['success_rate'] ?>%</div>
                        <div class="stat-label">อัตราส่งคืนสำเร็จ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Quick Bar -->
<section class="py-4">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-grid-fill text-primary me-2"></i>เลือกดูตามหมวดหมู่
            </h5>
            <a href="<?= BASE_URL ?>/search.php" class="text-primary small text-decoration-none fw-medium">
                ดูทั้งหมด <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>/search.php" class="category-pill active">
                <i class="bi bi-stars"></i> ทั้งหมด
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL ?>/search.php?category_id=<?= $cat['id'] ?>" class="category-pill">
                    <i class="bi <?= e($cat['icon']) ?>"></i> <?= e($cat['name']) ?>
                    <span class="badge bg-white text-muted border ms-1"><?= $cat['total_items'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Recent Items Section -->
<section class="py-4">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">รายการสิ่งของล่าสุด</h3>
                <p class="text-muted small mb-0">รายการของหายและของที่พบที่อัปเดตล่าสุดภายในมหาวิทยาลัย</p>
            </div>

            <!-- Filter Tabs -->
            <ul class="nav nav-pills align-items-center" id="recentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-3 py-1 small" id="all-tab" data-bs-toggle="pill" data-bs-target="#tab-all" type="button" role="tab">ทั้งหมด</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-3 py-1 small text-danger" id="lost-tab" data-bs-toggle="pill" data-bs-target="#tab-lost" type="button" role="tab">ของหายล่าสุด</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-3 py-1 small text-success" id="found-tab" data-bs-toggle="pill" data-bs-target="#tab-found" type="button" role="tab">พบของล่าสุด</button>
                </li>
                <!-- ปุ่มตัวกรอง ต่อท้ายพบของล่าสุด เมื่อกดแล้วโชว์แถบคัดกรอง -->
                <li class="nav-item ms-1" role="presentation">
                    <button class="nav-link rounded-pill px-3 py-1 small text-primary border border-primary border-opacity-25 bg-white d-flex align-items-center gap-1 shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#quickFilterBar" aria-expanded="false" aria-controls="quickFilterBar" id="toggleFilterBtn" title="เปิด/ปิดแถบตัวกรอง">
                        <i class="bi bi-funnel-fill"></i>
                        <span>ตัวกรอง</span>
                    </button>
                </li>
            </ul>
        </div>

        <!-- Collapsible Quick Filter Toolbar (ภาพ 2: ปรับให้ทุกช่องใส่ข้อความเท่ากัน) -->
        <div class="collapse mb-4" id="quickFilterBar">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4 border-start border-4 border-primary">
                <form action="<?= BASE_URL ?>/search.php" method="GET">
                    <div class="row g-3 align-items-end">
                        <!-- ช่องที่ 1: คำค้นหา -->
                        <div class="col-lg-3 col-md-6 col-12">
                            <label class="form-label small fw-bold text-muted mb-1">คำค้นหา</label>
                            <div class="input-group" style="height: 44px;">
                                <span class="input-group-text bg-light border-end-0 text-muted" style="border-top-left-radius: 10px; border-bottom-left-radius: 10px;">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="q" class="form-control border-start-0" placeholder="ชื่อสิ่งของ, ยี่ห้อ, สถานที่..." style="height: 44px; border-top-right-radius: 10px; border-bottom-right-radius: 10px;">
                            </div>
                        </div>

                        <!-- ช่องที่ 2: หมวดหมู่ (กว้างและสูงเท่ากัน) -->
                        <div class="col-lg-3 col-md-6 col-12">
                            <label class="form-label small fw-bold text-muted mb-1">หมวดหมู่</label>
                            <select name="category_id" class="form-select" style="height: 44px; border-radius: 10px;">
                                <option value="">ทุกหมวดหมู่</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- ช่องที่ 3: สถานที่ (กว้างและสูงเท่ากัน) -->
                        <div class="col-lg-3 col-md-6 col-12">
                            <label class="form-label small fw-bold text-muted mb-1">สถานที่</label>
                            <input type="text" name="location" class="form-control" placeholder="เช่น หอสมุด, โรงอาหาร..." style="height: 44px; border-radius: 10px;">
                        </div>

                        <!-- ช่องที่ 4: ปุ่มกรอง และ ปุ่มปิด (กว้างและสูงเท่ากัน) -->
                        <div class="col-lg-3 col-md-6 col-12">
                            <label class="form-label small fw-bold text-muted mb-1 d-none d-lg-block">&nbsp;</label>
                            <div class="d-flex gap-2" style="height: 44px;">
                                <button type="submit" class="btn btn-primary flex-grow-1 d-flex align-items-center justify-content-center gap-1 shadow-sm fw-medium" style="height: 44px; border-radius: 10px;">
                                    <i class="bi bi-funnel-fill me-1"></i> กรอง
                                </button>
                                <button type="button" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" data-bs-toggle="collapse" data-bs-target="#quickFilterBar" style="height: 44px; width: 44px; border-radius: 10px;" title="ปิดแถบตัวกรอง">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="tab-content" id="recentTabsContent">
            <!-- Tab: All -->
            <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
                <div class="row g-4">
                    <?php if (empty($recentAll)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2">ยังไม่มีรายการสิ่งของในขณะนี้</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentAll as $item): ?>
                            <div class="col-md-6 col-lg-4">
                                <?php include __DIR__ . '/includes/item_card.php'; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab: Lost -->
            <div class="tab-pane fade" id="tab-lost" role="tabpanel">
                <div class="row g-4">
                    <?php if (empty($recentLost)): ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">ยังไม่มีรายการของหาย</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentLost as $item): ?>
                            <div class="col-md-6 col-lg-4">
                                <?php include __DIR__ . '/includes/item_card.php'; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab: Found -->
            <div class="tab-pane fade" id="tab-found" role="tabpanel">
                <div class="row g-4">
                    <?php if (empty($recentFound)): ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">ยังไม่มีรายการพบของ</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentFound as $item): ?>
                            <div class="col-md-6 col-lg-4">
                                <?php include __DIR__ . '/includes/item_card.php'; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/search.php" class="btn btn-outline-primary px-4 py-2">
                ดูรายการทั้งหมดในระบบ <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5 bg-white border-top border-bottom mt-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-medium mb-2">ขั้นตอนการใช้งาน</span>
            <h3 class="fw-bold text-dark">ระบบทำงานอย่างไร?</h3>
            <p class="text-muted small">เพียง 3 ขั้นตอนง่ายๆ ในการตามหาของหายหรือส่งมอบของที่พบ</p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4 rounded-4 bg-light h-100">
                    <div class="brand-icon-box mx-auto mb-3" style="width: 55px; height: 55px; font-size: 1.5rem;">
                        1
                    </div>
                    <h5 class="fw-bold mb-2">แจ้งข้อมูลในระบบ</h5>
                    <p class="text-muted small mb-0">
                        กรอกรายละเอียดสิ่งของ สถานที่ วันที่ และรูปภาพ (ถ้ามี) พร้อมช่องทางติดต่อกลับ
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 rounded-4 bg-light h-100">
                    <div class="brand-icon-box mx-auto mb-3" style="width: 55px; height: 55px; font-size: 1.5rem; background: linear-gradient(135deg, #10b981, #059669);">
                        2
                    </div>
                    <h5 class="fw-bold mb-2">ระบบ Smart Match จับคู่</h5>
                    <p class="text-muted small mb-0">
                        ระบบจะวิเคราะห์หมวดหมู่ สถานที่ วันที่ และคำสำคัญ เพื่อแนะนำสิ่งของที่ตรงกันทันที
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 rounded-4 bg-light h-100">
                    <div class="brand-icon-box mx-auto mb-3" style="width: 55px; height: 55px; font-size: 1.5rem; background: linear-gradient(135deg, #f59e0b, #d97706);">
                        3
                    </div>
                    <h5 class="fw-bold mb-2">ติดต่อและรับของคืน</h5>
                    <p class="text-muted small mb-0">
                        ตรวจสอบยืนยันความเป็นเจ้าของ และนัดรับสิ่งของคืน ณ จุดบริการของมหาวิทยาลัย
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
