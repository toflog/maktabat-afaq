<?php
/**
 * Admin\Dashboard\DashboardWidget
 *
 * انظر: Section 28 من مخطط المشروع (مؤشرات Dashboard الأساسية)
 *
 * قاعدة الطبقة: يستدعي Integrations/WooCommerce فقط لجلب بيانات
 * WooCommerce — لا يستدعي wc_get_product/wc_get_orders مباشرة هنا
 * (architecture/dependency_rules.md).
 */

namespace Maktaba\Core\Admin\Dashboard;

use Maktaba\Core\Integrations\WooCommerce\ProductAsBookBridge;
use Maktaba\Core\Integrations\WooCommerce\OrderMetaBridge;
use Maktaba\Core\Shared\Constants;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class DashboardWidget {

    public static function register() {
        add_action( "wp_dashboard_setup", array( __CLASS__, "addWidget" ) );
    }

    public static function addWidget() {
        wp_add_dashboard_widget(
            "mkt_dashboard_widget",
            "لوحة المكتبة — نظرة سريعة",
            array( __CLASS__, "render" )
        );
    }

    public static function render() {
        $stock  = ProductAsBookBridge::getStockCounts();
        $new    = OrderMetaBridge::countByLibraryStatus( Constants::ORDER_STATUS_NEW );
        $today  = OrderMetaBridge::countCreatedToday();

        printf(
            "<ul style=\"line-height:1.9\">
                <li>📚 إجمالي الكتب: <strong>%d</strong></li>
                <li>✅ متوفرة: <strong>%d</strong></li>
                <li>⛔ نافدة: <strong>%d</strong></li>
                <li>⚠ منخفضة المخزون: <strong>%d</strong></li>
                <li>🆕 طلبات جديدة بانتظار التواصل: <strong>%d</strong></li>
                <li>📅 طلبات اليوم: <strong>%d</strong></li>
            </ul>",
            (int) $stock["total"],
            (int) $stock["in_stock"],
            (int) $stock["out_of_stock"],
            (int) $stock["low_stock"],
            (int) $new,
            (int) $today
        );
    }
}
