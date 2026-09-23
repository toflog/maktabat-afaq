<?php
/**
 * Domain\Inventory\StockCalculator
 *
 * المصدر الوحيد المسموح له بالكتابة إلى mkt_inventory_movements
 * وحساب المخزون الناتج عنها. انظر:
 * contracts/data_contracts/inventory_movement.md
 * contracts/domain_contracts/inventory_rules.md
 *
 * قاعدة الطبقة: يُستخدم هنا $wpdb (WordPress core DB layer) لأن الجدول
 * جدول مخصص يملكه هذا الـ Domain مباشرة — هذا لا يخالف عزل WooCommerce
 * (architecture/dependency_rules.md)، فالجدول ليس جدول WooCommerce.
 *
 * ممنوع: هذا الملف لا يكتب أبداً إلى _stock/_stock_status الخاصة
 * بـ WooCommerce مباشرة — تلك مسؤولية
 * Integrations/WooCommerce/ProductAsBookBridge::syncStock().
 */

namespace Maktaba\Core\Domain\Inventory;

use Maktaba\Core\Shared\Constants;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class StockCalculator {

    /**
     * @return string اسم الجدول الكامل مع بادئة المنشأة (Multisite-safe)
     */
    private static function table() {
        global $wpdb;
        return $wpdb->prefix . "mkt_inventory_movements";
    }

    /**
     * تسجيل حركة مخزون جديدة (الوسيلة الوحيدة المسموحة — لا تعديل مباشر).
     *
     * @param InventoryMovement $movement
     * @return int معرّف السطر الجديد (movement id)
     */
    public static function recordMovement( InventoryMovement $movement ) {
        global $wpdb;

        $inserted = $wpdb->insert(
            self::table(),
            array(
                "book_id"    => $movement->bookId(),
                "type"       => $movement->type(),
                "quantity"   => $movement->signedQuantity(),
                "reference"  => $movement->reference(),
                "note"       => $movement->note(),
                "created_at" => current_time( "mysql" ),
            ),
            array( "%d", "%s", "%d", "%s", "%s", "%s" )
        );

        if ( false === $inserted ) {
            throw new \RuntimeException( "Failed to record inventory movement for book #" . $movement->bookId() );
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * إعادة حساب المخزون الحالي لكتاب معيّن من مجموع حركاته بالكامل.
     * هذا هو "مصدر الحقيقة" الوحيد للكمية — انظر inventory_rules.md
     *
     * @param int $book_id
     * @return int الكمية المحسوبة (قد تكون صفراً، لا تكون سالبة منطقياً
     *             لكن الدالة لا "تصحّح" أي خطأ بيانات — تعكس الواقع فقط)
     */
    public static function calculateStock( $book_id ) {
        global $wpdb;

        $sum = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COALESCE(SUM(quantity), 0) FROM " . self::table() . " WHERE book_id = %d",
                (int) $book_id
            )
        );

        return (int) $sum;
    }

    /**
     * سجل الحركات الكامل لكتاب، لأغراض التقارير وشاشة "سجل الحركات".
     *
     * @param int   $book_id
     * @param array $args { type?: string, limit?: int, offset?: int }
     * @return array[] كل عنصر: [id, book_id, type, quantity, reference, note, created_at]
     */
    public static function getMovements( $book_id, array $args = array() ) {
        global $wpdb;

        $where  = "WHERE book_id = %d";
        $params = array( (int) $book_id );

        if ( ! empty( $args["type"] ) && in_array( $args["type"], Constants::allMovementTypes(), true ) ) {
            $where   .= " AND type = %s";
            $params[] = $args["type"];
        }

        $limit  = isset( $args["limit"] ) ? (int) $args["limit"] : 50;
        $offset = isset( $args["offset"] ) ? (int) $args["offset"] : 0;

        $sql = "SELECT * FROM " . self::table() . " {$where} ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
    }

    /**
     * اختصار: تسجيل شراء يزيد المخزون + إعادة الحساب.
     * تُستدعى من سير "تسجيل Purchase" (Section 18/37 من مخطط المشروع).
     *
     * @return int الكمية الجديدة بعد التسجيل
     */
    public static function applyPurchase( $book_id, $quantity, $purchase_reference ) {
        $movement = new InventoryMovement( $book_id, Constants::MOVEMENT_PURCHASE, $quantity, $purchase_reference );
        self::recordMovement( $movement );
        return self::calculateStock( $book_id );
    }

    /**
     * اختصار: تسجيل بيع مؤكد ينقص المخزون (عند الانتقال لحالة CONFIRMED).
     *
     * @return int الكمية الجديدة بعد التسجيل
     */
    public static function applySale( $book_id, $quantity, $order_reference ) {
        $movement = new InventoryMovement( $book_id, Constants::MOVEMENT_SALE, $quantity, $order_reference );
        self::recordMovement( $movement );
        return self::calculateStock( $book_id );
    }

    /**
     * اختصار: إرجاع كمية (مرتجع أو إلغاء قبل الشحن) — انظر
     * order_status_transitions.md قاعدة رقم 3.
     *
     * @return int الكمية الجديدة بعد التسجيل
     */
    public static function applyReturn( $book_id, $quantity, $order_reference, $note = null ) {
        $movement = new InventoryMovement( $book_id, Constants::MOVEMENT_RETURN, $quantity, $order_reference, $note );
        self::recordMovement( $movement );
        return self::calculateStock( $book_id );
    }
}
