<?php
/**
 * Admin\Purchase\PurchaseAdminPage
 *
 * نموذج بسيط بلا بناء JS معقّد (Zero-Cost/Zero-Complexity) لتسجيل شراء
 * بعدد ثابت من البنود (حتى 5 كتب دفعة واحدة) — يكفي لعمليات V1 اليدوية.
 * انظر: Section 18 من مخطط المشروع + contracts/data_contracts/purchase.md
 */

namespace Maktaba\Core\Admin\Purchase;

use Maktaba\Core\Domain\Purchase\PurchaseRepository;
use Maktaba\Core\Admin\Ajax\AjaxHandlers;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class PurchaseAdminPage {

    const MAX_ROWS = 5;
    const ACTION   = "mkt_record_purchase";

    public static function register() {
        add_action( "admin_post_" . self::ACTION, array( __CLASS__, "handleSubmit" ) );
    }

    public static function render() {
        if ( ! current_user_can( "manage_options" ) ) {
            return;
        }

        $suppliers = get_posts( array( "post_type" => "mkt_supplier", "posts_per_page" => -1, "post_status" => "publish" ) );
        $notice    = isset( $_GET["mkt_notice"] ) ? sanitize_text_field( wp_unslash( $_GET["mkt_notice"] ) ) : "";
        $nonce     = wp_create_nonce( AjaxHandlers::NONCE_ACTION );

        echo "<div class=\"wrap\"><h1>تسجيل شراء جديد</h1>";

        if ( "success" === $notice ) {
            echo "<div class=\"notice notice-success\"><p>تم تسجيل الشراء وتحديث المخزون بنجاح.</p></div>";
        } elseif ( "" !== $notice ) {
            echo "<div class=\"notice notice-error\"><p>" . esc_html( $notice ) . "</p></div>";
        }
        ?>
        <form method="post" action="<?php echo esc_url( admin_url( "admin-post.php" ) ); ?>">
            <input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
            <?php wp_nonce_field( self::ACTION, "mkt_nonce" ); ?>

            <table class="form-table">
                <tr>
                    <th><label for="supplier_id">المورد</label></th>
                    <td>
                        <select name="supplier_id" required>
                            <option value="">— اختر —</option>
                            <?php foreach ( $suppliers as $supplier ) : ?>
                                <option value="<?php echo esc_attr( $supplier->ID ); ?>">
                                    <?php echo esc_html( $supplier->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="reference_number">رقم المرجع</label></th>
                    <td><input type="text" name="reference_number" class="regular-text" required></td>
                </tr>
            </table>

            <h2>البنود</h2>
            <table class="widefat">
                <thead>
                    <tr><th style="width:45%">الكتاب</th><th>الكمية</th><th>تكلفة الوحدة</th></tr>
                </thead>
                <tbody>
                    <?php for ( $i = 0; $i < self::MAX_ROWS; $i++ ) : ?>
                        <tr>
                            <td class="mkt-book-search-cell" style="position:relative">
                                <input
                                    type="text"
                                    class="regular-text mkt-book-search-input"
                                    placeholder="اكتب اسم الكتاب أو ISBN..."
                                    autocomplete="off"
                                >
                                <input type="hidden" name="items[<?php echo $i; ?>][book_id]" class="mkt-book-search-id">
                                <div class="mkt-book-search-results"></div>
                            </td>
                            <td><input type="number" name="items[<?php echo $i; ?>][quantity]" min="1"></td>
                            <td><input type="number" step="0.01" name="items[<?php echo $i; ?>][unit_cost]" min="0"></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            <p class="description">ابحث عن الكتاب واختره من القائمة. اترك السطر فارغاً لتجاهله — الصفوف غير المكتملة (بلا كتاب محدَّد) تُهمَل تلقائياً.</p>

            <p>
                <label for="notes">ملاحظات</label><br>
                <textarea name="notes" rows="3" class="large-text"></textarea>
            </p>

            <?php submit_button( "تسجيل الشراء" ); ?>
        </form>
        </div>

        <style>
        .mkt-book-search-results {
            display: none;
            position: absolute;
            z-index: 10;
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            max-height: 220px;
            overflow-y: auto;
            width: 100%;
        }
        .mkt-book-search-results div {
            padding: 6px 8px;
            cursor: pointer;
        }
        .mkt-book-search-results div:hover {
            background: #f0f0f1;
        }
        .mkt-book-search-input.mkt-unresolved {
            border-color: #d63638;
        }
        </style>

        <script>
        (function () {
            var nonce = "<?php echo esc_js( $nonce ); ?>";
            var rows  = document.querySelectorAll(".mkt-book-search-cell");

            rows.forEach(function (cell) {
                var input   = cell.querySelector(".mkt-book-search-input");
                var hidden  = cell.querySelector(".mkt-book-search-id");
                var results = cell.querySelector(".mkt-book-search-results");
                var timer   = null;

                function closeResults() {
                    results.style.display = "none";
                    results.innerHTML = "";
                }

                function markUnresolved() {
                    hidden.value = "";
                    input.classList.add("mkt-unresolved");
                }

                input.addEventListener("input", function () {
                    markUnresolved();

                    var term = input.value.trim();
                    if (timer) { clearTimeout(timer); }

                    if (term.length < 2) {
                        closeResults();
                        return;
                    }

                    timer = setTimeout(function () {
                        var data = new FormData();
                        data.append("action", "mkt_search_books");
                        data.append("nonce", nonce);
                        data.append("term", term);

                        fetch(ajaxurl, { method: "POST", body: data, credentials: "same-origin" })
                            .then(function (r) { return r.json(); })
                            .then(function (res) {
                                closeResults();

                                if (!res.success || !res.data.results.length) {
                                    return;
                                }

                                res.data.results.forEach(function (item) {
                                    var row = document.createElement("div");
                                    row.textContent = item.label;
                                    row.addEventListener("click", function () {
                                        input.value = item.label;
                                        hidden.value = item.id;
                                        input.classList.remove("mkt-unresolved");
                                        closeResults();
                                    });
                                    results.appendChild(row);
                                });

                                results.style.display = "block";
                            });
                    }, 300);
                });

                input.addEventListener("blur", function () {
                    // مهلة صغيرة كي يُسجَّل النقر على عنصر القائمة قبل إخفائها
                    setTimeout(closeResults, 150);
                });
            });
        })();
        </script>
        <?php
    }

    public static function handleSubmit() {
        if ( ! current_user_can( "manage_options" )
            || ! isset( $_POST["mkt_nonce"] )
            || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST["mkt_nonce"] ) ), self::ACTION )
        ) {
            wp_die( "غير مصرَّح." );
        }

        $supplier_id      = isset( $_POST["supplier_id"] ) ? (int) $_POST["supplier_id"] : 0;
        $reference_number = isset( $_POST["reference_number"] ) ? sanitize_text_field( wp_unslash( $_POST["reference_number"] ) ) : "";
        $notes            = isset( $_POST["notes"] ) ? sanitize_textarea_field( wp_unslash( $_POST["notes"] ) ) : "";
        $raw_items        = isset( $_POST["items"] ) && is_array( $_POST["items"] ) ? wp_unslash( $_POST["items"] ) : array();

        $items = array();
        foreach ( $raw_items as $row ) {
            if ( ! empty( $row["book_id"] ) && ! empty( $row["quantity"] ) && isset( $row["unit_cost"] ) && $row["unit_cost"] !== "" ) {
                $items[] = array(
                    "book_id"   => (int) $row["book_id"],
                    "quantity"  => (int) $row["quantity"],
                    "unit_cost" => (float) $row["unit_cost"],
                );
            }
        }

        $redirect_url = admin_url( "admin.php?page=mkt-purchases" );

        try {
            PurchaseRepository::recordPurchase( $supplier_id, $reference_number, $items, $notes );
            $redirect_url = add_query_arg( "mkt_notice", "success", $redirect_url );
        } catch ( \Exception $e ) {
            $redirect_url = add_query_arg( "mkt_notice", rawurlencode( $e->getMessage() ), $redirect_url );
        }

        wp_safe_redirect( $redirect_url );
        exit;
    }
}
