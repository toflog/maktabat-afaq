<?php
/**
 * Frontend\Pricing\CurrencyToggle
 *
 * انظر: contracts/domain_contracts/multi_currency_pricing.md
 *
 * عنصر تبديل العملة (دولار/ليرة سورية) — يظهر تلقائياً في كل صفحة
 * (عبر wp_footer)، بلا الحاجة لإدراج Shortcode يدوياً. Vanilla JS فقط
 * (بلا مكتبات خارجية، التزاماً بـ Zero-Cost) — يبدّل ظهور/إخفاء
 * .mkt-price-usd و .mkt-price-syp (اللذين يُنشئهما
 * Integrations/WooCommerce/PriceDisplayBridge) عبر تفضيل محفوظ في
 * localStorage، فيبقى ثابتاً بين تصفّح الصفحات لنفس الزائر.
 *
 * ⚠ هذا العنصر بصري بحت — لا يحسب أي سعر بنفسه ولا يعرف شيئاً عن سعر
 * الصرف؛ الأسعار نفسها (لكلتا العملتين) مُحسَّبة ومُخزَّنة مسبقاً في
 * HTML من طرف الخادم قبل وصول الصفحة للمتصفح.
 */

namespace Maktaba\Core\Frontend\Pricing;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class CurrencyToggle {

    public static function register() {
        add_action( "wp_footer", array( __CLASS__, "render" ) );
    }

    public static function render() {
        if ( is_admin() ) {
            return;
        }
        ?>
        <div id="mkt-currency-toggle" style="position:fixed;bottom:16px;left:16px;z-index:9999;background:#fff;border:1px solid #E6DCC8;border-radius:20px;box-shadow:0 2px 8px rgba(43,33,28,0.15);padding:4px;font-family:inherit;">
            <button type="button" data-currency="usd" style="border:none;background:var(--mkt-color-primary,#6B2737);color:#fff;padding:6px 14px;border-radius:16px;cursor:pointer;font-size:0.85em;">$</button>
            <button type="button" data-currency="syp" style="border:none;background:transparent;color:var(--mkt-color-text,#2B211C);padding:6px 14px;border-radius:16px;cursor:pointer;font-size:0.85em;">ل.س</button>
        </div>
        <script>
        (function () {
            var STORAGE_KEY = "mkt_preferred_currency";
            var toggle = document.getElementById("mkt-currency-toggle");
            if (!toggle) { return; }

            var buttons = toggle.querySelectorAll("button");

            function applyCurrency(currency) {
                document.querySelectorAll(".mkt-price-usd").forEach(function (el) {
                    el.style.display = (currency === "usd") ? "" : "none";
                });
                document.querySelectorAll(".mkt-price-syp").forEach(function (el) {
                    el.style.display = (currency === "syp") ? "" : "none";
                });

                buttons.forEach(function (btn) {
                    var active = btn.getAttribute("data-currency") === currency;
                    btn.style.background = active ? "var(--mkt-color-primary, #6B2737)" : "transparent";
                    btn.style.color = active ? "#fff" : "var(--mkt-color-text, #2B211C)";
                });
            }

            buttons.forEach(function (btn) {
                btn.addEventListener("click", function () {
                    var currency = btn.getAttribute("data-currency");
                    try {
                        localStorage.setItem(STORAGE_KEY, currency);
                    } catch (e) {
                        // بعض المتصفحات في وضع التصفح الخاص قد تمنع localStorage — تجاهل بصمت
                    }
                    applyCurrency(currency);
                });
            });

            var saved = "usd";
            try {
                saved = localStorage.getItem(STORAGE_KEY) || "usd";
            } catch (e) {
                saved = "usd";
            }
            applyCurrency(saved);
        })();
        </script>
        <?php
    }
}
