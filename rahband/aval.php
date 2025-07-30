<!-- دکمه شناور بازگشت به صفحه اصلی -->
<a href="index.php" class="btn btn-primary rounded-circle floating-btn shadow" title="بازگشت به صفحه اصلی">
    <i class="fas fa-home"></i>
</a>

<style>
    /* استایل دکمه شناور */
    .floating-btn {
        position: fixed;
        bottom: 30px;
        left: 30px;
        width: 60px;
        height: 60px;
        font-size: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        transition: all 0.3s;
    }
    
    .floating-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3) !important;
    }
</style>

<!-- اضافه کردن فونت آیکون (Font Awesome) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Bootstrap JS (اختیاری - برای افکت‌ها) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>