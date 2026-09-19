# Data Contract: Book

## القرار المعماري
Book لا يُبنى ككيان منفصل بالكامل، بل **يُبنى فوق WooCommerce Product**
(post type: `product`) لتفادي إعادة بناء ما توفره WooCommerce أصلاً
(الأسعار، المخزون الأساسي، الصور، الروابط) — مع إضافة الحقول المخصصة
كـ Post Meta عبر `Integrations/WooCommerce/ProductAsBookBridge.php`.

## الحقول
| الحقل | المصدر | ملاحظة |
|---|---|---|
| id, title, slug | WooCommerce Product (native) | |
| author_id | Taxonomy: `mkt_author` | وليس حقل نصي حر |
| publisher_id | Taxonomy: `mkt_publisher` | |
| category_id | Taxonomy: `product_cat` (هرمية) | يدعم التفرع مباشرة |
| description / short_description | WooCommerce Product (native) | |
| isbn | Post Meta: `_mkt_isbn` | |
| language | Post Meta: `_mkt_language` | افتراضي: ar |
| page_count | Post Meta: `_mkt_page_count` | |
| publication_year | Post Meta: `_mkt_publication_year` | |
| cover_image / gallery | WooCommerce Product (native) | |
| purchase_price | Post Meta: `_mkt_purchase_price` | **داخلي — ممنوع أي استعلام Frontend لهذا الحقل** |
| shipping_cost | Post Meta: `_mkt_shipping_cost` | **داخلي** |
| selling_price | WooCommerce `_regular_price` (native) | هذا فقط الظاهر للعميل |
| stock_quantity / stock_status | WooCommerce Product (native) | لا يُعدَّل يدوياً مباشرة — فقط عبر Inventory Movement |
| condition | Post Meta: `_mkt_condition` | قيم: `new` \| `used` — **مخزّن غير مفعّل بالواجهة في V1** |
| featured / new_arrival | WooCommerce native / Post Meta: `_mkt_new_arrival` | |
| is_active | WooCommerce `post_status` (native) | |

## قيود إلزامية
- أي كود يقرأ `purchase_price` أو `shipping_cost` من سياق Frontend/Templates
  يُعتبر خرقاً أمنياً للعقد (تسريب بيانات تكلفة داخلية للعميل).
- `stock_quantity` لا يُعدَّل مباشرة أبداً — فقط عبر
  `Domain/Inventory/StockCalculator.php` استناداً لسجل Inventory Movement.
