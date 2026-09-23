# Data Contract: Category

## القرار المعماري
تُستخدم Taxonomy الأصلية في WooCommerce: `product_cat` (هرمية بطبيعتها،
تدعم `parent_id` أصلاً) — **لا تُبنى Taxonomy مخصصة جديدة للتصنيفات**.

## سبب القرار
- تفادي ازدواجية مفهوم "تصنيف الكتاب" مع "تصنيف منتج WooCommerce".
- توفر لوحة إدارة جاهزة للتفرّع (parent/child) دون كود إضافي — يحقق
  متطلب "غير مثبتة في الكود ويمكن تعديلها من لوحة الإدارة" مباشرة.

## الحقول
```
id, name, slug, parent_id, description   (كلها native في product_cat)
image                                     (native WooCommerce category thumbnail)
```
