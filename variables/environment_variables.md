# Environment Variables / Config Constants

> لا توجد بوابة دفع أو مفاتيح API خارجية حساسة في V1 (Zero external
> paid services) — القائمة التالية هي ثوابت تهيئة (wp-config.php أو
> Plugin Settings) وليست أسرار API بالمعنى التقليدي.

| المفتاح | الموقع | الغرض |
|---|---|---|
| `MKT_ENV` | wp-config.php | `production` \| `staging` \| `local` |
| `mkt_whatsapp_number` | Settings (DB option) | رقم واتساب الاستقبال — مصدر وحيد |
| `mkt_free_shipping_threshold` | Settings (DB option) | حد الشحن المجاني (Section 3.6 من المخطط) |
| `mkt_social_default_hashtags` | Settings (DB option) | هاشتاغات افتراضية للمحتوى الاجتماعي |
| `WP_DEBUG` | wp-config.php | `false` في production إلزامياً |

## قاعدة
أي إضافة مستقبلية فعلية (مثل مفتاح بوابة دفع في V2) تُضاف هنا أولاً
كتعديل موثق على الـ Baseline، قبل ظهورها في الكود.
