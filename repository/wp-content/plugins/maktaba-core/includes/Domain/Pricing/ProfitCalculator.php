<?php
/**
 * Domain\Pricing\ProfitCalculator
 *
 * انظر: contracts/domain_contracts/pricing_and_profit.md
 *
 * قاعدة الطبقة الحرجة: هذا الملف لا يجلب selling_price بنفسه من
 * WooCommerce (ذلك يتطلب WC_Product) — يستقبله كمُعامل صريح من المستدعي
 * (Admin/Reports عبر Integrations/WooCommerce). هذا يحافظ على استقلالية
 * Domain عن WooCommerce (Dependency Inversion).
 */

namespace Maktaba\Core\Domain\Pricing;

use Maktaba\Core\Domain\Book\BookRepository;
use Maktaba\Core\Shared\Helpers;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class ProfitCalculator {

    /**
     * التكلفة الفعلية = سعر الشراء + تكلفة الوصول + تكاليف إضافية.
     *
     * @param int   $book_id
     * @param float $additional_costs تكاليف إضافية اختيارية (افتراضي صفر)
     * @return float
     */
    public static function calculateActualCost( $book_id, $additional_costs = 0.0 ) {
        Helpers::assertAdminContext( "ProfitCalculator::calculateActualCost" );

        $purchase_price = BookRepository::getPurchasePrice( $book_id );
        $shipping_cost  = BookRepository::getShippingCost( $book_id );

        return Helpers::roundMoney( $purchase_price + $shipping_cost + (float) $additional_costs );
    }

    /**
     * الربح = سعر البيع − التكلفة الفعلية.
     *
     * @param int   $book_id
     * @param float $selling_price   يُمرَّر من المستدعي (مصدره WooCommerce _regular_price)
     * @param float $additional_costs
     * @return float
     */
    public static function calculateProfit( $book_id, $selling_price, $additional_costs = 0.0 ) {
        Helpers::assertAdminContext( "ProfitCalculator::calculateProfit" );

        $actual_cost = self::calculateActualCost( $book_id, $additional_costs );

        return Helpers::roundMoney( (float) $selling_price - $actual_cost );
    }
}
