<?php
/**
 * Domain\Customer\CustomerSourceRepository
 *
 * الفجوة رقم 3 من تقرير الحالة — انظر: contracts/data_contracts/customer.md
 *
 * قاعدة العقد: هذا الجدول تجميعي فقط لأغراض التقارير — لا يُعبَّأ يدوياً،
 * ويُحدَّث تلقائياً (upsert بالهاتف) من
 * Integrations/WooCommerce/OrderMetaBridge::setSource() عند كل طلب.
 * ليس "مصدر حقيقة" بديلاً عن WooCommerce Order billing data.
 */

namespace Maktaba\Core\Domain\Customer;

use Maktaba\Core\Shared\Constants;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class CustomerSourceRepository {

    private static function table() {
        global $wpdb;
        return $wpdb->prefix . "mkt_customers";
    }

    /**
     * إنشاء/تحديث سجل عميل بالمفتاح الطبيعي (رقم الهاتف).
     *
     * @param string $phone
     * @param array  $data { name?, city?, area?, address?, notes?, source }
     * @return int معرّف السجل (جديد أو موجود)
     * @throws \InvalidArgumentException
     */
    public static function upsert( $phone, array $data ) {
        global $wpdb;

        $phone = self::normalizePhone( $phone );

        if ( "" === $phone ) {
            throw new \InvalidArgumentException( "رقم هاتف العميل مطلوب لتسجيله." );
        }

        if ( isset( $data["source"] ) && ! in_array( $data["source"], array(
            Constants::SOURCE_WEBSITE, Constants::SOURCE_FACEBOOK, Constants::SOURCE_INSTAGRAM,
            Constants::SOURCE_WHATSAPP, Constants::SOURCE_DIRECT, Constants::SOURCE_OTHER,
        ), true ) ) {
            throw new \InvalidArgumentException( "مصدر عميل غير صالح: {$data["source"]}" );
        }

        $existing = self::getByPhone( $phone );

        $fields = array(
            "phone"      => $phone,
            "name"       => $data["name"] ?? ( $existing["name"] ?? "" ),
            "city"       => $data["city"] ?? ( $existing["city"] ?? "" ),
            "area"       => $data["area"] ?? ( $existing["area"] ?? "" ),
            "address"    => $data["address"] ?? ( $existing["address"] ?? "" ),
            "notes"      => $data["notes"] ?? ( $existing["notes"] ?? "" ),
            "source"     => $data["source"] ?? ( $existing["source"] ?? Constants::SOURCE_OTHER ),
            "updated_at" => current_time( "mysql" ),
        );

        if ( $existing ) {
            $wpdb->update( self::table(), $fields, array( "id" => $existing["id"] ) );
            return (int) $existing["id"];
        }

        $fields["created_at"] = current_time( "mysql" );
        $wpdb->insert( self::table(), $fields );

        return (int) $wpdb->insert_id;
    }

    /**
     * @param string $phone
     * @return array|null
     */
    public static function getByPhone( $phone ) {
        global $wpdb;

        $phone = self::normalizePhone( $phone );

        if ( "" === $phone ) {
            return null;
        }

        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM " . self::table() . " WHERE phone = %s", $phone ),
            ARRAY_A
        );

        return $row ?: null;
    }

    /**
     * تجميع عدد العملاء حسب المصدر — لأغراض تقرير Section 20/50 من المخطط.
     *
     * @return array [ source => count ]
     */
    public static function countBySource() {
        global $wpdb;

        $rows = $wpdb->get_results(
            "SELECT source, COUNT(*) AS total FROM " . self::table() . " GROUP BY source",
            ARRAY_A
        );

        $counts = array();
        foreach ( $rows as $row ) {
            $counts[ $row["source"] ] = (int) $row["total"];
        }

        return $counts;
    }

    private static function normalizePhone( $phone ) {
        return preg_replace( "/[^0-9]/", "", (string) $phone );
    }
}
