<?php
/**
 * Admin\MetaBoxes\SupplierMetaBox
 *
 * حقول بيانات المورد على شاشة تعديل mkt_supplier.
 * انظر: contracts/data_contracts/supplier.md
 */

namespace Maktaba\Core\Admin\MetaBoxes;

use Maktaba\Core\Domain\Supplier\SupplierRepository;
use Maktaba\Core\Shared\Constants;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class SupplierMetaBox {

    const NONCE_FIELD = "mkt_supplier_meta_nonce";

    public static function register() {
        add_action( "add_meta_boxes", array( __CLASS__, "addBox" ) );
        add_action( "save_post_" . SupplierRepository::POST_TYPE, array( __CLASS__, "save" ) );
    }

    public static function addBox() {
        add_meta_box(
            "mkt_supplier_details",
            "بيانات المورد",
            array( __CLASS__, "render" ),
            SupplierRepository::POST_TYPE,
            "normal",
            "high"
        );
    }

    public static function render( $post ) {
        wp_nonce_field( "mkt_save_supplier", self::NONCE_FIELD );

        $contact_name = get_post_meta( $post->ID, "_mkt_contact_name", true );
        $phone        = get_post_meta( $post->ID, "_mkt_phone", true );
        $address      = get_post_meta( $post->ID, "_mkt_address", true );
        $notes        = get_post_meta( $post->ID, "_mkt_notes", true );
        $type         = get_post_meta( $post->ID, "_mkt_supplier_type", true );

        $types = array(
            Constants::SUPPLIER_DIRECT_PUBLISHER      => "ناشر مباشر",
            Constants::SUPPLIER_AUTHORIZED_DISTRIBUTOR => "موزع معتمد",
            Constants::SUPPLIER_SECONDARY_MARKET      => "سوق ثانوي",
        );
        ?>
        <table class="form-table">
            <tr>
                <th><label>اسم جهة الاتصال</label></th>
                <td><input type="text" name="mkt_contact_name" class="regular-text" value="<?php echo esc_attr( $contact_name ); ?>"></td>
            </tr>
            <tr>
                <th><label>الهاتف</label></th>
                <td><input type="text" name="mkt_phone" class="regular-text" value="<?php echo esc_attr( $phone ); ?>"></td>
            </tr>
            <tr>
                <th><label>العنوان</label></th>
                <td><textarea name="mkt_address" rows="2" class="large-text"><?php echo esc_textarea( $address ); ?></textarea></td>
            </tr>
            <tr>
                <th><label>نوع المورد</label></th>
                <td>
                    <select name="mkt_supplier_type" required>
                        <option value="">— اختر —</option>
                        <?php foreach ( $types as $value => $label ) : ?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $type, $value ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>ملاحظات</label></th>
                <td><textarea name="mkt_notes" rows="3" class="large-text"><?php echo esc_textarea( $notes ); ?></textarea></td>
            </tr>
        </table>
        <?php
    }

    public static function save( $post_id ) {
        if ( ! isset( $_POST[ self::NONCE_FIELD ] )
            || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ), "mkt_save_supplier" )
            || ! current_user_can( "edit_post", $post_id )
        ) {
            return;
        }

        $data = array(
            "contact_name"  => wp_unslash( $_POST["mkt_contact_name"] ?? "" ),
            "phone"         => wp_unslash( $_POST["mkt_phone"] ?? "" ),
            "address"       => wp_unslash( $_POST["mkt_address"] ?? "" ),
            "notes"         => wp_unslash( $_POST["mkt_notes"] ?? "" ),
            "supplier_type" => wp_unslash( $_POST["mkt_supplier_type"] ?? "" ),
        );

        try {
            SupplierRepository::update( $post_id, $data );
        } catch ( \InvalidArgumentException $e ) {
            // نوع مورد غير صالح/فارغ — نتجاهل الحفظ الجزئي لهذا الحقل
            // بدل كسر شاشة التعديل بالكامل؛ الحقل إلزامي بواجهة HTML أصلاً.
            unset( $data["supplier_type"] );
            SupplierRepository::update( $post_id, $data );
        }
    }
}
