<?php
/**
 * Admin\MetaBoxes\BookDetailsMetaBox
 *
 * الفجوة الحرجة رقم 1 من تقرير الحالة: لا توجد واجهة إدخال لحقول
 * الكتاب التفصيلية رغم وجود getters/setters كاملة في
 * Domain/Book/BookRepository.php — هذا الملف يسدّها.
 *
 * انظر: contracts/data_contracts/book.md
 *
 * قاعدة أمنية حرجة: حقلا سعر الشراء وتكلفة الشحن داخليان بالكامل —
 * هذا الصندوق يظهر فقط لمن يملك manage_options، ولا يُطبع أي منهما
 * في أي مخرج غير إداري (القراءة تمر عبر BookRepository الذي يفرض
 * assertAdminContext أصلاً كطبقة حماية ثانية).
 */

namespace Maktaba\Core\Admin\MetaBoxes;

use Maktaba\Core\Domain\Book\BookRepository;
use Maktaba\Core\Shared\Constants;
use Maktaba\Core\Admin\Pricing\PriceRecalculator;
use Maktaba\Core\Admin\Settings\GeneralSettings;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class BookDetailsMetaBox {

    const NONCE_FIELD  = "mkt_book_details_nonce";
    const NONCE_ACTION = "mkt_save_book_details";

    public static function register() {
        add_action( "add_meta_boxes", array( __CLASS__, "addBox" ) );
        add_action( "save_post_product", array( __CLASS__, "save" ), 20, 1 );
    }

    public static function addBox() {
        add_meta_box(
            "mkt_book_details",
            "بيانات الكتاب (مخطط المشروع — Section 12)",
            array( __CLASS__, "render" ),
            "product",
            "normal",
            "high"
        );
    }

    public static function render( $post ) {
        if ( ! current_user_can( "manage_options" ) ) {
            echo "<p>لا تملك صلاحية عرض هذه البيانات.</p>";
            return;
        }

        wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

        $book_id = $post->ID;

        $isbn             = BookRepository::getIsbn( $book_id );
        $language         = BookRepository::getLanguage( $book_id );
        $page_count       = BookRepository::getPageCount( $book_id );
        $publication_year = BookRepository::getPublicationYear( $book_id );
        $condition        = BookRepository::getCondition( $book_id );

        // حقول داخلية حساسة — assertAdminContext يمرّ لأننا داخل is_admin() فعلاً هنا.
        $purchase_price = BookRepository::getPurchasePrice( $book_id );
        $shipping_cost  = BookRepository::getShippingCost( $book_id );

        $official_price_egp = BookRepository::getOfficialPriceEgp( $book_id );
        $display_price_usd  = BookRepository::getDisplayPriceUsd( $book_id );
        $display_price_syp  = BookRepository::getDisplayPriceSyp( $book_id );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="mkt_isbn">ISBN</label></th>
                <td><input type="text" id="mkt_isbn" name="mkt_isbn" class="regular-text" value="<?php echo esc_attr( $isbn ); ?>"></td>
            </tr>
            <tr>
                <th><label for="mkt_language">اللغة</label></th>
                <td><input type="text" id="mkt_language" name="mkt_language" class="regular-text" value="<?php echo esc_attr( $language ); ?>" placeholder="ar"></td>
            </tr>
            <tr>
                <th><label for="mkt_page_count">عدد الصفحات</label></th>
                <td><input type="number" id="mkt_page_count" name="mkt_page_count" min="0" value="<?php echo esc_attr( $page_count ); ?>"></td>
            </tr>
            <tr>
                <th><label for="mkt_publication_year">سنة النشر</label></th>
                <td><input type="number" id="mkt_publication_year" name="mkt_publication_year" min="0" value="<?php echo esc_attr( $publication_year ); ?>"></td>
            </tr>
            <tr>
                <th><label for="mkt_condition">حالة الكتاب</label></th>
                <td>
                    <select id="mkt_condition" name="mkt_condition">
                        <option value="<?php echo esc_attr( Constants::BOOK_CONDITION_NEW ); ?>" <?php selected( $condition, Constants::BOOK_CONDITION_NEW ); ?>>جديد</option>
                        <option value="<?php echo esc_attr( Constants::BOOK_CONDITION_USED ); ?>" <?php selected( $condition, Constants::BOOK_CONDITION_USED ); ?>>مستعمل</option>
                    </select>
                    <p class="description">حقل احتياطي غير مُستخدم في واجهة العرض العامة في V1 (انظر contracts/data_contracts/book.md).</p>
                </td>
            </tr>
        </table>

        <hr>
        <h4>التسعير (دار نهضة مصر + 20%، بالدولار والليرة السورية)</h4>
        <table class="form-table">
            <tr>
                <th><label for="mkt_official_price_egp">السعر الرسمي على موقع دار نهضة مصر (جنيه مصري)</label></th>
                <td>
                    <input type="number" step="0.01" id="mkt_official_price_egp" name="mkt_official_price_egp"
                           min="0" value="<?php echo esc_attr( $official_price_egp ); ?>">
                    <p class="description">
                        انسخه حرفياً كما يظهر على nahdetmisr.com لهذا الكتاب تحديداً —
                        لا تُدخل سعر أي مكتبة أو موزّع آخر (انظر دليل منهجية جمع
                        البيانات، القسم الخاص بالتسعير). هذا هو الحقل الوحيد الذي
                        تُدخله يدوياً؛ كل ما تحته يُحسَب تلقائياً عند الحفظ.
                    </p>
                </td>
            </tr>
            <tr>
                <th>السعر المعروض للزوار (محسوب تلقائياً)</th>
                <td>
                    <?php if ( null !== $display_price_usd ) : ?>
                        <strong><?php echo esc_html( number_format_i18n( $display_price_usd, 2 ) ); ?> $</strong>
                        &nbsp;/&nbsp;
                        <strong><?php echo esc_html( number_format_i18n( (float) $display_price_syp, 0 ) ); ?> ل.س</strong>
                        <p class="description">
                            بأسعار الصرف الحالية في الإعدادات (<?php echo esc_html( number_format_i18n( GeneralSettings::getEgpPerUsdRate(), 2 ) ); ?>
                            ج.م/$، <?php echo esc_html( number_format_i18n( GeneralSettings::getSypPerUsdRate(), 2 ) ); ?> ل.س/$).
                            هذه القيم تُحدَّث فقط عند حفظ هذا الكتاب من جديد، أو عبر
                            سكربت إعادة الحساب الدفعي بعد تغيير سعر الصرف.
                        </p>
                    <?php else : ?>
                        <em>أدخل السعر الرسمي أعلاه واحفظ — سيظهر السعر المحسوب هنا.</em>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <hr>
        <h4 style="color:#b32d2e">بيانات داخلية — لا تظهر للعميل مطلقاً</h4>
        <table class="form-table">
            <tr>
                <th><label for="mkt_purchase_price">سعر الشراء</label></th>
                <td><input type="number" step="0.01" id="mkt_purchase_price" name="mkt_purchase_price" min="0" value="<?php echo esc_attr( $purchase_price ); ?>"></td>
            </tr>
            <tr>
                <th><label for="mkt_shipping_cost">تكلفة الوصول</label></th>
                <td><input type="number" step="0.01" id="mkt_shipping_cost" name="mkt_shipping_cost" min="0" value="<?php echo esc_attr( $shipping_cost ); ?>"></td>
            </tr>
        </table>
        <?php
    }

    public static function save( $post_id ) {
        if ( ! isset( $_POST[ self::NONCE_FIELD ] )
            || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ), self::NONCE_ACTION )
            || ! current_user_can( "manage_options" )
            || ( defined( "DOING_AUTOSAVE" ) && DOING_AUTOSAVE )
        ) {
            return;
        }

        if ( isset( $_POST["mkt_isbn"] ) ) {
            BookRepository::setIsbn( $post_id, wp_unslash( $_POST["mkt_isbn"] ) );
        }

        if ( isset( $_POST["mkt_language"] ) && "" !== trim( $_POST["mkt_language"] ) ) {
            BookRepository::setLanguage( $post_id, wp_unslash( $_POST["mkt_language"] ) );
        }

        if ( isset( $_POST["mkt_page_count"] ) && "" !== $_POST["mkt_page_count"] ) {
            BookRepository::setPageCount( $post_id, (int) $_POST["mkt_page_count"] );
        }

        if ( isset( $_POST["mkt_publication_year"] ) && "" !== $_POST["mkt_publication_year"] ) {
            BookRepository::setPublicationYear( $post_id, (int) $_POST["mkt_publication_year"] );
        }

        if ( isset( $_POST["mkt_condition"] ) ) {
            try {
                BookRepository::setCondition( $post_id, sanitize_text_field( wp_unslash( $_POST["mkt_condition"] ) ) );
            } catch ( \InvalidArgumentException $e ) {
                // تجاهل قيمة غير صالحة بدل كسر الحفظ بالكامل — القيمة الافتراضية تبقى "جديد".
            }
        }

        if ( isset( $_POST["mkt_purchase_price"] ) && "" !== $_POST["mkt_purchase_price"] ) {
            BookRepository::setPurchasePrice( $post_id, (float) $_POST["mkt_purchase_price"] );
        }

        if ( isset( $_POST["mkt_shipping_cost"] ) && "" !== $_POST["mkt_shipping_cost"] ) {
            BookRepository::setShippingCost( $post_id, (float) $_POST["mkt_shipping_cost"] );
        }

        if ( isset( $_POST["mkt_official_price_egp"] ) && "" !== $_POST["mkt_official_price_egp"] ) {
            BookRepository::setOfficialPriceEgp( $post_id, (float) $_POST["mkt_official_price_egp"] );
            PriceRecalculator::recalculateOne( $post_id );
        }
    }
}
