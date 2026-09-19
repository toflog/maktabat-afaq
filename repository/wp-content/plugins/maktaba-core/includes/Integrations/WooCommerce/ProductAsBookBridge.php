<?php
/**
 * Integrations\WooCommerce\ProductAsBookBridge
 *
 * انظر: contracts/integration_contracts/woocommerce_integration.md
 * انظر: contracts/data_contracts/book.md
 *
 * قاعدة الطبقة: هذا هو المكان الوحيد المسموح فيه استدعاء WooCommerce
 * classes/functions مباشرة لكل ما يخص "الكتاب كمنتج". لا Domain ولا
 * Admin ولا Frontend يلمس wc_get_product()/WC_Product مباشرة.
 */

namespace Maktaba\Core\Integrations\WooCommerce;

use Maktaba\Core\Domain\Book\BookRules;
use Maktaba\Core\Domain\Inventory\StockCalculator;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class ProductAsBookBridge {

    public static function register() {
        add_action( "save_post_product", array( __CLASS__, "onProductSave" ), 10, 3 );
    }

    /**
     * عند حفظ منتج (كتاب): تشغيل قواعد التحقق (Domain/Book/BookRules)
     * دون كتابة أي منطق تحقق هنا — هذا الملف جسر فقط.
     */
    public static function onProductSave( $post_id, $post, $update ) {
        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }

        $product = function_exists( "wc_get_product" ) ? wc_get_product( $post_id ) : null;

        if ( ! $product ) {
            return;
        }

        $data = array(
            "title"         => $product->get_name(),
            "author_id"     => self::getPrimaryTermId( $post_id, "mkt_author" ),
            "category_id"   => self::getPrimaryTermId( $post_id, "product_cat" ),
            "selling_price" => $product->get_regular_price(),
        );

        $errors = BookRules::validate( $data );

        if ( ! empty( $errors ) ) {
            update_post_meta( $post_id, "_mkt_validation_errors", $errors );
        } else {
            delete_post_meta( $post_id, "_mkt_validation_errors" );
        }
    }

    /**
     * مزامنة المخزون المحسوب من StockCalculator (مصدر الحقيقة) إلى
     * حقول WooCommerce الأصلية (_stock, _stock_status) — الاتجاه دائماً
     * من Domain إلى WooCommerce، وليس العكس أبداً.
     *
     * @param int $book_id
     */
    public static function syncStock( $book_id ) {
        $product = function_exists( "wc_get_product" ) ? wc_get_product( $book_id ) : null;

        if ( ! $product ) {
            return;
        }

        $quantity = StockCalculator::calculateStock( $book_id );

        $product->set_manage_stock( true );
        $product->set_stock_quantity( $quantity );
        $product->set_stock_status( $quantity > 0 ? "instock" : "outofstock" );
        $product->save();
    }

    /**
     * @return int|null أول Term ID مرتبط بالمنتج ضمن Taxonomy معيّنة
     */
    private static function getPrimaryTermId( $post_id, $taxonomy ) {
        $terms = get_the_terms( $post_id, $taxonomy );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return null;
        }

        return (int) $terms[0]->term_id;
    }

    /**
     * إحصاءات مخزون مجمّعة — نقطة القراءة الوحيدة المسموحة لطبقة Admin
     * (Admin ممنوع من استدعاء wc_get_products مباشرة —
     * architecture/dependency_rules.md: "WooCommerce ... مسموحة حصراً
     * في Integrations/WooCommerce/").
     *
     * @param int $low_stock_threshold حد "منخفض المخزون" (افتراضي 3)
     * @return array { total, in_stock, out_of_stock, low_stock }
     */
    public static function getStockCounts( $low_stock_threshold = 3 ) {
        $counts = array( "total" => 0, "in_stock" => 0, "out_of_stock" => 0, "low_stock" => 0 );

        if ( ! function_exists( "wc_get_products" ) ) {
            return $counts;
        }

        $product_ids = wc_get_products( array( "status" => "publish", "limit" => -1, "return" => "ids" ) );
        $counts["total"] = count( $product_ids );

        foreach ( $product_ids as $product_id ) {
            $product = wc_get_product( $product_id );
            if ( ! $product ) {
                continue;
            }

            $qty = (int) $product->get_stock_quantity();

            if ( $qty <= 0 ) {
                $counts["out_of_stock"]++;
            } else {
                $counts["in_stock"]++;
                if ( $qty <= $low_stock_threshold ) {
                    $counts["low_stock"]++;
                }
            }
        }

        return $counts;
    }

    /**
     * بيانات عرض موحّدة للكتاب (اسم، وصف مختصر، سعر، صورة، رابط) —
     * نقطة القراءة الوحيدة المسموحة لأي وحدة تحتاج عرض الكتاب دون
     * لمس wc_get_product()/WC_Product مباشرة بنفسها (مثل
     * Integrations/SocialContent/BookSocialContentBuilder.php).
     *
     * @param int $book_id
     * @return array|null null إذا لم يوجد المنتج
     */
    public static function getDisplayData( $book_id ) {
        $product = function_exists( "wc_get_product" ) ? wc_get_product( $book_id ) : null;

        if ( ! $product ) {
            return null;
        }

        return array(
            "title"             => $product->get_name(),
            "short_description" => wp_strip_all_tags( $product->get_short_description() ),
            "price"             => $product->get_regular_price(),
            "cover_image_url"   => wp_get_attachment_url( $product->get_image_id() ),
            "permalink"         => get_permalink( $book_id ),
        );
    }
}
