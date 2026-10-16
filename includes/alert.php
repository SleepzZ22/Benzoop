<?php
/**
 * Flash Alert Notification Component
 * Lost & Found Campus System
 */
$flash = getFlash();
if ($flash): 
    $iconMap = [
        'success' => 'bi-check-circle-fill',
        'danger'  => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        'info'    => 'bi-info-circle-fill'
    ];
    $icon = $iconMap[$flash['type']] ?? 'bi-info-circle-fill';
?>
<div class="container mt-3">
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert" style="border-radius: 12px;">
        <i class="bi <?= $icon ?> fs-5 me-2"></i>
        <div class="flex-grow-1">
            <?= $flash['message'] ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>
