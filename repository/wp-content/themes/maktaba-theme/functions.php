<?php
/**
 * functions.php — Child Theme (maktaba-theme)
 *
 * قاعدة معمارية إلزامية (architecture/boundaries.md):
 * هذا الملف "تحميل فقط" — يستدعي Shortcodes الجاهزة من maktaba-core
 * عبر نقاط ربط WooCommerce القياسية. لا استعلام SQL، لا حساب، لا قراءة
 * لأي حقل داخلي (_mkt_purchase_price / _mkt_shipping_cost ممنوعة هنا تماماً).
 */

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

add_action( "wp_enqueue_scripts", function () {
    /**
     * الهوية البصرية (خطوط عربية من Google Fonts) — Amiri للعناوين
     * (خط نسخي كلاسيكي مناسب لمكتبة كتب) وCairo للنصوص (خط عربي حديث
     * وواضح القراءة). تحميل عبر رابط CDN مباشر التزاماً بمبدأ Zero-Cost
     * (بلا أي أداة بناء أو تثبيت محلي للخطوط).
     */
    wp_enqueue_style(
        "maktaba-google-fonts",
        "https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@400;600;700&display=swap",
        array(),
        null
    );
    wp_enqueue_style(
        "maktaba-theme-parent-style",
        get_template_directory_uri() . "/style.css"
    );
    wp_enqueue_style(
        "maktaba-theme-style",
        get_stylesheet_uri(),
        array( "maktaba-theme-parent-style", "maktaba-google-fonts" )
    );
} );

/**
 * ربط زر "اطلب الكتاب" بعد السعر مباشرة في صفحة الكتاب —
 * انظر: interfaces/integration_points.md
 */
add_action( "woocommerce_single_product_summary", function () {
    echo do_shortcode( "[mkt_order_button]" );
}, 35 );

/**
 * ربط قسم "كتب مشابهة" أسفل تفاصيل الكتاب —
 * انظر: interfaces/integration_points.md
 */
add_action( "woocommerce_after_single_product_summary", function () {
    echo do_shortcode( "[mkt_similar_books]" );
}, 25 );

/**
 * ملاحظة Zero-Cost: فلاتر صفحة "كل الكتب" (تصنيف/سعر) تُفعَّل عبر
 * ودجت WooCommerce الجاهزة (Layered Nav + Price Filter) من
 * Appearance > Widgets، بلا كود إضافي — بدل إعادة بناء ما توفره
 * WooCommerce أصلاً (Section 8 من مخطط المشروع).
 */
