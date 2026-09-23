-- Migration: 003_create_mkt_customers_table
-- انظر: contracts/data_contracts/customer.md
-- جدول تجميعي فقط — ليس مصدر الحقيقة (ذلك WooCommerce Order billing).

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mkt_customers` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone`      VARCHAR(32) NOT NULL,
  `name`       VARCHAR(191) NULL,
  `city`       VARCHAR(191) NULL,
  `area`       VARCHAR(191) NULL,
  `address`    TEXT NULL,
  `notes`      TEXT NULL,
  `source`     ENUM("WEBSITE","FACEBOOK","INSTAGRAM","WHATSAPP","DIRECT","OTHER") NOT NULL DEFAULT "OTHER",
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_phone` (`phone`),
  KEY `idx_source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
