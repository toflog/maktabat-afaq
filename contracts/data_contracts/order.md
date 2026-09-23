# Data Contract: Order / Order Item

## القرار المعماري
يُستخدم **WooCommerce Order** (`shop_order` / HPOS) كأساس كامل —
لا يُبنى نظام طلبات موازٍ. الحقول الإضافية (`source`, حالات المكتبة
المخصصة) تُضاف كـ Order Meta فقط.

## الحقول الإضافية على WooCommerce Order
```
_mkt_source          → WEBSITE | FACEBOOK | INSTAGRAM | WHATSAPP | DIRECT | OTHER
_mkt_library_status  → NEW | CONTACTED | CONFIRMED | PREPARING | SHIPPED
                        | DELIVERED | CANCELLED | RETURNED | FAILED
_mkt_internal_notes  → ملاحظات داخلية (مختلفة عن customer_note الأصلي في WC)
```

## قاعدة العلاقة بين حالتي المكتبة وحالة WooCommerce
`_mkt_library_status` هو حالة تشغيلية تفصيلية خاصة بالمكتبة، بينما
`post_status` في WooCommerce يبقى مبسطاً (pending/processing/completed/
cancelled). التطابق (Mapping) موثّق في:
`contracts/domain_contracts/order_status_transitions.md`
— يُمنع أي كود يقارن مباشرة بين الاثنين دون المرور بـ `OrderStatusMap.php`.

## Order Item
Order Item الأصلي في WooCommerce (`book_id` = Product ID) يُستخدم كما هو
دون أي حقل إضافي — البيانات (quantity, unit_price, total_price) native.
