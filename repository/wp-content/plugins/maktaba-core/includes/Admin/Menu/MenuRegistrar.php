<?php
/**
 * Admin\Menu\MenuRegistrar
 *
 * قائمة إدارة رئيسية واحدة تجمع الموردين والمشتريات والتقارير —
 * (Section 27 من مخطط المشروع: لوحة إدارة مركزية).
 */

namespace Maktaba\Core\Admin\Menu;

use Maktaba\Core\Admin\Purchase\PurchaseAdminPage;
use Maktaba\Core\Admin\Reports\ReportsAdminPage;
use Maktaba\Core\Admin\Settings\SettingsAdminPage;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class MenuRegistrar {

    const SLUG = "maktaba-core";

    public static function register() {
        add_action( "admin_menu", array( __CLASS__, "addMenus" ) );
    }

    public static function addMenus() {
        add_menu_page(
            "المكتبة",
            "المكتبة",
            "manage_options",
            self::SLUG,
            array( "Maktaba\\Core\\Admin\\Dashboard\\DashboardWidget", "render" ),
            "dashicons-book-alt",
            26
        );

        add_submenu_page(
            self::SLUG,
            "تسجيل شراء جديد",
            "المشتريات",
            "manage_options",
            "mkt-purchases",
            array( PurchaseAdminPage::class, "render" )
        );

        add_submenu_page(
            self::SLUG,
            "التقارير",
            "التقارير",
            "manage_options",
            "mkt-reports",
            array( ReportsAdminPage::class, "render" )
        );

        add_submenu_page(
            self::SLUG,
            "إعدادات المكتبة",
            "الإعدادات",
            "manage_options",
            "mkt-settings",
            array( SettingsAdminPage::class, "render" )
        );
    }
}
