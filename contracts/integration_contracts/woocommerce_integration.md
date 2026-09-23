# Integration Contract: WooCommerce

## المبدأ
كل لمس مباشر لـ WooCommerce hooks/APIs محصور حصراً في:
`Integrations/WooCommerce/*.php`
لا يُسمح باستدعاء `wc_get_product()`, `WC_Order`, أو أي WooCommerce hook
من داخل `Domain/`, `Admin/`, أو `Frontend/` مباشرة.

## نقاط الربط المعتمدة
| الحدث | Hook | المُعالِج |
|---|---|---|
| إنشاء/تعديل منتج | `save_post_product` | `ProductAsBookBridge::onSave()` |
| تغيّر حالة طلب WooCommerce | `woocommerce_order_status_changed` | `OrderMetaBridge::syncLibraryStatus()` |
| عرض صفحة كتاب | `woocommerce_before_single_product_summary` | `Frontend/OrderCTA` (عبر Bridge) |

## قاعدة الاتجاه الواحد
`Integrations/WooCommerce/` يقرأ من WooCommerce ويكتب إلى `Domain/`،
والعكس عند الحاجة — لكن `Domain/` نفسه لا يعرف أن WooCommerce موجود
(Dependency Inversion) — يستقبل بيانات مجردة (Book ID, quantities)
لا كائنات WooCommerce.
