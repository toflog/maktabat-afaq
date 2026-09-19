<?php
/**
 * Domain\Inventory\InventoryMovement
 *
 * Value Object يمثّل سطراً واحداً في سجل حركات المخزون.
 * انظر: contracts/data_contracts/inventory_movement.md
 *
 * قاعدة الطبقة: هذا الملف لا يعرف شيئاً عن WordPress/WooCommerce —
 * بيانات خام فقط (Dependency Inversion — architecture/layer_rules.md).
 */

namespace Maktaba\Core\Domain\Inventory;

use Maktaba\Core\Shared\Constants;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class InventoryMovement {

    /** @var int */
    private $book_id;

    /** @var string */
    private $type;

    /** @var int عدد صحيح — الإشارة (+/-) تُحسب حسب النوع لا تُدخل يدوياً */
    private $quantity;

    /** @var string|null */
    private $reference;

    /** @var string|null */
    private $note;

    /**
     * @param int    $book_id
     * @param string $type      أحد Constants::allMovementTypes()
     * @param int    $quantity  لكل الأنواع عدا ADJUSTMENT: رقم موجب (الإشارة
     *                          تُحدَّد داخلياً حسب النوع). لنوع ADJUSTMENT:
     *                          رقم موقّع (موجب أو سالب) يمثّل التغيير مباشرة.
     * @param string|null $reference  مرجع Purchase ID أو Order ID
     * @param string|null $note       إلزامي لنوع ADJUSTMENT
     *
     * @throws \InvalidArgumentException عند خرق قواعد العقد
     */
    public function __construct( $book_id, $type, $quantity, $reference = null, $note = null ) {
        if ( ! in_array( $type, Constants::allMovementTypes(), true ) ) {
            throw new \InvalidArgumentException( "Invalid inventory movement type: {$type}" );
        }

        $quantity = (int) $quantity;

        if ( 0 === $quantity ) {
            throw new \InvalidArgumentException( "Inventory movement quantity cannot be zero." );
        }

        if ( Constants::MOVEMENT_ADJUSTMENT === $type ) {
            // قاعدة إلزامية: contracts/domain_contracts/inventory_rules.md
            if ( empty( $note ) ) {
                throw new \InvalidArgumentException( "ADJUSTMENT movements require a mandatory note explaining the reason." );
            }
        } elseif ( $quantity < 0 ) {
            // كل الأنواع عدا ADJUSTMENT تستقبل مقداراً موجباً فقط —
            // الإشارة تُحسب داخلياً في signedQuantity().
            throw new \InvalidArgumentException( "Only ADJUSTMENT movements may carry a signed (negative) quantity." );
        }

        $this->book_id   = (int) $book_id;
        $this->type      = $type;
        $this->quantity  = $quantity;
        $this->reference = $reference;
        $this->note      = $note;
    }

    public function bookId() {
        return $this->book_id;
    }

    public function type() {
        return $this->type;
    }

    /**
     * الكمية كما أُدخلت (موجبة لكل الأنواع عدا ADJUSTMENT التي قد تكون موقّعة).
     */
    public function rawQuantity() {
        return $this->quantity;
    }

    /**
     * الكمية بعد تطبيق الإشارة الصحيحة حسب النوع — هذا ما يُخزَّن فعلياً
     * في mkt_inventory_movements.quantity.
     */
    public function signedQuantity() {
        if ( Constants::MOVEMENT_ADJUSTMENT === $this->type ) {
            // موقّعة أصلاً منذ الإدخال (تحقّق في المُنشئ).
            return $this->quantity;
        }

        if ( in_array( $this->type, Constants::negativeMovementTypes(), true ) ) {
            return -1 * $this->quantity;
        }

        // PURCHASE / RETURN → موجبة كما هي.
        return $this->quantity;
    }

    public function reference() {
        return $this->reference;
    }

    public function note() {
        return $this->note;
    }
}
