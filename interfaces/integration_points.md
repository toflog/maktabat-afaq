# Integration Points بين الأدوار الثلاثة

## Backend/Plugin ⇄ Frontend/Theme
| نقطة الربط | من (Plugin) | إلى (Theme) |
|---|---|---|
| زر الطلب | `Frontend/OrderCTA/WhatsAppOrderButton.php` | Shortcode: `[mkt_order_button]` يُستدعى داخل `single-product.php` |
| كتب مشابهة | `Frontend/SimilarBooks/` | Shortcode: `[mkt_similar_books]` |
| نتائج البحث الموسّعة | `Domain` عبر `pre_get_posts` | لا شيء يتغيّر في Theme — شفاف تماماً |

**قاعدة:** كل تواصل من Theme نحو Plugin يكون عبر **Shortcodes أو
Template Tags موثقة هنا فقط** — لا استدعاء دوال داخلية للـ Plugin
مباشرة من ملفات Theme.

## Backend/Plugin ⇄ Content/Social
| نقطة الربط | الوصف |
|---|---|
| صندوق "محتوى جاهز للنشر" | يظهر في شاشة تعديل الكتاب (Admin)، ناتج
  عن `BookSocialContentBuilder.php` — يُنسخ يدوياً من فريق المحتوى |

## Backend/Plugin ⇄ WooCommerce (خارجي لكن حرج)
موثق بالكامل في `contracts/integration_contracts/woocommerce_integration.md`
