<?php
/**
 * Admin\Settings\SettingsAdminPage
 *
 * الفجوة الحرجة رقم 2 من تقرير الحالة: GeneralSettings كان غلافاً
 * برمجياً بلا واجهة — هذا الملف يضيف شاشة فعلية تحت قائمة "المكتبة".
 *
 * انظر: variables/environment_variables.md
 */

namespace Maktaba\Core\Admin\Settings;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class SettingsAdminPage {

    const ACTION = "mkt_save_settings";

    public static function register() {
        add_action( "admin_post_" . self::ACTION, array( __CLASS__, "handleSubmit" ) );
    }

    public static function render() {
        if ( ! current_user_can( "manage_options" ) ) {
            return;
        }

        $whatsapp_number = GeneralSettings::getWhatsAppNumber();
        $threshold       = GeneralSettings::getFreeShippingThreshold();
        $hashtags        = implode( ", ", GeneralSettings::getDefaultHashtags() );
        $egp_per_usd     = GeneralSettings::getEgpPerUsdRate();
        $syp_per_usd     = GeneralSettings::getSypPerUsdRate();
        $notice          = isset( $_GET["mkt_notice"] ) ? sanitize_text_field( wp_unslash( $_GET["mkt_notice"] ) ) : "";

        echo "<div class=\"wrap\"><h1>إعدادات المكتبة</h1>";

        if ( "success" === $notice ) {
            echo "<div class=\"notice notice-success\"><p>تم حفظ الإعدادات.</p></div>";
        }
        ?>
        <form method="post" action="<?php echo esc_url( admin_url( "admin-post.php" ) ); ?>">
            <input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
            <?php wp_nonce_field( self::ACTION, "mkt_settings_nonce" ); ?>

            <table class="form-table">
                <tr>
                    <th><label for="mkt_whatsapp_number">رقم واتساب المكتبة</label></th>
                    <td>
                        <input type="text" id="mkt_whatsapp_number" name="mkt_whatsapp_number"
                               class="regular-text" value="<?php echo esc_attr( $whatsapp_number ); ?>"
                               placeholder="963900000000">
                        <p class="description">
                            بصيغة دولية بدون + أو مسافات. هذا هو المصدر الوحيد المستخدم في
                            زر "اطلب الكتاب" ورابط واتساب — لا نص ثابت في الكود
                            (انظر contracts/integration_contracts/whatsapp_order_message.md).
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label for="mkt_free_shipping_threshold">حد الشحن المجاني</label></th>
                    <td>
                        <input type="number" step="0.01" id="mkt_free_shipping_threshold"
                               name="mkt_free_shipping_threshold" min="0"
                               value="<?php echo esc_attr( $threshold ); ?>">
                        <p class="description">اتركه فارغاً لتعطيل رسالة الشحن المجاني في مسار الطلب.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="mkt_hashtags">هاشتاغات افتراضية للمحتوى الاجتماعي</label></th>
                    <td>
                        <input type="text" id="mkt_hashtags" name="mkt_hashtags"
                               class="regular-text" value="<?php echo esc_attr( $hashtags ); ?>"
                               placeholder="مكتبة_آفاق, كتب_سوريا, اقرأ">
                        <p class="description">افصل بينها بفواصل — بلا علامة # (تُضاف تلقائياً).</p>
                    </td>
                </tr>
            </table>

            <h2>أسعار الصرف (للتسعير بالدولار والليرة السورية)</h2>
            <p class="description">
                سياسة التسعير: سعر البيع = (السعر الرسمي على موقع دار نهضة مصر بالجنيه
                المصري ÷ سعر الجنيه مقابل الدولار أدناه) × 1.20 — ثم يُحوَّل لليرة السورية
                عبر السعر الثاني. <strong>يجب تحديث هذين الرقمين يدوياً بانتظام</strong> —
                لا يوجد جلب آلي لهما من الإنترنت في هذا الإصدار (انظر دليل المنهجية).
            </p>
            <table class="form-table">
                <tr>
                    <th><label for="mkt_egp_per_usd">الجنيه المصري مقابل الدولار</label></th>
                    <td>
                        <input type="number" step="0.01" id="mkt_egp_per_usd" name="mkt_egp_per_usd"
                               min="0.01" value="<?php echo esc_attr( $egp_per_usd ); ?>">
                        <p class="description">كم جنيهاً مصرياً يساوي دولاراً واحداً الآن.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="mkt_syp_per_usd">الليرة السورية مقابل الدولار</label></th>
                    <td>
                        <input type="number" step="0.01" id="mkt_syp_per_usd" name="mkt_syp_per_usd"
                               min="0.01" value="<?php echo esc_attr( $syp_per_usd ); ?>">
                        <p class="description">كم ليرة سورية يساوي دولاراً واحداً الآن (السوق الموازي عادة).</p>
                    </td>
                </tr>
            </table>

            <p class="description" style="color:#b32d2e">
                ⚠ تغيير أي من الرقمين أعلاه <strong>لا يُعيد حساب أسعار الكتب المنشورة
                مسبقاً تلقائياً</strong> — الأسعار المعروضة مُحسَّبة ومُخزَّنة وقت حفظ كل
                كتاب فقط (انظر Admin/MetaBoxes/BookDetailsMetaBox). لإعادة حساب كل الكتب
                دفعة واحدة بعد تغيير السعر، استخدم سكربت WP-CLI
                <code>docker/wp-cli-scripts/recalculate-prices.sh</code>.
            </p>

            <?php submit_button( "حفظ الإعدادات" ); ?>
        </form>
        </div>
        <?php
    }

    public static function handleSubmit() {
        if ( ! current_user_can( "manage_options" )
            || ! isset( $_POST["mkt_settings_nonce"] )
            || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST["mkt_settings_nonce"] ) ), self::ACTION )
        ) {
            wp_die( "غير مصرَّح." );
        }

        if ( isset( $_POST["mkt_whatsapp_number"] ) ) {
            GeneralSettings::setWhatsAppNumber( wp_unslash( $_POST["mkt_whatsapp_number"] ) );
        }

        if ( isset( $_POST["mkt_free_shipping_threshold"] ) && "" !== $_POST["mkt_free_shipping_threshold"] ) {
            GeneralSettings::setFreeShippingThreshold( (float) $_POST["mkt_free_shipping_threshold"] );
        } else {
            update_option( GeneralSettings::OPTION_FREE_SHIPPING_THRESHOLD, "" );
        }

        if ( isset( $_POST["mkt_hashtags"] ) ) {
            update_option( GeneralSettings::OPTION_SOCIAL_HASHTAGS, sanitize_text_field( wp_unslash( $_POST["mkt_hashtags"] ) ) );
        }

        if ( isset( $_POST["mkt_egp_per_usd"] ) && "" !== $_POST["mkt_egp_per_usd"] ) {
            GeneralSettings::setEgpPerUsdRate( (float) $_POST["mkt_egp_per_usd"] );
        }

        if ( isset( $_POST["mkt_syp_per_usd"] ) && "" !== $_POST["mkt_syp_per_usd"] ) {
            GeneralSettings::setSypPerUsdRate( (float) $_POST["mkt_syp_per_usd"] );
        }

        wp_safe_redirect( add_query_arg( "mkt_notice", "success", admin_url( "admin.php?page=mkt-settings" ) ) );
        exit;
    }
}
