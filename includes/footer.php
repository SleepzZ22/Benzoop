<?php
/**
 * Footer Template
 * Lost & Found Campus System
 */
?>
</main>

<footer class="campus-footer">
    <div class="container">
        <div class="row g-4 mb-4">
            <!-- Brand & Mission -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-icon-box" style="width: 34px; height: 34px; font-size: 1rem;">
                        <i class="bi bi-compass-fill"></i>
                    </div>
                    <span class="fs-5 fw-bold text-white"><?= e(APP_NAME) ?></span>
                </div>
                <p class="small text-muted mb-3">
                    ศูนย์กลางรับแจ้งสิ่งของสูญหายและส่งคืนสิ่งของที่พบภายในพื้นที่มหาวิทยาลัย สะดวก รวดเร็ว ด้วยระบบจับคู่สิ่งของอัจฉริยะ (Smart Match) เพื่อช่วยให้นักศึกษาและบุคลากรได้รับของคืนอย่างปลอดภัย
                </p>
                <div class="d-flex gap-2">
                    <span class="badge bg-secondary-subtle text-light border border-secondary">PHP 8+ OOP</span>
                    <span class="badge bg-secondary-subtle text-light border border-secondary">MySQL PDO</span>
                    <span class="badge bg-secondary-subtle text-light border border-secondary">Bootstrap 5</span>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 col-6">
                <h5>เมนูด่วน</h5>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a href="<?= BASE_URL ?>/index.php"><i class="bi bi-chevron-right me-1"></i>หน้าแรก</a></li>
                    <li><a href="<?= BASE_URL ?>/search.php"><i class="bi bi-chevron-right me-1"></i>ค้นหาสิ่งของ</a></li>
                    <li><a href="<?= BASE_URL ?>/report.php?type=lost"><i class="bi bi-chevron-right me-1 text-danger"></i>แจ้งของหาย</a></li>
                    <li><a href="<?= BASE_URL ?>/report.php?type=found"><i class="bi bi-chevron-right me-1 text-success"></i>แจ้งพบของ</a></li>
                    <li><a href="<?= BASE_URL ?>/login.php"><i class="bi bi-chevron-right me-1"></i>เข้าสู่ระบบ</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div class="col-lg-3 col-md-6 col-6">
                <h5>หมวดหมู่หลัก</h5>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a href="<?= BASE_URL ?>/search.php?category_id=1"><i class="bi bi-laptop me-1"></i>อุปกรณ์อิเล็กทรอนิกส์</a></li>
                    <li><a href="<?= BASE_URL ?>/search.php?category_id=2"><i class="bi bi-card-heading me-1"></i>เอกสารและบัตรสำคัญ</a></li>
                    <li><a href="<?= BASE_URL ?>/search.php?category_id=3"><i class="bi bi-bag me-1"></i>กระเป๋าและสัมภาระ</a></li>
                    <li><a href="<?= BASE_URL ?>/search.php?category_id=5"><i class="bi bi-key me-1"></i>กุญแจและยานพาหนะ</a></li>
                    <li><a href="<?= BASE_URL ?>/search.php?category_id=6"><i class="bi bi-pencil me-1"></i>เครื่องเขียนและของใช้</a></li>
                </ul>
            </div>

            <!-- Contact / Help Center -->
            <div class="col-lg-3 col-md-6">
                <h5>ศูนย์ช่วยเหลือนักศึกษา</h5>
                <p class="small text-muted mb-2">
                    <i class="bi bi-geo-alt-fill text-primary me-2"></i>กองพัฒนานักศึกษา ชั้น 1 อาคารกิจกรรมนักศึกษา
                </p>
                <p class="small text-muted mb-2">
                    <i class="bi bi-telephone-fill text-success me-2"></i>เบอร์โทรศัพท์: 02-123-4567 ต่อ 110-112
                </p>
                <p class="small text-muted mb-2">
                    <i class="bi bi-clock-fill text-warning me-2"></i>เวลาทำการ: จันทร์ - ศุกร์ 08:30 - 16:30 น.
                </p>
                <p class="small text-muted mb-0">
                    <i class="bi bi-shield-check text-info me-2"></i>กรณีฉุกเฉิน รปภ. มหาวิทยาลัย: 02-123-4500 (24 ชม.)
                </p>
            </div>
        </div>

        <hr class="border-secondary opacity-25">

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small text-muted py-2">
            <div>
                &copy; <?= date('Y') ?> <strong><?= e(APP_NAME) ?></strong>. พัฒนาขึ้นเพื่อการศึกษา (PHP OOP Project).
            </div>
            <div class="mt-2 mt-sm-0">
                <span class="text-secondary">ระบบรักษาความปลอดภัย: CSRF Protection &bull; PDO Prepared &bull; Password Hash</span>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Custom JS -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
