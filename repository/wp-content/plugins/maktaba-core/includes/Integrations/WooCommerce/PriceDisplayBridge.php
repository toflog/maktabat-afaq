<?php
/**
 * Integrations\WooCommerce\PriceDisplayBridge
 *
 * انظر: contracts/domain_contracts/multi_currency_pricing.md
 * انظر: contracts/integration_contracts/woocommerce_integration.md
 *
 * الجسر الوحيد المسموح له بلمس خطاف WooCommerce الخاص بعرض السعر
 * (woocommerce_get_price_html) — القاعدة المعمارية تحصر أي استخدام
 * لخطافات/أصناف WooCommerce داخل Integrations/WooCommerce/ حصراً.
 *
 * يقرأ فقط (BookRepository::getDisplayPriceUsd/Syp) — لا يحسب شيئاً
 * بنفسه ولا يعرف شيئاً عن سعر الصرف أو نسبة الهامش (ذلك محسوب ومخزَّن
 * مسبقاً وقت حفظ الكتاب عبر Admin/Pricing/PriceRecalculator).
 */

namespace Maktaba\Core\Integrations\WooCommerce;

use Maktaba\Core\Domain\Book\BookRepository;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class PriceDisplayBridge {

    public static function register() {
        add_filter( "woocommerce_get_price_html", array( __CLASS__, "renderDualCurrencyPrice" ), 10, 2 );
    }

    /**
     * @param string      $price_html HTML الافتراضي من WooCommerce (يُستخدم كاحتياط)
     * @param \WC_Product $product
     * @return string
     */
    public static function renderDualCurrencyPrice( $price_html, $product ) {
        $book_id = $product->get_id();

        $usd = BookRepository::getDisplayPriceUsd( $book_id );
        $syp = BookRepository::getDisplayPriceSyp( $book_id );

        // لا سعر عرض محسوب بعد (لم يُدخَل السعر الرسمي بالجنيه المصري) —
        // اعرض تنبيهاً في وضع الإدارة فقط، والسعر الافتراضي لِلزوار.
        if ( null === $usd || null === $syp ) {
            if ( current_user_can( "manage_options" ) ) {
                return "<span style=\"color:red\">⚠ لم يُدخَل السعر الرسمي بالجنيه المصري بعد</span>";
            }
            return $price_html;
        }

        return sprintf(
            '<span class="mkt-dual-price">' .
                '<span class="mkt-price-usd">%s $</span>' .
                '<span class="mkt-price-syp" style="display:none">%s ل.س</span>' .
            '</span>',
            esc_html( number_format_i18n( $usd, 2 ) ),
            esc_html( number_format_i18n( $syp, 0 ) )
        );
    }
}
