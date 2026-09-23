<?php
/**
 * Domain\Purchase\PurchaseRepository
 *
 * انظر: contracts/data_contracts/purchase.md
 *
 * قاعدة إلزامية (من inventory_rules.md): تسجيل Purchase مكتمل هو
 * المُطلق الوحيد لإنشاء Inventory Movement من نوع PURCHASE — لذلك
 * recordPurchase() هنا يستدعي StockCalculator مباشرة (كلاهما Domain،
 * الاعتماد بين وحدتين من نفس الطبقة مسموح — architecture/dependency_rules.md).
 */

namespace Maktaba\Core\Domain\Purchase;

use Maktaba\Core\Domain\Inventory\StockCalculator;
use Maktaba\Core\Shared\Helpers;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class PurchaseRepository {

    const POST_TYPE = "mkt_purchase";

    private static function itemsTable() {
        global $wpdb;
        return $wpdb->prefix . "mkt_purchase_items";
    }

    /**
     * تسجيل عملية شراء كاملة (رأس + بنود) + تحديث المخزون تلقائياً.
     *
     * @param int    $supplier_id
     * @param string $reference_number
     * @param array  $items  [ [book_id, quantity, unit_cost], ... ]
     * @param string|null $notes
     *
     * @return int معرّف Purchase الجديد
     * @throws \InvalidArgumentException
     */
    public static function recordPurchase( $supplier_id, $reference_number, array $items, $notes = null ) {
        global $wpdb;

        if ( empty( $items ) ) {
            throw new \InvalidArgumentException( "عملية الشراء يجب أن تحتوي بنداً واحداً على الأقل." );
        }

        $total_cost = 0.0;
        foreach ( $items as $item ) {
            if ( empty( $item["book_id"] ) || empty( $item["quantity"] ) || ! isset( $item["unit_cost"] ) ) {
                throw new \InvalidArgumentException( "كل بند يجب أن يحوي book_id وquantity وunit_cost." );
            }
            $total_cost += (float) $item["quantity"] * (float) $item["unit_cost"];
        }

        $purchase_id = wp_insert_post( array(
            "post_type"   => self::POST_TYPE,
            "post_title"  => sprintf( "Purchase #%s", $reference_number ),
            "post_status" => "publish",
        ), true );

        if ( is_wp_error( $purchase_id ) ) {
            throw new \RuntimeException( "تعذّر تسجيل الشراء: " . $purchase_id->get_error_message() );
        }

        update_post_meta( $purchase_id, "_mkt_supplier_id", (int) $supplier_id );
        update_post_meta( $purchase_id, "_mkt_reference_number", sanitize_text_field( $reference_number ) );
        update_post_meta( $purchase_id, "_mkt_total_cost", Helpers::roundMoney( $total_cost ) );
        update_post_meta( $purchase_id, "_mkt_notes", sanitize_textarea_field( (string) $notes ) );

        foreach ( $items as $item ) {
            $unit_cost  = (float) $item["unit_cost"];
            $quantity   = (int) $item["quantity"];
            $line_total = Helpers::roundMoney( $unit_cost * $quantity );

            $wpdb->insert(
                self::itemsTable(),
                array(
                    "purchase_id" => $purchase_id,
                    "book_id"     => (int) $item["book_id"],
                    "quantity"    => $quantity,
                    "unit_cost"   => $unit_cost,
                    "total_cost"  => $line_total,
                ),
                array( "%d", "%d", "%d", "%f", "%f" )
            );

            // قاعدة إلزامية: هذا المسار الوحيد لإنشاء حركة PURCHASE
            StockCalculator::applyPurchase( $item["book_id"], $quantity, "purchase:{$purchase_id}" );
        }

        return (int) $purchase_id;
    }

    /**
     * @param int $purchase_id
     * @return array[] بنود الشراء [book_id, quantity, unit_cost, total_cost]
     */
    public static function getItems( $purchase_id ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . self::itemsTable() . " WHERE purchase_id = %d ORDER BY id ASC",
                (int) $purchase_id
            ),
            ARRAY_A
        );
    }

    public static function getTotalCost( $purchase_id ) {
        return (float) get_post_meta( (int) $purchase_id, "_mkt_total_cost", true );
    }
}
