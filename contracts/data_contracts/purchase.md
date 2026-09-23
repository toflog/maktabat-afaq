# Data Contract: Purchase / Purchase Item

## القرار المعماري
Custom Post Type: `mkt_purchase` للـ Purchase (رأس العملية)،
وجدول DB مخصص `mkt_purchase_items` للبنود (لأن البنود متعددة لكل عملية
ومطلوب استعلامات تجميعية عليها لاحقاً في التقارير).

## Purchase (Post Meta على mkt_purchase)
```
supplier_id       → Post Meta: _mkt_supplier_id (يشير إلى mkt_supplier)
date              → post_date (native)
reference_number  → Post Meta: _mkt_reference_number
total_cost        → Post Meta: _mkt_total_cost (محسوب من Purchase Items، read-only)
notes             → Post Meta: _mkt_notes
```

## Purchase Item (جدول mkt_purchase_items)
```
id            BIGINT PK
purchase_id   BIGINT  → mkt_purchase.ID
book_id       BIGINT  → WooCommerce product ID
quantity      INT
unit_cost     DECIMAL(10,2)
total_cost    DECIMAL(10,2)  -- = quantity * unit_cost, محسوب لا يُدخل يدوياً
```

## قاعدة إلزامية
تسجيل Purchase مكتمل (رأس + بنود) هو المُطلق الوحيد لإنشاء
Inventory Movement من نوع `PURCHASE` — لا يوجد مسار آخر لزيادة المخزون
بسبب "شراء" في النظام.
