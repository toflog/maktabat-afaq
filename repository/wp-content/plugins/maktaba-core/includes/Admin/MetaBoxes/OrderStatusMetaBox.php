<?php
/**
 * Admin\MetaBoxes\OrderStatusMetaBox
 *
 * صندوق "حالة المكتبة" على شاشة تعديل طلب WooCommerce — منفصل عن حالة
 * WooCommerce القياسية (order_status_transitions.md).
 */

namespace Maktaba\Core\Admin\MetaBoxes;

use Maktaba\Core\Admin\Ajax\AjaxHandlers;
use Maktaba\Core\Admin\Reports\ProfitReport;
use Maktaba\Core\Integrations\WooCommerce\OrderMetaBridge;
use Maktaba\Core\Shared\Constants;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class OrderStatusMetaBox {

    public static function register() {
        add_action( "add_meta_boxes", array( __CLASS__, "addBox" ) );
    }

    public static function addBox() {
        $screen = class_exists( "\\Automattic\\WooCommerce\\Utilities\\OrderUtil" )
            && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()
            ? wc_get_page_screen_id( "shop-order" )
            : "shop_order";

        add_meta_box(
            "mkt_library_status",
            "حالة المكتبة",
            array( __CLASS__, "render" ),
            $screen,
            "side",
            "high"
        );
    }

    public static function render( $post_or_order ) {
        $order_id = is_a( $post_or_order, "WC_Order" ) ? $post_or_order->get_id() : $post_or_order->ID;

        $current = OrderMetaBridge::getLibraryStatus( $order_id );
        $source  = OrderMetaBridge::getSource( $order_id );
        $nonce   = wp_create_nonce( AjaxHandlers::NONCE_ACTION );
        $profit  = ProfitReport::calculateForOrder( $order_id );
        ?>
        <p>الحالة الحالية: <strong><?php echo esc_html( $current ); ?></strong></p>

        <p>
            <label for="mkt-order-source">مصدر الطلب</label><br>
            <select id="mkt-order-source">
                <option value="">— غير محدَّد —</option>
                <?php
                $sources = array(
                    Constants::SOURCE_WEBSITE   => "الموقع",
                    Constants::SOURCE_FACEBOOK  => "فيسبوك",
                    Constants::SOURCE_INSTAGRAM => "انستغرام",
                    Constants::SOURCE_WHATSAPP  => "واتساب",
                    Constants::SOURCE_DIRECT    => "مباشر",
                    Constants::SOURCE_OTHER     => "أخرى",
                );
                foreach ( $sources as $value => $label ) :
                    ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $source, $value ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="button" id="mkt-order-source-save">حفظ المصدر</button>
            <span id="mkt-order-source-result"></span>
        </p>

        <hr>
            <select id="mkt-order-status">
                <?php foreach ( Constants::allOrderStatuses() as $status ) : ?>
                    <option value="<?php echo esc_attr( $status ); ?>" <?php selected( $current, $status ); ?>>
                        <?php echo esc_html( $status ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p><textarea id="mkt-order-note" placeholder="سبب (إلزامي عند القفز فوق حالة)" rows="2" class="large-text"></textarea></p>
        <p>
            <label><input type="checkbox" id="mkt-order-override"> السماح بالقفز فوق حالة (إجراء إداري)</label>
        </p>
        <p>
            <button type="button" class="button button-primary" id="mkt-order-submit">تحديث الحالة</button>
            <span id="mkt-order-result"></span>
        </p>

        <hr>
        <p>الربح التقديري لهذا الطلب: <strong><?php echo esc_html( $profit["total_profit"] ); ?></strong></p>

        <script>
        (function () {
            document.getElementById("mkt-order-submit").addEventListener("click", function () {
                var data = new FormData();
                data.append("action", "mkt_change_order_status");
                data.append("nonce", "<?php echo esc_js( $nonce ); ?>");
                data.append("order_id", "<?php echo (int) $order_id; ?>");
                data.append("new_status", document.getElementById("mkt-order-status").value);
                data.append("note", document.getElementById("mkt-order-note").value);
                data.append("admin_override", document.getElementById("mkt-order-override").checked ? "1" : "");

                var result = document.getElementById("mkt-order-result");
                result.textContent = "جارٍ التحديث...";

                fetch(ajaxurl, { method: "POST", body: data, credentials: "same-origin" })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        result.textContent = res.success ? "تم التحديث." : ("خطأ: " + res.data.message);
                    });
            });

            document.getElementById("mkt-order-source-save").addEventListener("click", function () {
                var data = new FormData();
                data.append("action", "mkt_set_order_source");
                data.append("nonce", "<?php echo esc_js( $nonce ); ?>");
                data.append("order_id", "<?php echo (int) $order_id; ?>");
                data.append("source", document.getElementById("mkt-order-source").value);

                var result = document.getElementById("mkt-order-source-result");
                result.textContent = "جارٍ الحفظ...";

                fetch(ajaxurl, { method: "POST", body: data, credentials: "same-origin" })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        result.textContent = res.success ? "تم." : ("خطأ: " + res.data.message);
                    });
            });
        })();
        </script>
        <?php
    }
}
