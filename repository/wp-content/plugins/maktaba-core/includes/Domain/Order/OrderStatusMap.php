<?php
/**
 * Domain\Order\OrderStatusMap
 *
 * انظر: contracts/domain_contracts/order_status_transitions.md
 *
 * قاعدة الطبقة: يستقبل بيانات خام فقط (حالات نصية، مصفوفة عناصر
 * book_id => quantity) — لا يعرف شيئاً عن WC_Order. الجسر الوحيد
 * المسموح له باستدعاء هذا الملف هو
 * Integrations/WooCommerce/OrderMetaBridge.php
 */

namespace Maktaba\Core\Domain\Order;

use Maktaba\Core\Domain\Inventory\StockCalculator;
use Maktaba\Core\Shared\Constants;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class OrderStatusMap {

    /** الترتيب التسلسلي الطبيعي للحالات (Section 11 من مخطط المشروع) */
    const SEQUENCE = array(
        Constants::ORDER_STATUS_NEW,
        Constants::ORDER_STATUS_CONTACTED,
        Constants::ORDER_STATUS_CONFIRMED,
        Constants::ORDER_STATUS_PREPARING,
        Constants::ORDER_STATUS_SHIPPED,
        Constants::ORDER_STATUS_DELIVERED,
    );

    /** الحالات التي يُسمح منها بالإلغاء (كل ما قبل التسليم) */
    const CANCELLABLE_FROM = array(
        Constants::ORDER_STATUS_NEW,
        Constants::ORDER_STATUS_CONTACTED,
        Constants::ORDER_STATUS_CONFIRMED,
        Constants::ORDER_STATUS_PREPARING,
        Constants::ORDER_STATUS_SHIPPED,
    );

    /** الحالات التي يُسمح منها بتسجيل فشل التواصل/التأكيد */
    const FAILABLE_FROM = array(
        Constants::ORDER_STATUS_CONTACTED,
        Constants::ORDER_STATUS_CONFIRMED,
    );

    /** الحالات التي بعدها يكون المخزون قد خُصم فعلياً (بعد CONFIRMED) */
    const STOCK_RESERVED_FROM = array(
        Constants::ORDER_STATUS_CONFIRMED,
        Constants::ORDER_STATUS_PREPARING,
        Constants::ORDER_STATUS_SHIPPED,
        Constants::ORDER_STATUS_DELIVERED,
    );

    /**
     * هل الانتقال $from → $to هو انتقال "طبيعي" (تسلسلي أو فرع معتمد)
     * لا يحتاج تدخلاً إدارياً استثنائياً؟
     */
    public static function isNormalTransition( $from, $to ) {
        $seq_from = array_search( $from, self::SEQUENCE, true );
        $seq_to   = array_search( $to, self::SEQUENCE, true );

        // تسلسلي مباشر: الخطوة التالية بالضبط
        if ( false !== $seq_from && false !== $seq_to && $seq_to === $seq_from + 1 ) {
            return true;
        }

        if ( Constants::ORDER_STATUS_CANCELLED === $to && in_array( $from, self::CANCELLABLE_FROM, true ) ) {
            return true;
        }

        if ( Constants::ORDER_STATUS_RETURNED === $to && Constants::ORDER_STATUS_DELIVERED === $from ) {
            return true;
        }

        if ( Constants::ORDER_STATUS_FAILED === $to && in_array( $from, self::FAILABLE_FROM, true ) ) {
            return true;
        }

        return false;
    }

    /**
     * يتحقق من صلاحية الانتقال ويرمي استثناء عند الخرق.
     *
     * @param string      $from
     * @param string      $to
     * @param bool        $admin_override  إجراء إداري صريح للقفز فوق حالة
     * @param string|null $note            إلزامي عند القفز (admin_override = true)
     *
     * @throws \InvalidArgumentException
     */
    public static function assertTransitionAllowed( $from, $to, $admin_override = false, $note = null ) {
        if ( ! in_array( $from, Constants::allOrderStatuses(), true )
            || ! in_array( $to, Constants::allOrderStatuses(), true )
        ) {
            throw new \InvalidArgumentException( "Invalid order status in transition: {$from} -> {$to}" );
        }

        if ( self::isNormalTransition( $from, $to ) ) {
            return; // مسموح دائماً بلا شرط إضافي
        }

        // أي انتقال غير طبيعي (قفز) يتطلب إجراءً إدارياً صريحاً + سبب مسجَّل
        // (قاعدة رقم 1 في order_status_transitions.md)
        if ( ! $admin_override ) {
            throw new \InvalidArgumentException(
                "Transition {$from} -> {$to} skips required steps. Use an explicit admin override with a note."
            );
        }

        if ( empty( $note ) ) {
            throw new \InvalidArgumentException(
                "Skipping a status step requires a mandatory note explaining the reason."
            );
        }
    }

    /**
     * يطبّق الأثر التلقائي على المخزون الناتج عن انتقال الحالة.
     * لا يكتب الحالة نفسها — فقط يستدعي StockCalculator عند الحاجة.
     *
     * @param string $to
     * @param string $from
     * @param array  $items            [book_id => quantity, ...]
     * @param string $order_reference  مثال: "order:123"
     */
    public static function applyStockSideEffects( $to, $from, array $items, $order_reference ) {
        if ( Constants::ORDER_STATUS_CONFIRMED === $to ) {
            // قاعدة رقم 2: CONFIRMED = خصم المخزون
            foreach ( $items as $book_id => $quantity ) {
                StockCalculator::applySale( $book_id, $quantity, $order_reference );
            }
            return;
        }

        if ( Constants::ORDER_STATUS_CANCELLED === $to && in_array( $from, self::STOCK_RESERVED_FROM, true ) ) {
            // قاعدة رقم 3: إلغاء بعد أن كان المخزون محجوزاً/مخصوماً → إعادة الكمية
            foreach ( $items as $book_id => $quantity ) {
                StockCalculator::applyReturn( $book_id, $quantity, $order_reference, "إلغاء طلب بعد التأكيد" );
            }
            return;
        }

        if ( Constants::ORDER_STATUS_RETURNED === $to ) {
            // مرتجع بعد التسليم
            foreach ( $items as $book_id => $quantity ) {
                StockCalculator::applyReturn( $book_id, $quantity, $order_reference, "مرتجع بعد التسليم" );
            }
        }
    }
}
