# Domain Contract: Multi-Currency Pricing (USD + SYP)

## مصدر الحقيقة الوحيد
`official_price_egp` — السعر كما يظهر **حرفياً** على nahdetmisr.com
لهذا الكتاب تحديداً. لا يُشتق من أي مكتبة أو موزّع آخر مهما كان
موثوقاً، لأن السياسة التجارية للمشروع محدَّدة صراحة بأنها نسبة فوق
سعر الناشر نفسه، لا فوق سعر أي وسيط.

## معادلة التسعير
```
price_usd = (official_price_egp / egp_per_usd_rate) × 1.20
price_syp = price_usd × syp_per_usd_rate
```

- `1.20` (هامش 20%) **ثابت مبرمج** في `CurrencyConverter::MARKUP_PERCENT`
  — تغييره يستوجب تعديل كود + رفع إصدار الـ Baseline (`change_control`).
- `egp_per_usd_rate` و `syp_per_usd_rate` **إعدادات يدوية** في
  `Admin/Settings` — لا جلب آلي من أي API خارجي في V1 (انظر
  "لماذا لا يوجد جلب آلي؟" أدناه).

## أين تُحسَب ومتى
الحساب يحدث **مرة واحدة وقت حفظ الكتاب** (`BookDetailsMetaBox::save()`
→ `Admin/Pricing/PriceRecalculator::recalculateOne()`), وليس لحظياً
عند كل زيارة صفحة. النتيجة تُخزَّن كـ Post Meta
(`_mkt_display_price_usd`, `_mkt_display_price_syp`) ويقرأها كل من:
- `Integrations/WooCommerce/PriceDisplayBridge` (عرض السعر في الصفحة)
- `Frontend/OrderCTA/WhatsAppOrderButton` (رسالة واتساب)

هذا يعني: **تغيير سعر الصرف من الإعدادات لا يُحدِّث الكتب المنشورة
مسبقاً تلقائياً** — يجب تشغيل
`docker/wp-cli-scripts/recalculate-prices.sh` بعد أي تعديل على سعري
الصرف لإعادة حساب كل الكتب دفعة واحدة.

## قاعدة الطبقات (لماذا التصميم بهذا الشكل تحديداً)
- `Domain/Pricing/CurrencyConverter` رياضيات صرفة فقط — يستقبل أسعار
  الصرف كمُعاملات، لا يجلبها بنفسه (نفس نمط `ProfitCalculator`).
- حساب الأسعار الفعلي (الذي يحتاج قراءة `GeneralSettings`) يعيش في
  `Admin/Pricing/PriceRecalculator` — ليس في `Domain/` ولا
  `Integrations/`، لأن كليهما ممنوعان من معرفة `Admin/` حسب
  `architecture/dependency_rules.md`.
- `Frontend/` يقرأ فقط القيم الجاهزة المخزَّنة عبر `BookRepository`
  (طبقة `Domain` مسموح لـ `Frontend` قراءتها) — لا يحسب شيئاً ولا يعرف
  شيئاً عن سعر الصرف أو نسبة الهامش.

## لماذا لا يوجد جلب آلي لسعر الصرف من الإنترنت؟ (قرار مقصود)
1. الليرة السورية شديدة التقلب، ولا يوجد مصدر رسمي واحد "موثوق
   برمجياً" (API) للسعر الموازي الفعلي المستخدم في السوق.
2. ربط مسار حساس كالتسعير بمصدر خارجي غير مضمون الاستقرار (قد يتعطل،
   يتغيّر شكله، أو يُحجب) يخالف مبدأ Zero-Complexity المعتمد في
   المشروع.
3. البديل: صاحب المكتبة يحدِّث رقمين فقط يدوياً من شاشة الإعدادات
   عندما يرى تغيّراً فعلياً يستحق التحديث — تحكّم كامل، بلا مفاجآت.

## قاعدة عزل — ما الذي لا يظهر للزائر أبداً
`official_price_egp` نفسه **لا يُعرض للزائر أبداً** — هو مدخل داخلي
فقط لحساب السعرين النهائيين. عرضه للزائر يكشف هامش الربح ومصدر
التوريد الحصري، وهذا يخالف نفس مبدأ العزل المطبَّق على
`purchase_price`/`shipping_cost` في `pricing_and_profit.md`.

## اختبار بسيط لأي Pull Request مستقبلي
- عدّلت `CurrencyConverter`؟ تأكد أنه ما زال بلا أي استدعاء لدالة
  WordPress أو لـ `GeneralSettings`.
- أضفت مكاناً جديداً يعرض السعر؟ يجب أن يقرأ من `BookRepository`
  (القيم الجاهزة) لا أن يحسب بنفسه — وإلا ستتعدد مصادر الحقيقة.
