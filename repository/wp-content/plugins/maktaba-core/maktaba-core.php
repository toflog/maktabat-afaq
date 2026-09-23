<?php
/**
 * Plugin Name: Maktaba Core
 * Description: منطق العمل الكامل لمشروع مكتبة الكتب الورقية (V1) — انظر PROJECT_BASELINE.
 * Version: 1.0.0
 * Text Domain: maktaba-core
 *
 * قاعدة معمارية إلزامية: هذا الملف نقطة دخول (Bootstrap) فقط —
 * لا يحتوي أي منطق عمل بنفسه (architecture/boundaries.md).
 */

namespace Maktaba\Core;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

define( "MKT_PLUGIN_DIR", plugin_dir_path( __FILE__ ) );
define( "MKT_PLUGIN_URL", plugin_dir_url( __FILE__ ) );

/**
 * Autoloader بسيط (بلا Composer — التزاماً بمبدأ Zero-Cost/Zero-Complexity).
 * يحوّل Maktaba\Core\Domain\Book\BookRepository
 * إلى includes/Domain/Book/BookRepository.php
 */
spl_autoload_register( function ( $class ) {
    $prefix = "Maktaba\\Core\\";

    if ( strpos( $class, $prefix ) !== 0 ) {
        return;
    }

    $relative_path = str_replace( "\\", "/", substr( $class, strlen( $prefix ) ) );
    $file          = MKT_PLUGIN_DIR . "includes/" . $relative_path . ".php";

    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

register_activation_hook( __FILE__, array( "Maktaba\\Core\\Integrations\\Installer", "activate" ) );

/**
 * نقطة التهيئة الرئيسية بعد تحميل كل الإضافات — يتحقق من WooCommerce أولاً
 * (تبعية إلزامية معلنة في architecture/dependency_rules.md).
 */
add_action( "plugins_loaded", function () {
    if ( ! class_exists( "WooCommerce" ) ) {
        add_action( "admin_notices", function () {
            echo "<div class=\"notice notice-error\"><p>";
            echo "Maktaba Core يتطلب تفعيل WooCommerce أولاً.";
            echo "</p></div>";
        } );
        return;
    }

    \Maktaba\Core\Integrations\WooCommerce\ProductAsBookBridge::register();
    \Maktaba\Core\Integrations\WooCommerce\OrderMetaBridge::register();
    \Maktaba\Core\Integrations\WooCommerce\PriceDisplayBridge::register();
    \Maktaba\Core\Frontend\OrderCTA\WhatsAppOrderButton::register();
    \Maktaba\Core\Frontend\Search\SearchExtender::register();
    \Maktaba\Core\Frontend\SimilarBooks\SimilarBooksFinder::register();
    \Maktaba\Core\Frontend\Pricing\CurrencyToggle::register();

    \Maktaba\Core\Admin\Dashboard\DashboardWidget::register();
    \Maktaba\Core\Admin\Menu\MenuRegistrar::register();
    \Maktaba\Core\Admin\Ajax\AjaxHandlers::register();
    \Maktaba\Core\Admin\Purchase\PurchaseAdminPage::register();
    \Maktaba\Core\Admin\Settings\SettingsAdminPage::register();
    \Maktaba\Core\Admin\MetaBoxes\SupplierMetaBox::register();
    \Maktaba\Core\Admin\MetaBoxes\InventoryMetaBox::register();
    \Maktaba\Core\Admin\MetaBoxes\OrderStatusMetaBox::register();
    \Maktaba\Core\Admin\MetaBoxes\SocialContentMetaBox::register();
    \Maktaba\Core\Admin\MetaBoxes\BookDetailsMetaBox::register();

    add_action( "init", array( "Maktaba\\Core\\Integrations\\Taxonomies", "register" ) );
    add_action( "init", array( "Maktaba\\Core\\Integrations\\PostTypes", "register" ) );
} );
