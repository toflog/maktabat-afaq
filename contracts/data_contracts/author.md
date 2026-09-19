# Data Contract: Author

## القرار المعماري
Taxonomy مخصصة `mkt_author` (وليس Custom Post Type) لأن المؤلف كيان بسيط
(اسم + سيرة + صورة) ومرتبط أساساً بتصنيف الكتب حسب المؤلف.

## الحقول (Term Meta)
```
name        (Taxonomy term name — native)
slug        (native)
biography   (Term Meta: mkt_author_biography)
photo       (Term Meta: mkt_author_photo — attachment ID)
is_active   (Term Meta: mkt_author_is_active — افتراضي true)
```

## قاعدة
صفحة المؤلف = صفحة Taxonomy Archive قياسية
(`taxonomy-mkt_author.php` في Theme) — لا تُبنى كصفحة مخصصة منفصلة.
