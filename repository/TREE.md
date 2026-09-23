# شجرة المشروع الكاملة — Repository Baseline

هذه هي الشجرة الملزمة. أي ملف/مجلد غير موجود هنا يعتبر انحرافاً معمارياً
عن الـ Baseline إلى أن يُعتمد رسمياً ويُحدَّث هذا الملف.

```
maktaba-project/
│
├── wp-content/
│   │
│   ├── themes/
│   │   └── maktaba-theme/                 (Child Theme فوق Storefront — عرض فقط)
│   │       ├── style.css                  (Child Theme header + CSS، Mobile First + RTL)
│   │       ├── functions.php              (تحميل فقط + ربط Hooks بـ Shortcodes الجاهزة)
│   │       ├── front-page.php             (جذر إلزامي — WordPress template hierarchy)
│   │       ├── taxonomy-mkt_author.php    (جذر إلزامي — صفحة مؤلف)
│   │       ├── taxonomy-mkt_publisher.php (جذر إلزامي — صفحة ناشر)
│   │       ├── templates/
│   │       │   ├── front-page.php         (المحتوى الفعلي للرئيسية)
│   │       │   └── taxonomy-term-books.php (قالب مشترك: مؤلف/ناشر)
│   │       └── assets/
│   │           ├── css/
│   │           └── js/
│   │
│   └── plugins/
│       └── maktaba-core/                  (Custom Plugin — كل منطق العمل هنا)
│           ├── maktaba-core.php           (نقطة الدخول/Bootstrap فقط)
│           │
│           ├── includes/
│           │   │
│           │   ├── Domain/                (منطق العمل الصافي)
│           │   │   ├── Book/
│           │   │   ├── Inventory/
│           │   │   ├── Supplier/
│           │   │   ├── Purchase/
│           │   │   ├── Order/
│           │   │   ├── Customer/
│           │   │   └── Pricing/
│           │   │
│           │   ├── Admin/                 (لوحة الإدارة فقط)
│           │   │   ├── Ajax/              (نقاط AJAX الثلاث المعتمدة في العقد)
│           │   │   ├── Dashboard/
│           │   │   ├── Menu/
│           │   │   ├── MetaBoxes/         (مورد، مخزون، حالة طلب، محتوى سوشيال)
│           │   │   ├── Purchase/          (صفحة تسجيل شراء)
│           │   │   ├── Reports/
│           │   │   └── Settings/
│           │   │
│           │   ├── Frontend/              (واجهة عامة تستدعي Domain فقط)
│           │   │   ├── OrderCTA/
│           │   │   ├── Search/
│           │   │   └── SimilarBooks/
│           │   │
│           │   ├── Integrations/          (كل اتصال خارجي محصور هنا)
│           │   │   ├── WooCommerce/
│           │   │   ├── SocialContent/
│           │   │   ├── PostTypes.php      (تسجيل mkt_supplier, mkt_purchase)
│           │   │   ├── Taxonomies.php     (تسجيل mkt_author, mkt_publisher)
│           │   │   └── Installer.php      (إنشاء جداول DB عند التفعيل)
│           │   │
│           │   └── Shared/
│           │
│           └── assets/
│
├── db/
│   └── migrations/
│       ├── 001_create_mkt_inventory_movements_table.sql
│       └── 002_create_mkt_purchase_items_table.sql
│
└── docs/
```

## ملاحظة إلزامية
- `Domain/` هو الطبقة الوحيدة المسموح لها بحمل قواعد العمل (Business Rules).
- `Integrations/` هو المكان الوحيد المسموح فيه لمس WooCommerce classes/hooks مباشرة.
- `Admin/` و `Frontend/` يستدعيان `Domain/` و `Integrations/` فقط — لا يكتبان منطق عمل بأنفسهما، ولا يستدعيان `wc_get_product`/`WC_Order` بأنفسهما.

## تحديث معماري (Change Log)
> **قرار مُراجَع:** الخطة الأولى للـ Baseline افترضت استنساخ قوالب
> WooCommerce كاملة (`single-product.php`, `archive-product.php`,
> `taxonomy-product_cat.php`) داخل الـ Theme. بعد التنفيذ الفعلي تَقرَّر
> ما يلي (أكثر اتساقاً مع Zero-Cost First):
> - **صفحة الكتاب**: لا يُستنسخ `single-product.php` — يُكتفى بربط
>   `[mkt_order_button]` و`[mkt_similar_books]` عبر
>   `woocommerce_single_product_summary` / `woocommerce_after_single_product_summary`
>   في `functions.php`. هذا يحافظ على توافق القالب مع ترقيات WooCommerce
>   المستقبلية دون صيانة نسخة مكررة من القالب الأصلي.
> - **صفحة كل الكتب**: لا يُستنسخ `archive-product.php` — فلاتر
>   التصنيف/السعر تُفعَّل عبر ودجت WooCommerce الجاهزة (Layered Nav +
>   Price Filter) من `Appearance > Widgets` بلا كود إضافي.
> - **صفحة تصنيف WooCommerce الأصلي** (`product_cat`): تُستخدم قوالب
>   WooCommerce/Storefront الافتراضية كما هي — لا حاجة لملف مخصص.
> - **صفحتا المؤلف/الناشر**: هاتان فعلاً بحاجة لملفين مخصصين لأن
>   WooCommerce لا يعرف Taxonomies المخصصة — نُفِّذتا كملفي جذر رفيعين
>   (`taxonomy-mkt_author.php`, `taxonomy-mkt_publisher.php`) يستدعيان
>   قالباً مشتركاً واحداً في `templates/taxonomy-term-books.php` لتفادي
>   التكرار.
