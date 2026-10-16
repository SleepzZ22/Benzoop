/**
 * Lost & Found Campus - Main JavaScript
 * Handles Image Preview, Alerts, Modals, and Interactive Features
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Initialize Bootstrap Tooltips & Popovers
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 2. Image Upload Preview
    const imageInput = document.getElementById('imageInput');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const uploadPrompt = document.getElementById('uploadPrompt');

    if (imageInput && imagePreview && imagePreviewContainer) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                // Validate file size on client (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('ไฟล์มีขนาดเกิน 5 MB กรุณาเลือกไฟล์ภาพขนาดเล็กลง');
                    this.value = '';
                    return;
                }

                // Check file extension
                const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                const fileExt = file.name.split('.').pop().toLowerCase();
                if (!allowedExtensions.includes(fileExt)) {
                    alert('รองรับเฉพาะไฟล์รูปภาพ .jpg, .jpeg, .png, .webp เท่านั้น');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imagePreviewContainer.style.display = 'block';
                    if (uploadPrompt) {
                        uploadPrompt.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 3. Remove/Clear Image Selection
    const removeImageBtn = document.getElementById('removeImageBtn');
    if (removeImageBtn && imageInput && imagePreviewContainer) {
        removeImageBtn.addEventListener('click', function (e) {
            e.preventDefault();
            imageInput.value = '';
            imagePreviewContainer.style.display = 'none';
            if (uploadPrompt) {
                uploadPrompt.style.display = 'block';
            }
        });
    }

    // 4. Auto Dismiss Alerts after 6 seconds
    const autoDismissAlerts = document.querySelectorAll('.alert-dismissible');
    autoDismissAlerts.forEach(function (alertEl) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getInstance(alertEl) || new bootstrap.Alert(alertEl);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 6000);
    });

    // 5. Generic Confirmation Prompt for dangerous actions (delete, reject)
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const message = this.getAttribute('data-confirm') || 'คุณแน่ใจหรือไม่ว่าต้องการดำเนินการนี้?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // 6. Share Item Button (Clipboard copy)
    const shareBtn = document.getElementById('shareItemBtn');
    if (shareBtn) {
        shareBtn.addEventListener('click', function () {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(window.location.href).then(function () {
                    alert('คัดลอกลิงก์ประกาศเรียบร้อยแล้ว สามารถส่งต่อได้ทันที');
                }).catch(function () {
                    prompt('คัดลอกลิงก์ด้านล่างนี้:', window.location.href);
                });
            } else {
                prompt('คัดลอกลิงก์ด้านล่างนี้:', window.location.href);
            }
        });
    }
});
