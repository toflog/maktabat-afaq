<?php
/**
 * Admin\MetaBoxes\SocialContentMetaBox
 *
 * صندوق على شاشة تعديل الكتاب لتوليد نص جاهز للنسخ على Facebook/Instagram.
 * انظر: contracts/integration_contracts/social_content_contract.md
 */

namespace Maktaba\Core\Admin\MetaBoxes;

use Maktaba\Core\Admin\Ajax\AjaxHandlers;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class SocialContentMetaBox {

    public static function register() {
        add_action( "add_meta_boxes", array( __CLASS__, "addBox" ) );
    }

    public static function addBox() {
        add_meta_box(
            "mkt_social_content",
            "محتوى جاهز للنشر",
            array( __CLASS__, "render" ),
            "product",
            "side",
            "default"
        );
    }

    public static function render( $post ) {
        $nonce = wp_create_nonce( AjaxHandlers::NONCE_ACTION );
        ?>
        <p>
            <button type="button" class="button" id="mkt-social-generate">توليد نص المنشور</button>
        </p>
        <textarea id="mkt-social-text" rows="8" class="large-text" readonly placeholder="اضغط الزر أعلاه لتوليد النص..."></textarea>

        <script>
        (function () {
            document.getElementById("mkt-social-generate").addEventListener("click", function () {
                var data = new FormData();
                data.append("action", "mkt_generate_social_content");
                data.append("nonce", "<?php echo esc_js( $nonce ); ?>");
                data.append("book_id", "<?php echo (int) $post->ID; ?>");

                fetch(ajaxurl, { method: "POST", body: data, credentials: "same-origin" })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        document.getElementById("mkt-social-text").value = res.success ? res.data.text : ("خطأ: " + res.data.message);
                    });
            });
        })();
        </script>
        <?php
    }
}
