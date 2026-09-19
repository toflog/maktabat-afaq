<?php
/**
 * Admin\Pricing\PriceRecalculator
 *
 * انظر: contracts/domain_contracts/multi_currency_pricing.md
 *
 * نقطة التجميع الوحيدة لحساب وتخزين الأسعار متعددة العملات لكتاب واحد
 * أو كل الكتب دفعة واحدة. يجمع: BookRepository (Domain) +
 * CurrencyConverter (Domain) + GeneralSettings (Admin) — لذلك يعيش هنا
 * في Admin/ وليس في Domain/ (Domain ممنوع من معرفة GeneralSettings).
 *
 * يُستدعى من مكانين فقط:
 *   1) Admin/MetaBoxes/BookDetailsMetaBox::save() — عند حفظ كتاب واحد
 *   2) docker/wp-cli-scripts/recalculate-prices.sh عبر `wp eval` — لإعادة
 *      حساب كل الكتب دفعة واحدة بعد تغيير سعر الصرف
 */

namespace Maktaba\Core\Admin\Pricing;

use Maktaba\Core\Admin\Settings\GeneralSettings;
use Maktaba\Core\Domain\Book\BookRepository;
use Maktaba\Core\Domain\Pricing\CurrencyConverter;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class PriceRecalculator {

    /**
     * يحسب سعري العرض (دولار/ليرة سورية) لكتاب واحد من سعره الرسمي
     * بالجنيه المصري، ويخزّنهما، ويحدّث سعر WooCommerce الأساسي
     * (_regular_price/_price) بالقيمة بالدولار كعملة مرجعية داخلية.
     *
     * لا يفعل شيئاً إن لم يكن السعر الرسمي بالجنيه مضبوطاً بعد —
     * كتاب بلا سعر رسمي مُدخَل يبقى بلا سعر عرض، بدل افتراض صفر مضلِّل.
     *
     * @param int $book_id
     * @return bool تم الحساب فعلياً أم تم التجاوز (لا سعر رسمي مضبوط)
     */
    public static function recalculateOne( $book_id ) {
        $book_id       = (int) $book_id;
        $official_egp  = BookRepository::getOfficialPriceEgp( $book_id );

        if ( null === $official_egp || $official_egp <= 0 ) {
            return false;
        }

        $egp_per_usd = GeneralSettings::getEgpPerUsdRate();
        $syp_per_usd = GeneralSettings::getSypPerUsdRate();

        $price_usd = CurrencyConverter::egpToUsdWithMarkup( $official_egp, $egp_per_usd );
        $price_syp = CurrencyConverter::usdToSyp( $price_usd, $syp_per_usd );

        BookRepository::setDisplayPriceUsd( $book_id, $price_usd );
        BookRepository::setDisplayPriceSyp( $book_id, $price_syp );

        // العملة المرجعية الداخلية لـ WooCommerce نفسه (فرز/بحث/تقارير) هي
        // الدولار — لا تُستخدم مباشرة في أي عرض للزائر (انظر Frontend/Pricing).
        update_post_meta( $book_id, "_regular_price", $price_usd );
        update_post_meta( $book_id, "_price", $price_usd );

        return true;
    }

    /**
     * يعيد حساب كل الكتب (منتجات WooCommerce) دفعة واحدة — يُستخدم بعد
     * تحديث سعري الصرف من شاشة الإعدادات، عبر:
     *   docker compose run --rm wpcli sh /scripts/recalculate-prices.sh
     *
     * @return array{updated:int, skipped:int}
     */
    public static function recalculateAll() {
        $updated = 0;
        $skipped = 0;

        $query = new \WP_Query( array(
            "post_type"      => "product",
            "post_status"    => "publish",
            "posts_per_page" => -1,
            "fields"         => "ids",
            "no_found_rows"  => true,
        ) );

        foreach ( $query->posts as $book_id ) {
            if ( self::recalculateOne( $book_id ) ) {
                $updated++;
            } else {
                $skipped++;
            }
        }

        return array( "updated" => $updated, "skipped" => $skipped );
    }
}
