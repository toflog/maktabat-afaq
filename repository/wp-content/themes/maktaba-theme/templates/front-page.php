<?php
/**
 * front-page.php — الصفحة الرئيسية (Section 7 من مخطط المشروع)
 *
 * قاعدة معمارية: عرض فقط — يستدعي WooCommerce shortcodes الرسمية
 * (نقطة تكامل مُعتمدة من WooCommerce نفسه لبناء الثيمات) و Shortcodes
 * الخاصة بـ maktaba-core فقط. لا استعلام مباشر لأي حقل داخلي.
 */

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

get_header();
?>

<div class="mkt-hero">
    <h1><?php bloginfo( "name" ); ?></h1>
    <p><?php esc_html_e( "اكتشف كتابك القادم", "maktaba-theme" ); ?></p>

    <?php if ( function_exists( "get_product_search_form" ) ) : ?>
        <?php get_product_search_form(); ?>
    <?php endif; ?>

    <p>
        <a class="button" href="<?php echo esc_url( get_permalink( wc_get_page_id( "shop" ) ) ); ?>">
            <?php esc_html_e( "تصفح الكتب", "maktaba-theme" ); ?>
        </a>
    </p>
</div>

<h2 class="mkt-section-title">🔥 <?php esc_html_e( "الأكثر طلباً", "maktaba-theme" ); ?></h2>
<?php echo do_shortcode( '[best_selling_products limit="4" columns="4"]' ); ?>

<h2 class="mkt-section-title">🆕 <?php esc_html_e( "وصل حديثاً", "maktaba-theme" ); ?></h2>
<?php echo do_shortcode( '[recent_products limit="4" columns="4"]' ); ?>

<h2 class="mkt-section-title">📚 <?php esc_html_e( "تصفح حسب التصنيف", "maktaba-theme" ); ?></h2>
<ul class="mkt-category-list">
    <?php
    $categories = get_terms( array( "taxonomy" => "product_cat", "hide_empty" => true, "parent" => 0 ) );
    if ( ! is_wp_error( $categories ) ) :
        foreach ( $categories as $category ) :
            ?>
            <li>
                <a href="<?php echo esc_url( get_term_link( $category ) ); ?>">
                    <?php echo esc_html( $category->name ); ?>
                </a>
            </li>
            <?php
        endforeach;
    endif;
    ?>
</ul>

<h2 class="mkt-section-title"><?php esc_html_e( "لماذا مكتبة آفاق؟", "maktaba-theme" ); ?></h2>
<div class="mkt-why-us">
    <span>✔ <?php esc_html_e( "كتب أصلية", "maktaba-theme" ); ?></span>
    <span>✔ <?php esc_html_e( "تشكيلة متنوعة", "maktaba-theme" ); ?></span>
    <span>✔ <?php esc_html_e( "طلب سهل عبر واتساب", "maktaba-theme" ); ?></span>
    <span>✔ <?php esc_html_e( "رد سريع", "maktaba-theme" ); ?></span>
    <span>✔ <?php esc_html_e( "توصيل داخل سوريا", "maktaba-theme" ); ?></span>
</div>

<div class="mkt-follow-us">
    <h3><?php esc_html_e( "تابعنا", "maktaba-theme" ); ?></h3>
    <?php
    /**
     * روابط السوشيال ميديا تُدار كإعداد قابل للتعديل — لا نصوص ثابتة هنا
     * (يمكن ربطها لاحقاً بـ Admin/Settings/GeneralSettings عند إضافة
     * حقول روابط فيسبوك/انستغرام إلى شاشة الإعدادات).
     */
    ?>
</div>

<?php
get_footer();
