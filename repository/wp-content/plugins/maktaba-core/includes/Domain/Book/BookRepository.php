<?php
/**
 * Domain\Book\BookRepository
 *
 * طبقة وصول موحّدة لحقول الكتاب المخصصة (Post Meta).
 * انظر: contracts/data_contracts/book.md
 *
 * قاعدة الطبقة: Book = WooCommerce Product + Meta — هذا الملف يتعامل
 * فقط مع book_id (int) وPost Meta، ولا يستدعي أي WC_Product class
 * (ذلك محصور في Integrations/WooCommerce/ProductAsBookBridge.php).
 */

namespace Maktaba\Core\Domain\Book;

use Maktaba\Core\Shared\Constants;
use Maktaba\Core\Shared\Helpers;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class BookRepository {

    /* ---------------------------------------------------------------
     * حقول عامة (متاحة من أي طبقة — لا حساسية أمنية)
     * ------------------------------------------------------------- */

    public static function getIsbn( $book_id ) {
        return get_post_meta( (int) $book_id, "_mkt_isbn", true );
    }

    public static function setIsbn( $book_id, $isbn ) {
        return update_post_meta( (int) $book_id, "_mkt_isbn", sanitize_text_field( $isbn ) );
    }

    public static function getLanguage( $book_id ) {
        $lang = get_post_meta( (int) $book_id, "_mkt_language", true );
        return $lang ? $lang : "ar"; // افتراضي حسب مخطط المشروع (Section 2.2)
    }

    public static function setLanguage( $book_id, $language ) {
        return update_post_meta( (int) $book_id, "_mkt_language", sanitize_text_field( $language ) );
    }

    public static function getPageCount( $book_id ) {
        $count = get_post_meta( (int) $book_id, "_mkt_page_count", true );
        return $count !== "" ? (int) $count : null;
    }

    public static function setPageCount( $book_id, $page_count ) {
        return update_post_meta( (int) $book_id, "_mkt_page_count", (int) $page_count );
    }

    public static function getPublicationYear( $book_id ) {
        $year = get_post_meta( (int) $book_id, "_mkt_publication_year", true );
        return $year !== "" ? (int) $year : null;
    }

    public static function setPublicationYear( $book_id, $year ) {
        return update_post_meta( (int) $book_id, "_mkt_publication_year", (int) $year );
    }

    /**
     * حقل احتياطي غير مفعّل بالواجهة في V1 — contracts/data_contracts/book.md
     *
     * @return string واحدة من Constants::BOOK_CONDITION_NEW / BOOK_CONDITION_USED
     */
    public static function getCondition( $book_id ) {
        $condition = get_post_meta( (int) $book_id, "_mkt_condition", true );
        return $condition ? $condition : Constants::BOOK_CONDITION_NEW;
    }

    public static function setCondition( $book_id, $condition ) {
        if ( ! in_array( $condition, array( Constants::BOOK_CONDITION_NEW, Constants::BOOK_CONDITION_USED ), true ) ) {
            throw new \InvalidArgumentException( "Invalid book condition: {$condition}" );
        }
        return update_post_meta( (int) $book_id, "_mkt_condition", $condition );
    }

    /* ---------------------------------------------------------------
     * التسعير متعدد العملات (سوري + دولار)
     * انظر: contracts/domain_contracts/multi_currency_pricing.md
     *
     * official_price_egp هو المصدر الوحيد للحقيقة — السعر كما يظهر
     * حرفياً على موقع دار نهضة مصر بالجنيه المصري. كل شيء آخر (USD/SYP)
     * محسوب منه عبر Domain/Pricing/CurrencyConverter ومُخزَّن كـ "كاش"
     * وقت الحفظ فقط (انظر Admin/MetaBoxes/BookDetailsMetaBox::save())
     * — Frontend يقرأ القيم الجاهزة هنا فقط، ولا يحسب شيئاً بنفسه ولا
     * يعرف شيئاً عن أسعار الصرف أو نسبة الهامش (قاعدة طبقات صارمة).
     * ------------------------------------------------------------- */

    public static function getOfficialPriceEgp( $book_id ) {
        $value = get_post_meta( (int) $book_id, "_mkt_official_price_egp", true );
        return ( "" === $value ) ? null : (float) $value;
    }

    public static function setOfficialPriceEgp( $book_id, $price_egp ) {
        return update_post_meta( (int) $book_id, "_mkt_official_price_egp", Helpers::roundMoney( $price_egp ) );
    }

    public static function getDisplayPriceUsd( $book_id ) {
        $value = get_post_meta( (int) $book_id, "_mkt_display_price_usd", true );
        return ( "" === $value ) ? null : (float) $value;
    }

    public static function setDisplayPriceUsd( $book_id, $price_usd ) {
        return update_post_meta( (int) $book_id, "_mkt_display_price_usd", Helpers::roundMoney( $price_usd ) );
    }

    public static function getDisplayPriceSyp( $book_id ) {
        $value = get_post_meta( (int) $book_id, "_mkt_display_price_syp", true );
        return ( "" === $value ) ? null : (float) $value;
    }

    public static function setDisplayPriceSyp( $book_id, $price_syp ) {
        // الليرة السورية بلا كسور عشرية عملياً — تقريب لأقرب رقم صحيح.
        return update_post_meta( (int) $book_id, "_mkt_display_price_syp", (float) round( (float) $price_syp ) );
    }

    /* ---------------------------------------------------------------
     * حقول داخلية حساسة — ممنوع الوصول إليها خارج سياق الإدارة
     * (contracts/domain_contracts/pricing_and_profit.md)
     * ------------------------------------------------------------- */

    /**
     * @throws \RuntimeException إذا استُدعيت من خارج wp-admin
     */
    public static function getPurchasePrice( $book_id ) {
        Helpers::assertAdminContext( "BookRepository::getPurchasePrice" );
        return (float) get_post_meta( (int) $book_id, "_mkt_purchase_price", true );
    }

    public static function setPurchasePrice( $book_id, $price ) {
        Helpers::assertAdminContext( "BookRepository::setPurchasePrice" );
        return update_post_meta( (int) $book_id, "_mkt_purchase_price", Helpers::roundMoney( $price ) );
    }

    /**
     * @throws \RuntimeException إذا استُدعيت من خارج wp-admin
     */
    public static function getShippingCost( $book_id ) {
        Helpers::assertAdminContext( "BookRepository::getShippingCost" );
        return (float) get_post_meta( (int) $book_id, "_mkt_shipping_cost", true );
    }

    public static function setShippingCost( $book_id, $cost ) {
        Helpers::assertAdminContext( "BookRepository::setShippingCost" );
        return update_post_meta( (int) $book_id, "_mkt_shipping_cost", Helpers::roundMoney( $cost ) );
    }
}
