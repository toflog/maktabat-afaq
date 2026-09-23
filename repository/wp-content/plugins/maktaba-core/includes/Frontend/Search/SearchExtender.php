<?php
/**
 * Frontend\Search\SearchExtender
 *
 * انظر: contracts/domain_contracts/search_rules.md
 *
 * يوسّع بحث WordPress/WooCommerce الأساسي ليشمل ISBN، المؤلف، الناشر —
 * دون بناء محرك بحث منفصل (Zero-Cost/Zero-Complexity).
 */

namespace Maktaba\Core\Frontend\Search;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class SearchExtender {

    public static function register() {
        add_action( "pre_get_posts", array( __CLASS__, "extendQuery" ) );
    }

    public static function extendQuery( $query ) {
        if ( is_admin() || ! $query->is_search() || ! $query->is_main_query() ) {
            return;
        }

        $search_term = $query->get( "s" );

        if ( empty( $search_term ) ) {
            return;
        }

        $query->set( "post_type", array( "product" ) );

        // إن وُجدت مطابقة ISBN دقيقة، أعطها أولوية عبر meta_query إضافي
        // مع بقاء نص البحث العادي فعّالاً (title/content) كما هو افتراضياً.
        $query->set( "meta_query", array(
            "relation" => "OR",
            array( "key" => "_mkt_isbn", "value" => $search_term, "compare" => "LIKE" ),
        ) );

        add_filter( "posts_search", array( __CLASS__, "widenSearchToTaxonomies" ), 10, 2 );
    }

    /**
     * توسيع WHERE بحيث يطابق أيضاً أسماء المؤلف/الناشر/التصنيف،
     * وليس فقط العنوان/المحتوى الافتراضي.
     *
     * ملاحظة: يعيد تغليف شرط WordPress الافتراضي بدل تعديله بمطابقة
     * نصية على نهاية الاستعلام (أكثر متانة عبر نسخ WordPress/WooCommerce
     * المختلفة) — يُنصح باختبارها عند أي ترقية كبرى لـ WordPress.
     */
    public static function widenSearchToTaxonomies( $search, $query ) {
        global $wpdb;

        if ( is_admin() || ! $query->is_search() || ! $query->is_main_query() || empty( $search ) ) {
            return $search;
        }

        $term = $query->get( "s" );
        if ( empty( $term ) ) {
            return $search;
        }

        $like       = "%" . $wpdb->esc_like( $term ) . "%";
        $taxonomies = array( "mkt_author", "mkt_publisher", "product_cat" );

        $placeholders = implode( ", ", array_fill( 0, count( $taxonomies ), "%s" ) );

        $taxonomy_match = $wpdb->prepare(
            "{$wpdb->posts}.ID IN (
                SELECT tr.object_id FROM {$wpdb->term_relationships} tr
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                WHERE tt.taxonomy IN ({$placeholders})
                AND t.name LIKE %s
            )",
            array_merge( $taxonomies, array( $like ) )
        );

        // إزالة بادئة " AND " من شرط ووردبريس الافتراضي ثم إعادة تغليف
        // الاثنين معاً بـ OR — أكثر أماناً من التخمين على شكل الأقواس الختامية.
        $default_condition = preg_replace( "/^\s*AND\s*/i", "", trim( $search ) );

        remove_filter( "posts_search", array( __CLASS__, "widenSearchToTaxonomies" ), 10 );

        return " AND ( ({$default_condition}) OR ({$taxonomy_match}) ) ";
    }
}
