<?php
/**
 * Integrations\SocialContent\BookSocialContentBuilder
 *
 * انظر: contracts/integration_contracts/social_content_contract.md
 *
 * قاعدة الطبقة: لا يستدعي wc_get_product()/WC_Product مباشرة — يقرأ
 * بيانات الكتاب حصراً عبر
 * Integrations/WooCommerce/ProductAsBookBridge::getDisplayData()
 * (نقطة الاتصال الوحيدة المعتمدة بـ WooCommerce —
 * architecture/dependency_rules.md).
 *
 * لا نشر تلقائي API إلى Facebook/Instagram في V1 — فقط يولّد نصاً جاهزاً
 * للنسخ اليدوي (Zero-Cost/Zero-Complexity).
 */

namespace Maktaba\Core\Integrations\SocialContent;

use Maktaba\Core\Admin\Settings\GeneralSettings;
use Maktaba\Core\Integrations\WooCommerce\ProductAsBookBridge;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class BookSocialContentBuilder {

    /**
     * @param int $book_id
     * @return array { title, short_description, price, cover_image, hashtags, call_to_action, product_url }
     */
    public static function build( $book_id ) {
        $display = ProductAsBookBridge::getDisplayData( $book_id );

        if ( null === $display ) {
            return array();
        }

        return array(
            "title"             => $display["title"],
            "short_description" => $display["short_description"],
            "price"             => $display["price"],
            "cover_image"       => $display["cover_image_url"],
            "hashtags"          => GeneralSettings::getDefaultHashtags(),
            "call_to_action"    => "اطلب الآن عبر واتساب 📲",
            "product_url"       => $display["permalink"],
        );
    }

    /**
     * نص جاهز للنسخ مباشرة إلى منشور Facebook/Instagram.
     *
     * @param int $book_id
     * @return string
     */
    public static function buildAsText( $book_id ) {
        $data = self::build( $book_id );

        if ( empty( $data ) ) {
            return "";
        }

        $hashtags_line = ! empty( $data["hashtags"] )
            ? "\n\n" . implode( " ", array_map( function ( $tag ) {
                return "#" . ltrim( $tag, "#" );
            }, $data["hashtags"] ) )
            : "";

        return sprintf(
            "📖 %s\n\n%s\n\nالسعر: %s\n\n%s\n%s%s",
            $data["title"],
            $data["short_description"],
            $data["price"],
            $data["call_to_action"],
            $data["product_url"],
            $hashtags_line
        );
    }
}
