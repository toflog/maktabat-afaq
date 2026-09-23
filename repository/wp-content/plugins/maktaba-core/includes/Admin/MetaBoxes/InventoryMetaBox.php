<?php
/**
 * Admin\MetaBoxes\InventoryMetaBox
 *
 * صندوق على شاشة تعديل الكتاب (product) لعرض سجل الحركات وتسجيل
 * تعديل/تلف يدوي — عبر AJAX (mkt_add_inventory_movement).
 * انظر: Section 16 من مخطط المشروع + Admin\Ajax\AjaxHandlers
 */

namespace Maktaba\Core\Admin\MetaBoxes;

use Maktaba\Core\Domain\Inventory\StockCalculator;
use Maktaba\Core\Admin\Ajax\AjaxHandlers;
use Maktaba\Core\Shared\Constants;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class InventoryMetaBox {

    public static function register() {
        add_action( "add_meta_boxes", array( __CLASS__, "addBox" ) );
    }

    public static function addBox() {
        add_meta_box(
            "mkt_inventory_movements",
            "سجل حركات المخزون",
            array( __CLASS__, "render" ),
            "product",
            "normal",
            "default"
        );
    }

    public static function render( $post ) {
        $book_id   = $post->ID;
        $stock_now = StockCalculator::calculateStock( $book_id );
        $movements = StockCalculator::getMovements( $book_id, array( "limit" => 10 ) );
        $nonce     = wp_create_nonce( AjaxHandlers::NONCE_ACTION );
        ?>
        <p>المخزون الحالي (محسوب من سجل الحركات): <strong><?php echo (int) $stock_now; ?></strong></p>

        <table class="widefat" style="margin-bottom:10px">
            <thead><tr><th>التاريخ</th><th>النوع</th><th>الكمية</th><th>ملاحظة</th></tr></thead>
            <tbody>
                <?php if ( empty( $movements ) ) : ?>
                    <tr><td colspan="4">لا توجد حركات مسجَّلة بعد.</td></tr>
                <?php else : foreach ( $movements as $m ) : ?>
                    <tr>
                        <td><?php echo esc_html( $m["created_at"] ); ?></td>
                        <td><?php echo esc_html( $m["type"] ); ?></td>
                        <td><?php echo (int) $m["quantity"]; ?></td>
                        <td><?php echo esc_html( $m["note"] ); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <h4>تسجيل حركة يدوية (تعديل/تلف)</h4>
        <p>
            <select id="mkt-mv-type">
                <option value="<?php echo esc_attr( Constants::MOVEMENT_DAMAGED ); ?>">تالف (نقص)</option>
                <option value="<?php echo esc_attr( Constants::MOVEMENT_ADJUSTMENT ); ?>">تعديل يدوي (± حسب الإشارة)</option>
            </select>
            <input type="number" id="mkt-mv-quantity" placeholder="الكمية (استخدم سالباً للتعديل الناقص)" style="width:220px">
        </p>
        <p><textarea id="mkt-mv-note" placeholder="السبب (إلزامي للتعديل اليدوي)" rows="2" class="large-text"></textarea></p>
        <p>
            <button type="button" class="button button-secondary" id="mkt-mv-submit">تسجيل الحركة</button>
            <span id="mkt-mv-result"></span>
        </p>

        <script>
        (function () {
            document.getElementById("mkt-mv-submit").addEventListener("click", function () {
                var type     = document.getElementById("mkt-mv-type").value;
                var quantity = document.getElementById("mkt-mv-quantity").value;
                var note     = document.getElementById("mkt-mv-note").value;
                var result   = document.getElementById("mkt-mv-result");

                result.textContent = "جارٍ الحفظ...";

                var data = new FormData();
                data.append("action", "mkt_add_inventory_movement");
                data.append("nonce", "<?php echo esc_js( $nonce ); ?>");
                data.append("book_id", "<?php echo (int) $book_id; ?>");
                data.append("type", type);
                data.append("quantity", quantity);
                data.append("note", note);

                fetch(ajaxurl, { method: "POST", body: data, credentials: "same-origin" })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (res.success) {
                            result.textContent = "تم — المخزون الجديد: " + res.data.new_stock;
                        } else {
                            result.textContent = "خطأ: " + res.data.message;
                        }
                    });
            });
        })();
        </script>
        <?php
    }
}
