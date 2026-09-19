<?php
/**
 * Admin\Reports\ProfitReport
 *
 * انظر: Section 29 من مخطط المشروع (تقارير الأرباح)
 * انظر: contracts/domain_contracts/pricing_and_profit.md
 *
 * قاعدة حرجة: هذا الملف لا يستدعي wc_get_order()/WC_Order أبداً — يقرأ
 * تفاصيل الطلب حصراً عبر Integrations/WooCommerce/OrderMetaBridge،
 * ثم يمرّر السعر الفعلي كمُعامل صريح إلى ProfitCalculator (Domain) —
 * تطبيق فعلي لعزل الاعتماد الموثّق في العقد.
 */

namespace Maktaba\Core\Admin\Reports;

use Maktaba\Core\Domain\Pricing\ProfitCalculator;
use Maktaba\Core\Integrations\WooCommerce\OrderMetaBridge;
use Maktaba\Core\Shared\Helpers;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class ProfitReport {

    /**
     * حساب الربح الإجمالي لطلب واحد (لكل بنوده).
     *
     * @param int $order_id
     * @return array { total_revenue, total_cost, total_profit, lines: array }
     */
    public static function calculateForOrder( $order_id ) {
        $lines_raw = OrderMetaBridge::getOrderLineDetails( $order_id ); // [book_id => [quantity, unit_price]]

        $total_revenue = 0.0;
        $total_cost    = 0.0;
        $lines         = array();

        foreach ( $lines_raw as $book_id => $line ) {
            $quantity   = (int) $line["quantity"];
            $unit_price = (float) $line["unit_price"];

            $revenue = Helpers::roundMoney( $unit_price * $quantity );
            $cost    = Helpers::roundMoney( ProfitCalculator::calculateActualCost( $book_id ) * $quantity );
            $profit  = Helpers::roundMoney( $revenue - $cost );

            $total_revenue += $revenue;
            $total_cost    += $cost;

            $lines[] = array(
                "book_id"    => $book_id,
                "quantity"   => $quantity,
                "unit_price" => $unit_price,
                "revenue"    => $revenue,
                "cost"       => $cost,
                "profit"     => $profit,
            );
        }

        return array(
            "total_revenue" => Helpers::roundMoney( $total_revenue ),
            "total_cost"    => Helpers::roundMoney( $total_cost ),
            "total_profit"  => Helpers::roundMoney( $total_revenue - $total_cost ),
            "lines"         => $lines,
        );
    }
}
