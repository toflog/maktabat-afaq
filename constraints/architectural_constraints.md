# Architectural Constraints

1. لا منطق عمل داخل `wp-content/themes/**` — راجع `architecture/boundaries.md`.
2. لا استدعاء مباشر لـ WooCommerce classes/hooks خارج
   `Integrations/WooCommerce/**`.
3. لا تعديل مباشر لـ `stock_quantity` خارج `StockCalculator.php`.
4. لا تكرار تعريف Enum/قائمة ثابتة (حالات الطلب، أنواع الحركة) في أكثر
   من ملف — مصدر واحد فقط لكل قائمة (انظر `variables/shared_identifiers.md`).
5. أي جدول DB جديد أو Post Meta جديد يجب أن يُضاف أولاً إلى
   `contracts/data_contracts/` قبل ظهوره في الكود — لا "حقول مفاجئة".
6. أي تغيير في هذا الملف أو في `contracts/` يعتبر تغييراً معمارياً
   يستوجب رفع رقم إصدار `BASELINE_MANIFEST.yaml`.
