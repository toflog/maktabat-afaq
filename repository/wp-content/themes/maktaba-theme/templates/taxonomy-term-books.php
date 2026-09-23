<?php
/**
 * templates/taxonomy-term-books.php
 *
 * قالب مشترك لصفحتي المؤلف والناشر (Section 13/14 من مخطط المشروع) —
 * يُستدعى من taxonomy-mkt_author.php و taxonomy-mkt_publisher.php في
 * جذر الـ Theme لتفادي تكرار الكود.
 *
 * قاعدة معمارية: يستخدم WooCommerce hooks القياسية
 * (woocommerce_before_shop_loop / woocommerce_product_loop_start ...)
 * وهي نقاط تكامل رسمية موثّقة من WooCommerce نفسه للثيمات — وليست
 * استدعاءً مباشراً لـ WC_Product/WC_Order الممنوع خارج Integrations/WooCommerce.
 */

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

get_header();

$term = get_queried_object();
?>

<header class="mkt-term-header">
    <h1><?php echo esc_html( $term->name ); ?></h1>
    <?php if ( ! empty( $term->description ) ) : ?>
        <p><?php echo wp_kses_post( $term->description ); ?></p>
    <?php endif; ?>
</header>

<?php
if ( have_posts() ) :
    woocommerce_product_loop_start();

    while ( have_posts() ) :
        the_post();
        wc_get_template_part( "content", "product" );
    endwhile;

    woocommerce_product_loop_end();
else :
    echo "<p>" . esc_html__( "لا توجد كتب ضمن هذا التصنيف حالياً.", "maktaba-theme" ) . "</p>";
endif;

get_footer();
