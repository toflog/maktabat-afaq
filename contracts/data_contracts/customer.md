# Data Contract: Customer

## القرار المعماري
لا يوجد نظام حسابات عملاء (No forced accounts). العميل يُعرَّف بـ
**رقم الهاتف كمفتاح طبيعي**، ويُخزَّن كـ Billing Data على WooCommerce
Order (Guest Checkout) — مع جدول تجميعي خفيف `mkt_customers` لأغراض
التقارير فقط (تحليل مصدر العميل، عدد الطلبات لكل رقم هاتف).

## الحقول
### على WooCommerce Order (billing meta - native)
```
name, phone, city (billing_city), area (billing_address_2), address, notes
```
### جدول تجميعي: mkt_customers
```sql
CREATE TABLE mkt_customers (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  phone         VARCHAR(32) NOT NULL UNIQUE,   -- المفتاح الطبيعي
  name          VARCHAR(191),
  city          VARCHAR(191),
  area          VARCHAR(191),
  address       TEXT,
  notes         TEXT,
  source        ENUM("WEBSITE","FACEBOOK","INSTAGRAM","WHATSAPP","DIRECT","OTHER"),
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## قاعدة
هذا الجدول يُحدَّث تلقائياً (upsert بالهاتف) عند كل Order جديد —
لا يُعبَّأ يدوياً أبداً، ولا يُعتبر "مصدر حقيقة" بديلاً عن WooCommerce Order.
