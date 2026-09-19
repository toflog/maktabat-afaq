<?php
/**
 * Admin\Settings\GeneralSettings
 *
 * انظر: variables/environment_variables.md
 * غلاف بسيط فوق WordPress Options API — مصدر وحيد للإعدادات العامة
 * (رقم واتساب، حد الشحن المجاني...) بدل تكرارها كنصوص ثابتة في الكود.
 */

namespace Maktaba\Core\Admin\Settings;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class GeneralSettings {

    const OPTION_WHATSAPP_NUMBER         = "mkt_whatsapp_number";
    const OPTION_FREE_SHIPPING_THRESHOLD = "mkt_free_shipping_threshold";
    const OPTION_SOCIAL_HASHTAGS         = "mkt_social_default_hashtags";
    const OPTION_EGP_PER_USD             = "mkt_egp_per_usd_rate";
    const OPTION_SYP_PER_USD             = "mkt_syp_per_usd_rate";

    /**
     * @return string رقم واتساب بصيغة دولية بدون + أو مسافات (مثال: 963900000000)
     */
    public static function getWhatsAppNumber() {
        return (string) get_option( self::OPTION_WHATSAPP_NUMBER, "" );
    }

    public static function setWhatsAppNumber( $number ) {
        $clean = preg_replace( "/[^0-9]/", "", (string) $number );
        return update_option( self::OPTION_WHATSAPP_NUMBER, $clean );
    }

    /**
     * @return float|null null يعني: لا يوجد حد شحن مجاني مفعّل
     */
    public static function getFreeShippingThreshold() {
        $value = get_option( self::OPTION_FREE_SHIPPING_THRESHOLD, "" );
        return ( "" === $value ) ? null : (float) $value;
    }

    public static function setFreeShippingThreshold( $amount ) {
        return update_option( self::OPTION_FREE_SHIPPING_THRESHOLD, (float) $amount );
    }

    /**
     * @return string[] هاشتاغات افتراضية للمحتوى الاجتماعي
     */
    public static function getDefaultHashtags() {
        $value = get_option( self::OPTION_SOCIAL_HASHTAGS, "" );
        return $value ? array_filter( array_map( "trim", explode( ",", $value ) ) ) : array();
    }

    /* ---------------------------------------------------------------
     * أسعار الصرف للتسعير متعدد العملات
     * انظر: contracts/domain_contracts/multi_currency_pricing.md
     *
     * ⚠ القيم الافتراضية أدناه مرجعية فقط (تحديث 14 سبتمبر 2026، سعر
     * السوق الموازي في دمشق لليرة السورية وسعر البنك المركزي المصري
     * للجنيه) — **يجب على صاحب المكتبة تحديثها يدورياً من هنا** لأن
     * سعري الصرف (خصوصاً الليرة السورية) شديدا التقلب، ولا يوجد أي
     * جلب آلي مباشر لهما من الإنترنت في هذا الإصدار (V1) — قرار مقصود
     * لتفادي الاعتماد على مصدر خارجي غير مضمون الاستقرار داخل مسار حساس
     * كتسعير المنتجات (انظر أيضاً القسم الخاص بهذا القرار في دليل
     * المنهجية).
     * ------------------------------------------------------------- */

    /** @return float كم جنيهاً مصرياً يساوي دولاراً أمريكياً واحداً */
    public static function getEgpPerUsdRate() {
        $value = get_option( self::OPTION_EGP_PER_USD, "" );
        return ( "" === $value ) ? 51.4 : (float) $value;
    }

    public static function setEgpPerUsdRate( $rate ) {
        return update_option( self::OPTION_EGP_PER_USD, (float) $rate );
    }

    /** @return float كم ليرة سورية يساوي دولاراً أمريكياً واحداً */
    public static function getSypPerUsdRate() {
        $value = get_option( self::OPTION_SYP_PER_USD, "" );
        // مرجع 14 سبتمبر 2026 (سعر السوق الموازي، دمشق): ~133.75
        // ⚠ هذا رقم متقلب جداً ويجب تحديثه يدوياً بشكل متكرر من الإعدادات.
        return ( "" === $value ) ? 133.75 : (float) $value;
    }

    public static function setSypPerUsdRate( $rate ) {
        return update_option( self::OPTION_SYP_PER_USD, (float) $rate );
    }
}
