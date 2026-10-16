<?php
/**
 * Navigation Bar Template
 * Lost & Found Campus System
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
$isAdminArea = ($currentDir === 'admin');
$isLoggedIn  = Auth::check();
$isAdmin     = Auth::isAdmin();
$currentUser = Auth::user();
?>
<nav class="navbar navbar-expand-lg navbar-campus sticky-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
            <div class="brand-icon-box">
                <i class="bi bi-compass-fill"></i>
            </div>
            <div>
                <span class="fw-bold tracking-tight"><?= e(APP_NAME) ?></span>
                <span class="d-none d-md-block text-muted small" style="font-size: 0.75rem; margin-top: -3px;"><?= e(APP_SUBTITLE) ?></span>
            </div>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-2 text-primary"></i>
        </button>

        <!-- Nav Items -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'index.php' && !$isAdminArea) ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">
                        <i class="bi bi-house-door me-1"></i>หน้าแรก
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'search.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/search.php">
                        <i class="bi bi-search me-1"></i>ค้นหาสิ่งของ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger fw-semibold <?= ($currentPage === 'report.php' && ($_GET['type'] ?? '') === 'lost') ? 'active' : '' ?>" href="<?= BASE_URL ?>/report.php?type=lost">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>แจ้งของหาย
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-success fw-semibold <?= ($currentPage === 'report.php' && ($_GET['type'] ?? '') === 'found') ? 'active' : '' ?>" href="<?= BASE_URL ?>/report.php?type=found">
                        <i class="bi bi-plus-circle-fill me-1"></i>แจ้งพบของ
                    </a>
                </li>
            </ul>

            <!-- Right Actions / User Menu -->
            <div class="d-flex align-items-center gap-2">
                <?php if ($isLoggedIn): ?>
                    <!-- Logged in User Menu -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar-circle bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.85rem;">
                                <?= strtoupper(mb_substr($currentUser['fullname'] ?? 'U', 0, 1, 'UTF-8')) ?>
                            </span>
                            <span class="d-none d-sm-inline fw-medium"><?= e($currentUser['fullname']) ?></span>
                            <?php if ($isAdmin): ?>
                                <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">ADMIN</span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userMenuDropdown" style="border-radius: 12px;">
                            <li><h6 class="dropdown-header">เข้าสู่ระบบในชื่อ: <?= e($currentUser['username']) ?></h6></li>
                            
                            <?php if ($isAdmin): ?>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/index.php"><i class="bi bi-speedometer2 text-primary me-2"></i>แดชบอร์ดผู้ดูแล (Admin)</a></li>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/items.php"><i class="bi bi-box-seam text-info me-2"></i>จัดการรายการสิ่งของ</a></li>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/categories.php"><i class="bi bi-tags text-warning me-2"></i>จัดการหมวดหมู่</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>

                            <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/my-items.php"><i class="bi bi-journal-check text-success me-2"></i>รายการที่ฉันแจ้ง</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Guest View -->
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline-primary btn-sm px-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i>เข้าสู่ระบบ
                    </a>
                    <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-person-plus me-1"></i>ลงทะเบียน
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
