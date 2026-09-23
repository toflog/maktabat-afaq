# Data Contract: Inventory Movement

## القرار المعماري
جدول DB مخصص `mkt_inventory_movements` (وليس Post Type) — لأنه سجل
دفتري (Ledger) عالي التكرار يحتاج استعلامات تجميعية سريعة (مجموع الحركات
لكل كتاب، فلترة حسب النوع/التاريخ) لا يناسبها أداء نظام Posts.

## البنية
```sql
CREATE TABLE mkt_inventory_movements (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_id       BIGINT UNSIGNED NOT NULL,      -- WooCommerce product ID
  type          ENUM("PURCHASE","SALE","RETURN","DAMAGED","ADJUSTMENT") NOT NULL,
  quantity      INT NOT NULL,                  -- موجب أو سالب حسب النوع
  reference     VARCHAR(191) NULL,             -- مرجع Purchase ID أو Order ID
  note          TEXT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_book_id (book_id),
  INDEX idx_type (type)
);
```

## قاعدة إلزامية صارمة
`stock_quantity` في WooCommerce Product هو **نتيجة محسوبة** من مجموع
حركات هذا الجدول لكل `book_id` — وليس مصدر الحقيقة (Source of Truth).
أي تعديل مباشر لـ `stock_quantity` من لوحة WooCommerce الافتراضية
بدون المرور عبر `StockCalculator.php` يعتبر خرقاً للعقد ويكسر تتبع السبب
(المطلب: "يمكن معرفة سبب تغيّر المخزون" من معايير قبول V1).
