<?php
/**
 * Admin\Ajax\AjaxHandlers
 *
 * انظر: contracts/api_contracts/admin_ajax_endpoints.md
 *
 * قاعدة إلزامية: كل Endpoint هنا محمي بـ nonce + current_user_can()
 * ويُستدعى فقط من wp-admin (is_admin() = true) — لا Endpoint عام.
 */

namespace Maktaba\Core\Admin\Ajax;

use Maktaba\Core\Domain\Inventory\InventoryMovement;
use Maktaba\Core\Domain\Inventory\StockCalculator;
use Maktaba\Core\Domain\Book\BookRepository;
use Maktaba\Core\Integrations\WooCommerce\ProductAsBookBridge;
use Maktaba\Core\Integrations\WooCommerce\OrderMetaBridge;
use Maktaba\Core\Integrations\SocialContent\BookSocialContentBuilder;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class AjaxHandlers {

    const NONCE_ACTION = "mkt_admin_ajax";

    public static function register() {
        add_action( "wp_ajax_mkt_add_inventory_movement", array( __CLASS__, "handleAddInventoryMovement" ) );
        add_action( "wp_ajax_mkt_change_order_status", array( __CLASS__, "handleChangeOrderStatus" ) );
        add_action( "wp_ajax_mkt_generate_social_content", array( __CLASS__, "handleGenerateSocialContent" ) );
        add_action( "wp_ajax_mkt_set_order_source", array( __CLASS__, "handleSetOrderSource" ) );
        add_action( "wp_ajax_mkt_search_books", array( __CLASS__, "handleSearchBooks" ) );
    }

    private static function assertAuthorized() {
        if ( ! is_admin() || ! current_user_can( "manage_options" ) ) {
            wp_send_json_error( array( "message" => "غير مصرَّح." ), 403 );
        }

        check_ajax_referer( self::NONCE_ACTION, "nonce" );
    }

    /** Action: mkt_add_inventory_movement */
    public static function handleAddInventoryMovement() {
        self::assertAuthorized();

        $book_id  = isset( $_POST["book_id"] ) ? (int) $_POST["book_id"] : 0;
        $type     = isset( $_POST["type"] ) ? sanitize_text_field( wp_unslash( $_POST["type"] ) ) : "";
        $quantity = isset( $_POST["quantity"] ) ? (int) $_POST["quantity"] : 0;
        $note     = isset( $_POST["note"] ) ? sanitize_textarea_field( wp_unslash( $_POST["note"] ) ) : null;

        try {
            $movement = new InventoryMovement( $book_id, $type, $quantity, "manual-admin", $note );
            StockCalculator::recordMovement( $movement );
            ProductAsBookBridge::syncStock( $book_id );

            wp_send_json_success( array(
                "new_stock" => StockCalculator::calculateStock( $book_id ),
            ) );
        } catch ( \Exception $e ) {
            wp_send_json_error( array( "message" => $e->getMessage() ), 400 );
        }
    }

    /** Action: mkt_change_order_status */
    public static function handleChangeOrderStatus() {
        self::assertAuthorized();

        $order_id       = isset( $_POST["order_id"] ) ? (int) $_POST["order_id"] : 0;
        $new_status     = isset( $_POST["new_status"] ) ? sanitize_text_field( wp_unslash( $_POST["new_status"] ) ) : "";
        $note           = isset( $_POST["note"] ) ? sanitize_textarea_field( wp_unslash( $_POST["note"] ) ) : null;
        $admin_override = ! empty( $_POST["admin_override"] );

        try {
            OrderMetaBridge::changeLibraryStatus( $order_id, $new_status, $note, $admin_override );
            wp_send_json_success( array( "status" => $new_status ) );
        } catch ( \Exception $e ) {
            wp_send_json_error( array( "message" => $e->getMessage() ), 400 );
        }
    }

    /** Action: mkt_generate_social_content */
    public static function handleGenerateSocialContent() {
        self::assertAuthorized();

        $book_id = isset( $_POST["book_id"] ) ? (int) $_POST["book_id"] : 0;
        $text    = BookSocialContentBuilder::buildAsText( $book_id );

        if ( "" === $text ) {
            wp_send_json_error( array( "message" => "تعذّر توليد المحتوى — تحقق من بيانات الكتاب." ), 400 );
        }

        wp_send_json_success( array( "text" => $text ) );
    }

    /**
     * Action: mkt_set_order_source
     * يسدّ الفجوة رقم 4 من تقرير الحالة — تسجيل مصدر الطلب من واجهة الإدارة.
     */
    public static function handleSetOrderSource() {
        self::assertAuthorized();

        $order_id = isset( $_POST["order_id"] ) ? (int) $_POST["order_id"] : 0;
        $source   = isset( $_POST["source"] ) ? sanitize_text_field( wp_unslash( $_POST["source"] ) ) : "";

        try {
            OrderMetaBridge::setSource( $order_id, $source );
            wp_send_json_success( array( "source" => $source ) );
        } catch ( \Exception $e ) {
            wp_send_json_error( array( "message" => $e->getMessage() ), 400 );
        }
    }

    /**
     * Action: mkt_search_books
     * يسدّ التحسين المتبقي من تقرير الحالة — بحث حيّ عن الكتب بدل إدخال
     * "معرّف الكتاب" يدوياً في PurchaseAdminPage. يبحث في عنوان المنتج
     * (WooCommerce Product = Book) وفي حقل ISBN المخصص معاً.
     */
    public static function handleSearchBooks() {
        self::assertAuthorized();

        $term = isset( $_POST["term"] ) ? sanitize_text_field( wp_unslash( $_POST["term"] ) ) : "";

        if ( mb_strlen( $term ) < 2 ) {
            wp_send_json_success( array( "results" => array() ) );
        }

        $by_title = new \WP_Query( array(
            "post_type"      => "product",
            "post_status"    => "publish",
            "posts_per_page" => 20,
            "s"              => $term,
            "fields"         => "ids",
            "no_found_rows"  => true,
        ) );

        $by_isbn = new \WP_Query( array(
            "post_type"      => "product",
            "post_status"    => "publish",
            "posts_per_page" => 20,
            "fields"         => "ids",
            "no_found_rows"  => true,
            "meta_query"     => array(
                array(
                    "key"     => "_mkt_isbn",
                    "value"   => $term,
                    "compare" => "LIKE",
                ),
            ),
        ) );

        $book_ids = array_unique( array_merge( $by_title->posts, $by_isbn->posts ) );
        $book_ids = array_slice( $book_ids, 0, 20 );

        $results = array();
        foreach ( $book_ids as $book_id ) {
            $isbn  = BookRepository::getIsbn( $book_id );
            $label = get_the_title( $book_id );

            if ( $isbn ) {
                $label .= " — ISBN: " . $isbn;
            }

            $label .= " (#" . $book_id . ")";

            $results[] = array(
                "id"    => (int) $book_id,
                "label" => $label,
            );
        }

        wp_send_json_success( array( "results" => $results ) );
    }
}
