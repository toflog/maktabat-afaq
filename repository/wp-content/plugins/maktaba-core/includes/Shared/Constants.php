<?php
/**
 * Shared\Constants
 *
 * مصدر التعريف الوحيد للقوائم الثابتة المشتركة بين الطبقات.
 * انظر: contracts/domain_contracts + variables/shared_identifiers.md
 *
 * قاعدة صارمة: لا يُعاد تعريف أي من هذه القوائم في أي ملف آخر.
 */

namespace Maktaba\Core\Shared;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class Constants {

    /** حالات الطلب — Domain/Order/OrderStatusMap.php هو المستهلك الرئيسي */
    const ORDER_STATUS_NEW        = "NEW";
    const ORDER_STATUS_CONTACTED  = "CONTACTED";
    const ORDER_STATUS_CONFIRMED  = "CONFIRMED";
    const ORDER_STATUS_PREPARING  = "PREPARING";
    const ORDER_STATUS_SHIPPED    = "SHIPPED";
    const ORDER_STATUS_DELIVERED  = "DELIVERED";
    const ORDER_STATUS_CANCELLED  = "CANCELLED";
    const ORDER_STATUS_RETURNED   = "RETURNED";
    const ORDER_STATUS_FAILED     = "FAILED";

    /** أنواع حركة المخزون — Domain/Inventory/InventoryMovement.php هو المستهلك الرئيسي */
    const MOVEMENT_PURCHASE   = "PURCHASE";
    const MOVEMENT_SALE       = "SALE";
    const MOVEMENT_RETURN     = "RETURN";
    const MOVEMENT_DAMAGED    = "DAMAGED";
    const MOVEMENT_ADJUSTMENT = "ADJUSTMENT";

    /** مصدر العميل/الطلب */
    const SOURCE_WEBSITE   = "WEBSITE";
    const SOURCE_FACEBOOK  = "FACEBOOK";
    const SOURCE_INSTAGRAM = "INSTAGRAM";
    const SOURCE_WHATSAPP  = "WHATSAPP";
    const SOURCE_DIRECT    = "DIRECT";
    const SOURCE_OTHER     = "OTHER";

    /** نوع المورد */
    const SUPPLIER_DIRECT_PUBLISHER      = "direct_publisher";
    const SUPPLIER_AUTHORIZED_DISTRIBUTOR = "authorized_distributor";
    const SUPPLIER_SECONDARY_MARKET      = "secondary_market";

    /** حالة الكتاب (حقل احتياطي غير مفعّل بالواجهة في V1) */
    const BOOK_CONDITION_NEW  = "new";
    const BOOK_CONDITION_USED = "used";

    /**
     * @return string[] كل حالات الطلب المسموحة
     */
    public static function allOrderStatuses() {
        return array(
            self::ORDER_STATUS_NEW,
            self::ORDER_STATUS_CONTACTED,
            self::ORDER_STATUS_CONFIRMED,
            self::ORDER_STATUS_PREPARING,
            self::ORDER_STATUS_SHIPPED,
            self::ORDER_STATUS_DELIVERED,
            self::ORDER_STATUS_CANCELLED,
            self::ORDER_STATUS_RETURNED,
            self::ORDER_STATUS_FAILED,
        );
    }

    /**
     * @return string[] كل أنواع حركة المخزون المسموحة
     */
    public static function allMovementTypes() {
        return array(
            self::MOVEMENT_PURCHASE,
            self::MOVEMENT_SALE,
            self::MOVEMENT_RETURN,
            self::MOVEMENT_DAMAGED,
            self::MOVEMENT_ADJUSTMENT,
        );
    }

    /**
     * @return string[] الأنواع التي تزيد المخزون (إشارة موجبة)
     */
    public static function positiveMovementTypes() {
        return array( self::MOVEMENT_PURCHASE, self::MOVEMENT_RETURN );
    }

    /**
     * @return string[] الأنواع التي تنقص المخزون (إشارة سالبة)
     */
    public static function negativeMovementTypes() {
        return array( self::MOVEMENT_SALE, self::MOVEMENT_DAMAGED );
    }
}
