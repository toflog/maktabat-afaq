<?php
/**
 * Admin\Reports\ReportsAdminPage
 *
 * انظر: Section 29 من مخطط المشروع (تقارير المبيعات/المخزون/الأرباح)
 * صفحة بسيطة تجمع أهم المؤشرات — عبر Integrations فقط، بلا لمس WooCommerce مباشرة.
 */

namespace Maktaba\Core\Admin\Reports;

use Maktaba\Core\Integrations\WooCommerce\ProductAsBookBridge;
use Maktaba\Core\Integrations\WooCommerce\OrderMetaBridge;
use Maktaba\Core\Shared\Constants;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class ReportsAdminPage {

    public static function render() {
        if ( ! current_user_can( "manage_options" ) ) {
            return;
        }

        $stock = ProductAsBookBridge::getStockCounts();

        echo "<div class=\"wrap\"><h1>التقارير</h1>";

        echo "<h2>المخزون</h2><table class=\"widefat\"><tbody>";
        printf( "<tr><td>إجمالي الكتب</td><td><strong>%d</strong></td></tr>", (int) $stock["total"] );
        printf( "<tr><td>متوفرة</td><td><strong>%d</strong></td></tr>", (int) $stock["in_stock"] );
        printf( "<tr><td>نافدة</td><td><strong>%d</strong></td></tr>", (int) $stock["out_of_stock"] );
        printf( "<tr><td>منخفضة المخزون</td><td><strong>%d</strong></td></tr>", (int) $stock["low_stock"] );
        echo "</tbody></table>";

        echo "<h2>الطلبات حسب الحالة</h2><table class=\"widefat\"><tbody>";
        foreach ( Constants::allOrderStatuses() as $status ) {
            printf(
                "<tr><td>%s</td><td><strong>%d</strong></td></tr>",
                esc_html( $status ),
                (int) OrderMetaBridge::countByLibraryStatus( $status )
            );
        }
        echo "</tbody></table>";

        echo "<p class=\"description\">لعرض ربح طلب محدد استخدم شاشة الطلب في WooCommerce (يظهر تلقائياً في صندوق \"حالة المكتبة\").</p>";
        echo "</div>";
    }
}
