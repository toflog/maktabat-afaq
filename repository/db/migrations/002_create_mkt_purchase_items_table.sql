-- Migration: 002_create_mkt_purchase_items_table
-- انظر: contracts/data_contracts/purchase.md

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mkt_purchase_items` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_id` BIGINT UNSIGNED NOT NULL,
  `book_id`     BIGINT UNSIGNED NOT NULL,
  `quantity`    INT NOT NULL,
  `unit_cost`   DECIMAL(10,2) NOT NULL,
  `total_cost`  DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_purchase_id` (`purchase_id`),
  KEY `idx_book_id` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
