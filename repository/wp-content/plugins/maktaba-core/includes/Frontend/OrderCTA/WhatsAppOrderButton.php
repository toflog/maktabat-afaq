<?php
/**
 * Frontend\OrderCTA\WhatsAppOrderButton
 *
 * انظر: contracts/integration_contracts/whatsapp_order_message.md
 * انظر: interfaces/integration_points.md (Shortcode: [mkt_order_button])
 *
 * قاعدة الطبقة: يستدعي Admin/Settings فقط لجلب الرقم — لا نص ثابت
 * (hardcoded) في الكود. ممنوع إدراج أي حقل داخلي (سعر شراء/تكلفة) هنا.
 */

namespace Maktaba\Core\Frontend\OrderCTA;

use Maktaba\Core\Admin\Settings\GeneralSettings;
use Maktaba\Core\Domain\Book\BookRepository;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class WhatsAppOrderButton {

    public static function register() {
        add_shortcode( "mkt_order_button", array( __CLASS__, "render" ) );
    }

    /**
     * @param array $atts { book_id?: int } — افتراضي: المنتج الحالي داخل الحلقة
     * @return string HTML
     */
    public static function render( $atts = array() ) {
        $atts = shortcode_atts( array( "book_id" => get_the_ID() ), $atts );
        $book_id = (int) $atts["book_id"];

        if ( ! $book_id ) {
            return "";
        }

        $whatsapp_number = GeneralSettings::getWhatsAppNumber();

        if ( empty( $whatsapp_number ) ) {
            // لا نعرض زراً معطلاً بصمت — تنبيه واضح في وضع الإدارة فقط
            if ( current_user_can( "manage_options" ) ) {
                return "<p style=\"color:red\">⚠ لم يتم ضبط رقم واتساب المكتبة من الإعدادات.</p>";
            }
            return "";
        }

        $url = self::buildWhatsAppUrl( $book_id, $whatsapp_number );

        return sprintf(
            "<a class=\"mkt-order-button\" href=\"%s\" target=\"_blank\" rel=\"noopener\">%s</a>",
            esc_url( $url ),
            esc_html__( "اطلب الكتاب عبر واتساب", "maktaba-core" )
        );
    }

    /**
     * يبني رابط wa.me حسب القالب الإلزامي في العقد — بلا أي حقل داخلي.
     * يتضمن السعر بالعملتين معاً (دولار وليرة سورية) بدل عملة واحدة،
     * لتجنّب الاعتماد على تفضيل عملة الزائر وقت إنشاء الرسالة (ذلك
     * تفضيل واجهة بصرية بحتة في المتصفح — انظر Frontend/Pricing/CurrencyToggle).
     */
    public static function buildWhatsAppUrl( $book_id, $whatsapp_number ) {
        $title     = get_the_title( $book_id );
        $permalink = get_permalink( $book_id );

        $price_usd = BookRepository::getDisplayPriceUsd( $book_id );
        $price_syp = BookRepository::getDisplayPriceSyp( $book_id );

        $price_line = "";
        if ( null !== $price_usd && null !== $price_syp ) {
            $price_line = sprintf(
                "\nالسعر: %s$ (%s ل.س)",
                number_format_i18n( $price_usd, 2 ),
                number_format_i18n( $price_syp, 0 )
            );
        }

        $message = sprintf(
            "مرحباً، أريد طلب كتاب:\n\"%s\"%s\n\nرابط الكتاب:\n%s",
            $title,
            $price_line,
            $permalink
        );

        return sprintf(
            "https://wa.me/%s?text=%s",
            rawurlencode( $whatsapp_number ),
            rawurlencode( $message )
        );
    }
}
