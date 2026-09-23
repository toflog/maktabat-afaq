<?php
/**
 * Frontend\SimilarBooks\SimilarBooksFinder
 *
 * انظر: Section 9 من مخطط المشروع (قسم "كتب مشابهة" في صفحة الكتاب)
 * انظر: interfaces/integration_points.md (Shortcode: [mkt_similar_books])
 *
 * قاعدة الطبقة: يستخدم WP_Query (WordPress Core) مباشرة على product_cat —
 * لا يستدعي WC_Product class، لذلك لا يخالف حصر WooCommerce في
 * Integrations/WooCommerce/ (تصنيف المنتج تصنيف WordPress قياسي).
 */

namespace Maktaba\Core\Frontend\SimilarBooks;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class SimilarBooksFinder {

    public static function register() {
        add_shortcode( "mkt_similar_books", array( __CLASS__, "render" ) );
    }

    /**
     * @param array $atts { book_id?: int, limit?: int }
     * @return string HTML
     */
    public static function render( $atts = array() ) {
        $atts = shortcode_atts( array(
            "book_id" => get_the_ID(),
            "limit"   => 4,
        ), $atts );

        $book_id = (int) $atts["book_id"];

        if ( ! $book_id ) {
            return "";
        }

        $category_ids = wp_get_post_terms( $book_id, "product_cat", array( "fields" => "ids" ) );

        if ( is_wp_error( $category_ids ) || empty( $category_ids ) ) {
            return "";
        }

        $query = new \WP_Query( array(
            "post_type"      => "product",
            "post_status"    => "publish",
            "posts_per_page" => (int) $atts["limit"],
            "post__not_in"   => array( $book_id ),
            "tax_query"      => array(
                array(
                    "taxonomy" => "product_cat",
                    "field"    => "term_id",
                    "terms"    => $category_ids,
                ),
            ),
        ) );

        if ( ! $query->have_posts() ) {
            return "";
        }

        ob_start();
        echo '<div class="mkt-similar-books">';
        echo '<h3>' . esc_html__( "كتب مشابهة", "maktaba-core" ) . '</h3>';
        echo '<div class="mkt-similar-books-grid">';

        while ( $query->have_posts() ) {
            $query->the_post();
            ?>
            <a class="mkt-similar-book-card" href="<?php the_permalink(); ?>">
                <?php echo get_the_post_thumbnail( get_the_ID(), "medium" ); ?>
                <span class="mkt-similar-book-title"><?php the_title(); ?></span>
            </a>
            <?php
        }

        echo '</div></div>';
        wp_reset_postdata();

        return ob_get_clean();
    }
}
