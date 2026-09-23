-- Migration: 001_create_mkt_inventory_movements_table
-- انظر: contracts/data_contracts/inventory_movement.md
-- ملاحظة: {$wpdb->prefix} يُستبدل فعلياً بادئة القاعدة الحقيقية (عادة wp_)
-- عند التنفيذ داخل Integrations/Installer.php — هذا الملف مرجع توثيقي.

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mkt_inventory_movements` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `book_id`    BIGINT UNSIGNED NOT NULL,
  `type`       ENUM("PURCHASE","SALE","RETURN","DAMAGED","ADJUSTMENT") NOT NULL,
  `quantity`   INT NOT NULL,
  `reference`  VARCHAR(191) NULL,
  `note`       TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_book_id` (`book_id`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
