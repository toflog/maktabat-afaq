<?php
/**
 * Integrations\Installer
 *
 * ينفّذ عند تفعيل الـ Plugin فقط — مسؤول عن إنشاء الجداول المخصصة.
 * يستخدم WordPress hooks (register_activation_hook) — مسموح هنا فقط
 * (architecture/dependency_rules.md: WordPress Core APIs في Integrations).
 */

namespace Maktaba\Core\Integrations;

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

final class Installer {

    /**
     * عند التفعيل: إنشاء الجداول المخصصة، ثم تسجيل CPT/Taxonomies فوراً
     * (بدل انتظار "init" التالي) حتى يكون flush_rewrite_rules() فعّالاً
     * من أول تفعيل — يسدّ الفجوة رقم 6 من تقرير الحالة (روابط 404 حتى
     * حفظ إعدادات الروابط الدائمة يدوياً).
     */
    public static function activate() {
        self::createInventoryMovementsTable();
        self::createPurchaseItemsTable();
        self::createCustomersTable();

        // تسجيل مسبق ضروري فقط لبناء قواعد الروابط الصحيحة أثناء التفعيل.
        Taxonomies::register();
        PostTypes::register();

        flush_rewrite_rules();
    }

    private static function createInventoryMovementsTable() {
        global $wpdb;

        $table_name      = $wpdb->prefix . "mkt_inventory_movements";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            book_id BIGINT UNSIGNED NOT NULL,
            type ENUM(\"PURCHASE\",\"SALE\",\"RETURN\",\"DAMAGED\",\"ADJUSTMENT\") NOT NULL,
            quantity INT NOT NULL,
            reference VARCHAR(191) NULL,
            note TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_book_id (book_id),
            KEY idx_type (type),
            KEY idx_created_at (created_at)
        ) {$charset_collate};";

        require_once ABSPATH . "wp-admin/includes/upgrade.php";
        dbDelta( $sql );
    }

    private static function createPurchaseItemsTable() {
        global $wpdb;

        $table_name      = $wpdb->prefix . "mkt_purchase_items";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            purchase_id BIGINT UNSIGNED NOT NULL,
            book_id BIGINT UNSIGNED NOT NULL,
            quantity INT NOT NULL,
            unit_cost DECIMAL(10,2) NOT NULL,
            total_cost DECIMAL(10,2) NOT NULL,
            PRIMARY KEY (id),
            KEY idx_purchase_id (purchase_id),
            KEY idx_book_id (book_id)
        ) {$charset_collate};";

        require_once ABSPATH . "wp-admin/includes/upgrade.php";
        dbDelta( $sql );
    }

    private static function createCustomersTable() {
        global $wpdb;

        $table_name      = $wpdb->prefix . "mkt_customers";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            phone VARCHAR(32) NOT NULL,
            name VARCHAR(191) NULL,
            city VARCHAR(191) NULL,
            area VARCHAR(191) NULL,
            address TEXT NULL,
            notes TEXT NULL,
            source ENUM(\"WEBSITE\",\"FACEBOOK\",\"INSTAGRAM\",\"WHATSAPP\",\"DIRECT\",\"OTHER\") NOT NULL DEFAULT \"OTHER\",
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_phone (phone),
            KEY idx_source (source)
        ) {$charset_collate};";

        require_once ABSPATH . "wp-admin/includes/upgrade.php";
        dbDelta( $sql );
    }
}
