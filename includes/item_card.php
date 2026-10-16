<?php
/**
 * Item Card Component
 * Lost & Found Campus System
 * Expects $item array in scope
 */
if (!isset($item)) return;

$imageUrl = itemImageUrl($item['image'] ?? null);
$detailUrl = BASE_URL . '/detail.php?id=' . $item['id'];
?>
<div class="item-card">
    <div class="item-card-image-wrapper">
        <a href="<?= $detailUrl ?>">
            <img src="<?= $imageUrl ?>" alt="<?= e($item['title']) ?>" class="item-card-image" loading="lazy">
        </a>
        <div class="item-card-badges">
            <?= typeBadge($item['type']) ?>
            <?= statusBadge($item['status']) ?>
        </div>
    </div>
    <div class="item-card-body">
        <div class="item-card-meta">
            <span class="text-primary fw-medium"><i class="bi <?= e($item['category_icon'] ?? 'bi-tag') ?> me-1"></i><?= e($item['category_name']) ?></span>
            <span>&bull;</span>
            <span><i class="bi bi-calendar3 me-1"></i><?= formatDateThai($item['item_date']) ?></span>
        </div>
        <h4 class="item-card-title">
            <a href="<?= $detailUrl ?>"><?= e($item['title']) ?></a>
        </h4>
        <div class="text-muted small mb-2 d-flex align-items-center">
            <i class="bi bi-geo-alt-fill text-danger me-1 flex-shrink-0"></i>
            <span class="text-truncate"><?= e($item['location']) ?></span>
        </div>
        <p class="item-card-desc">
            <?= e($item['description']) ?>
        </p>
        <div class="item-card-footer">
            <span><i class="bi bi-person me-1"></i><?= e($item['contact_name']) ?></span>
            <a href="<?= $detailUrl ?>" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 0.8rem;">
                ดูรายละเอียด <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
