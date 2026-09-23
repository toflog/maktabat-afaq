# Ownership: Frontend / Theme (maktaba-theme)

## يملك
```
wp-content/themes/maktaba-theme/**
```

## المسؤوليات
- كل ما هو عرض بصري: Templates, CSS, RTL, Mobile-first.
- استدعاء دوال جاهزة من الـ Plugin (لا يكتب منطق عمل بنفسه).
- تطبيق الهوية التجارية (Section 2 من مخطط المشروع).

## ممنوع عليه
- أي استعلام SQL مباشر.
- أي قراءة مباشرة لحقول داخلية (`purchase_price`, `shipping_cost`,
  `profit`) — ممنوعة تماماً من طبقة العرض (انظر pricing_and_profit.md).
- تعديل أي شيء داخل `wp-content/plugins/maktaba-core/**`.
