<?php
/**
 * Integrations\WooCommerce\OrderMetaBridge
 *
 * انظر: contracts/data_contracts/order.md
 * انظر: contracts/integration_contracts/woocommerce_integration.md
 *
 * الجسر الوحيد المسموح له بقراءة/كتابة WC_Order مباشرة، وتوجيه أي
 * تغيير حالة عبر Domain/Order/OrderStatusMap.php بدل التعامل معه هنا.
 */

namespace Maktaba\Core\Integrations\WooCommerce;

use Maktaba\Core\Domain\Order\OrderStatusMap;
use Maktaba\Core\Shared\Constants;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class OrderMetaBridge {

    const META_LIBRARY_STATUS = "_mkt_library_status";
    const META_SOURCE         = "_mkt_source";
    const META_INTERNAL_NOTES = "_mkt_internal_notes";
    const META_CONTACTED_AT   = "_mkt_contacted_at";

    public static function register() {
        // لا نعتمد على woocommerce_order_status_changed وحده لأن حالاتنا
        // (NEW/CONTACTED/...) أدق من حالات WooCommerce القياسية —
        // التغيير يُطلق غالباً من واجهة الإدارة عبر changeLibraryStatus().
    }

    /**
     * نقطة الدخول الوحيدة المعتمدة لتغيير حالة المكتبة لطلب.
     *
     * @param int         $order_id
     * @param string      $new_status
     * @param string|null $note
     * @param bool        $admin_override  إجراء إداري صريح لتجاوز التسلسل
     *
     * @throws \InvalidArgumentException عبر OrderStatusMap عند خرق العقد
     */
    public static function changeLibraryStatus( $order_id, $new_status, $note = null, $admin_override = false ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            throw new \InvalidArgumentException( "Order #{$order_id} not found." );
        }

        $current_status = $order->get_meta( self::META_LIBRARY_STATUS );
        $current_status = $current_status ? $current_status : Constants::ORDER_STATUS_NEW;

        OrderStatusMap::assertTransitionAllowed( $current_status, $new_status, $admin_override, $note );

        $items = self::extractBookQuantities( $order );

        OrderStatusMap::applyStockSideEffects(
            $new_status,
            $current_status,
            $items,
            "order:{$order_id}"
        );

        $order->update_meta_data( self::META_LIBRARY_STATUS, $new_status );

        if ( Constants::ORDER_STATUS_CONTACTED === $new_status && ! $order->get_meta( self::META_CONTACTED_AT ) ) {
            // SLA — قاعدة رقم 4 في order_status_transitions.md
            $order->update_meta_data( self::META_CONTACTED_AT, current_time( "mysql" ) );
        }

        if ( ! empty( $note ) ) {
            $existing_notes = $order->get_meta( self::META_INTERNAL_NOTES );
            $order->update_meta_data(
                self::META_INTERNAL_NOTES,
                trim( $existing_notes . "\n[" . current_time( "mysql" ) . "] " . $note )
            );
        }

        $order->save();

        // إعادة مزامنة عرض المخزون في WooCommerce بعد أي أثر على الكمية
        foreach ( array_keys( $items ) as $book_id ) {
            ProductAsBookBridge::syncStock( $book_id );
        }
    }

    /**
     * تسجيل مصدر الطلب عند إنشائه (Section 20 من مخطط المشروع).
     *
     * يُغذّي تلقائياً جدول mkt_customers التجميعي (Domain/Customer)
     * من بيانات فوترة الطلب — يسدّ الفجوة رقم 3 من تقرير الحالة.
     */
    public static function setSource( $order_id, $source ) {
        if ( ! in_array( $source, array(
            Constants::SOURCE_WEBSITE, Constants::SOURCE_FACEBOOK, Constants::SOURCE_INSTAGRAM,
            Constants::SOURCE_WHATSAPP, Constants::SOURCE_DIRECT, Constants::SOURCE_OTHER,
        ), true ) ) {
            throw new \InvalidArgumentException( "Invalid order source: {$source}" );
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        $order->update_meta_data( self::META_SOURCE, $source );
        $order->save();

        $phone = $order->get_billing_phone();

        if ( ! empty( $phone ) ) {
            \Maktaba\Core\Domain\Customer\CustomerSourceRepository::upsert( $phone, array(
                "name"    => trim( $order->get_billing_first_name() . " " . $order->get_billing_last_name() ),
                "city"    => $order->get_billing_city(),
                "area"    => $order->get_billing_address_2(),
                "address" => $order->get_billing_address_1(),
                "source"  => $source,
            ) );
        }
    }

    /**
     * نقطة القراءة الوحيدة لحالة المكتبة الحالية لطلب — تُستخدم من
     * Admin/MetaBoxes/OrderStatusMetaBox.php بدل استدعاء wc_get_order()
     * مباشرة (architecture/dependency_rules.md).
     *
     * @return string
     */
    public static function getLibraryStatus( $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return Constants::ORDER_STATUS_NEW;
        }

        $status = $order->get_meta( self::META_LIBRARY_STATUS );
        return $status ? $status : Constants::ORDER_STATUS_NEW;
    }

    /**
     * نقطة القراءة الوحيدة لمصدر الطلب — نفس قاعدة getLibraryStatus أعلاه.
     *
     * @return string
     */
    public static function getSource( $order_id ) {
        $order = wc_get_order( $order_id );
        return $order ? (string) $order->get_meta( self::META_SOURCE ) : "";
    }

    /**
     * @param \WC_Order $order
     * @return array [book_id => quantity, ...]
     */
    private static function extractBookQuantities( $order ) {
        $items = array();

        foreach ( $order->get_items() as $item ) {
            $book_id  = $item->get_product_id();
            $quantity = $item->get_quantity();

            if ( isset( $items[ $book_id ] ) ) {
                $items[ $book_id ] += $quantity;
            } else {
                $items[ $book_id ] = $quantity;
            }
        }

        return $items;
    }

    /**
     * نسخة عامة من extractBookQuantities — تُستخدم من Admin/Reports
     * لحساب الأرباح لكل طلب دون تكرار منطق القراءة من WC_Order.
     */
    public static function getOrderItems( $order_id ) {
        $order = wc_get_order( $order_id );
        return $order ? self::extractBookQuantities( $order ) : array();
    }

    /**
     * تفاصيل كل بند في الطلب (الكمية + سعر الوحدة الفعلي وقت البيع) —
     * نقطة القراءة الوحيدة المسموحة لـ Admin/Reports/ProfitReport.php
     * كي لا يستدعي wc_get_order()/WC_Order مباشرة (dependency_rules.md).
     *
     * @param int $order_id
     * @return array [ book_id => ["quantity" => int, "unit_price" => float], ... ]
     */
    public static function getOrderLineDetails( $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return array();
        }

        $lines = array();

        foreach ( $order->get_items() as $item ) {
            $book_id  = $item->get_product_id();
            $quantity = (int) $item->get_quantity();
            $unit_price = $quantity > 0 ? ( (float) $item->get_total() / $quantity ) : 0.0;

            if ( isset( $lines[ $book_id ] ) ) {
                $lines[ $book_id ]["quantity"] += $quantity;
            } else {
                $lines[ $book_id ] = array( "quantity" => $quantity, "unit_price" => $unit_price );
            }
        }

        return $lines;
    }

    /**
     * عدد الطلبات حسب حالة المكتبة المخصصة (وليس حالة WooCommerce القياسية).
     * نقطة القراءة الوحيدة المسموحة لطبقة Admin (بدل استعلام WC_Order مباشرة).
     *
     * @param string $library_status أحد Constants::allOrderStatuses()
     * @return int
     */
    public static function countByLibraryStatus( $library_status ) {
        $orders = wc_get_orders( array(
            "limit"      => -1,
            "return"     => "ids",
            "meta_key"   => self::META_LIBRARY_STATUS,
            "meta_value" => $library_status,
        ) );

        return count( $orders );
    }

    /**
     * عدد الطلبات المُنشأة اليوم (حسب توقيت الموقع).
     *
     * @return int
     */
    public static function countCreatedToday() {
        $orders = wc_get_orders( array(
            "limit"        => -1,
            "return"       => "ids",
            "date_created" => ">=" . strtotime( "today", current_time( "timestamp" ) ),
        ) );

        return count( $orders );
    }
}
