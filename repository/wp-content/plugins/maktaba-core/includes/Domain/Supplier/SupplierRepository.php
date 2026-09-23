<?php
/**
 * Domain\Supplier\SupplierRepository
 *
 * انظر: contracts/data_contracts/supplier.md
 * Supplier = Custom Post Type `mkt_supplier` (يُسجَّل في Integrations/PostTypes.php)
 */

namespace Maktaba\Core\Domain\Supplier;

use Maktaba\Core\Shared\Constants;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class SupplierRepository {

    const POST_TYPE = "mkt_supplier";

    /**
     * إنشاء مورد جديد.
     *
     * @param array $data { name, contact_name?, phone?, address?, notes?, supplier_type }
     * @return int معرّف المورد الجديد
     * @throws \InvalidArgumentException
     */
    public static function create( array $data ) {
        if ( empty( $data["name"] ) ) {
            throw new \InvalidArgumentException( "اسم المورد إلزامي." );
        }

        self::assertValidSupplierType( $data["supplier_type"] ?? null );

        $post_id = wp_insert_post( array(
            "post_type"   => self::POST_TYPE,
            "post_title"  => sanitize_text_field( $data["name"] ),
            "post_status" => "publish",
        ), true );

        if ( is_wp_error( $post_id ) ) {
            throw new \RuntimeException( "تعذّر إنشاء المورد: " . $post_id->get_error_message() );
        }

        self::saveMeta( $post_id, $data );

        return (int) $post_id;
    }

    public static function update( $supplier_id, array $data ) {
        if ( isset( $data["supplier_type"] ) ) {
            self::assertValidSupplierType( $data["supplier_type"] );
        }

        if ( isset( $data["name"] ) ) {
            wp_update_post( array( "ID" => $supplier_id, "post_title" => sanitize_text_field( $data["name"] ) ) );
        }

        self::saveMeta( $supplier_id, $data );
    }

    public static function getSupplierType( $supplier_id ) {
        return get_post_meta( (int) $supplier_id, "_mkt_supplier_type", true );
    }

    public static function getPhone( $supplier_id ) {
        return get_post_meta( (int) $supplier_id, "_mkt_phone", true );
    }

    public static function isActive( $supplier_id ) {
        return "publish" === get_post_status( $supplier_id );
    }

    public static function setActive( $supplier_id, $active ) {
        wp_update_post( array(
            "ID"          => $supplier_id,
            "post_status" => $active ? "publish" : "draft",
        ) );
    }

    private static function saveMeta( $supplier_id, array $data ) {
        $map = array(
            "contact_name"  => "_mkt_contact_name",
            "phone"         => "_mkt_phone",
            "address"       => "_mkt_address",
            "notes"         => "_mkt_notes",
            "supplier_type" => "_mkt_supplier_type",
        );

        foreach ( $map as $field => $meta_key ) {
            if ( array_key_exists( $field, $data ) ) {
                update_post_meta( $supplier_id, $meta_key, sanitize_text_field( $data[ $field ] ) );
            }
        }
    }

    private static function assertValidSupplierType( $type ) {
        $valid = array(
            Constants::SUPPLIER_DIRECT_PUBLISHER,
            Constants::SUPPLIER_AUTHORIZED_DISTRIBUTOR,
            Constants::SUPPLIER_SECONDARY_MARKET,
        );

        if ( null !== $type && ! in_array( $type, $valid, true ) ) {
            throw new \InvalidArgumentException( "نوع مورد غير صالح: {$type}" );
        }
    }
}
