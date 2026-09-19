<?php
/**
 * Domain\Pricing\CurrencyConverter
 *
 * انظر: contracts/domain_contracts/multi_currency_pricing.md
 *
 * قاعدة الطبقة الحرجة (نفس نمط ProfitCalculator): هذا الملف لا يجلب
 * أسعار الصرف بنفسه من الإعدادات (GeneralSettings تعيش في Admin/) —
 * يستقبلها كمُعاملات صريحة من المستدعي. هذا يبقي Domain مستقلاً تماماً
 * عن مصدر أسعار الصرف (إعدادات المستخدم اليوم، قد يكون API خارجي لاحقاً).
 *
 * سياسة التسعير (مصدر الحقيقة الوحيد لهذه القاعدة):
 *   سعر البيع بالدولار = (السعر الرسمي بالجنيه المصري ÷ سعر الجنيه
 *   مقابل الدولار) × 1.20   [هامش 20% ثابت فوق سعر دار نهضة مصر]
 *   سعر البيع بالليرة السورية = سعر البيع بالدولار × سعر الدولار
 *   مقابل الليرة السورية
 */

namespace Maktaba\Core\Domain\Pricing;

use Maktaba\Core\Shared\Helpers;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class CurrencyConverter {

    const MARKUP_PERCENT = 20.0;

    /**
     * @param float $price_egp    السعر كما يظهر حرفياً على موقع دار نهضة مصر
     * @param float $egp_per_usd  كم جنيهاً مصرياً يساوي دولاراً واحداً (رقم موجب)
     * @param float $markup_percent نسبة الهامش المئوية (افتراضي 20%)
     * @return float سعر البيع النهائي بالدولار
     * @throws \InvalidArgumentException إن كان سعر الصرف صفراً أو سالباً
     */
    public static function egpToUsdWithMarkup( $price_egp, $egp_per_usd, $markup_percent = self::MARKUP_PERCENT ) {
        $price_egp   = (float) $price_egp;
        $egp_per_usd = (float) $egp_per_usd;

        if ( $egp_per_usd <= 0 ) {
            throw new \InvalidArgumentException( "egp_per_usd must be a positive number" );
        }

        $base_usd = $price_egp / $egp_per_usd;
        $final_usd = $base_usd * ( 1 + ( (float) $markup_percent / 100 ) );

        return Helpers::roundMoney( $final_usd );
    }

    /**
     * @param float $price_usd   سعر البيع بالدولار (بعد الهامش أصلاً)
     * @param float $syp_per_usd كم ليرة سورية يساوي دولاراً واحداً
     * @return float سعر البيع بالليرة السورية (مُقرَّب لأقرب رقم صحيح)
     * @throws \InvalidArgumentException إن كان سعر الصرف صفراً أو سالباً
     */
    public static function usdToSyp( $price_usd, $syp_per_usd ) {
        $price_usd   = (float) $price_usd;
        $syp_per_usd = (float) $syp_per_usd;

        if ( $syp_per_usd <= 0 ) {
            throw new \InvalidArgumentException( "syp_per_usd must be a positive number" );
        }

        return round( $price_usd * $syp_per_usd );
    }
}
