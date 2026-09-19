<?php
/**
 * Shared\Helpers
 *
 * دوال مساعدة عامة بلا منطق عمل — انظر architecture/layer_rules.md
 */

namespace Maktaba\Core\Shared;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class Helpers {

    /**
     * حارس تنفيذ إلزامي لأي دالة تُعيد بيانات داخلية حساسة
     * (purchase_price, shipping_cost, profit ...).
     *
     * يطبّق عملياً القاعدة الأمنية في:
     * contracts/data_contracts/book.md
     * contracts/domain_contracts/pricing_and_profit.md
     *
     * @throws \RuntimeException إذا استُدعيت خارج سياق لوحة الإدارة.
     */
    public static function assertAdminContext( $caller_label ) {
        if ( ! is_admin() ) {
            throw new \RuntimeException(
                sprintf(
                    "Security contract violation: [%s] internal data requested outside admin context.",
                    $caller_label
                )
            );
        }
    }

    /**
     * تقريب موحّد للقيم المالية عبر المشروع (سنتان/قروش عشريان).
     */
    public static function roundMoney( $value ) {
        return round( (float) $value, 2 );
    }
}
