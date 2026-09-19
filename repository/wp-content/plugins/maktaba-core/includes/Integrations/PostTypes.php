<?php
/**
 * Integrations\PostTypes
 *
 * تسجيل Custom Post Types الخاصة بالمشروع (Supplier, Purchase).
 * انظر: contracts/data_contracts/supplier.md, purchase.md
 *
 * قاعدة: تسجيل بنية بيانات WordPress (register_post_type) هو استخدام
 * لـ WordPress Core APIs — مسموح في Integrations (architecture/dependency_rules.md)
 * رغم أنه لا يخص WooCommerce تحديداً؛ لذلك وُضع كملف منفصل عن مجلد
 * Integrations/WooCommerce/ (الذي يبقى محصوراً بلمس WooCommerce فقط).
 */

namespace Maktaba\Core\Integrations;

use Maktaba\Core\Domain\Supplier\SupplierRepository;
use Maktaba\Core\Domain\Purchase\PurchaseRepository;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class PostTypes {

    public static function register() {
        register_post_type( SupplierRepository::POST_TYPE, array(
            "label"        => "الموردون",
            "public"       => false,
            "show_ui"      => true,
            "show_in_menu" => "maktaba-core",
            "supports"     => array( "title" ),
            "capability_type" => "post",
            "map_meta_cap"    => true,
        ) );

        register_post_type( PurchaseRepository::POST_TYPE, array(
            "label"        => "المشتريات",
            "public"       => false,
            "show_ui"      => true,
            "show_in_menu" => "maktaba-core",
            "supports"     => array( "title" ),
            "capability_type" => "post",
            "map_meta_cap"    => true,
        ) );
    }
}
