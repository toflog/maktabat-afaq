<?php
/**
 * Integrations\Taxonomies
 *
 * تسجيل Taxonomies مخصصة (Author, Publisher) على Post Type: product
 * انظر: contracts/data_contracts/author.md, publisher.md
 * انظر: contracts/data_contracts/category.md (يوضح لماذا Category تُستثنى
 * من هذا الملف — تُستخدم product_cat الأصلية في WooCommerce مباشرة).
 */

namespace Maktaba\Core\Integrations;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class Taxonomies {

    const AUTHOR    = "mkt_author";
    const PUBLISHER = "mkt_publisher";

    public static function register() {
        register_taxonomy( self::AUTHOR, array( "product" ), array(
            "label"        => "المؤلفون",
            "hierarchical" => false,
            "public"       => true,
            "show_ui"      => true,
            "show_admin_column" => true,
            "rewrite"      => array( "slug" => "author" ),
        ) );

        register_taxonomy( self::PUBLISHER, array( "product" ), array(
            "label"        => "دور النشر",
            "hierarchical" => false,
            "public"       => true,
            "show_ui"      => true,
            "show_admin_column" => true,
            "rewrite"      => array( "slug" => "publisher" ),
        ) );
    }
}
