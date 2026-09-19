# Domain Contract: Order Status Transitions

## الحالات المسموحة (Finite State Machine)
```
NEW ──► CONTACTED ──► CONFIRMED ──► PREPARING ──► SHIPPED ──► DELIVERED

من أي حالة قبل DELIVERED يمكن الانتقال إلى:
  CANCELLED
  RETURNED   (فقط بعد DELIVERED)
  FAILED     (فقط من CONTACTED أو CONFIRMED — فشل التواصل/التأكيد)
```

## قواعد إلزامية
1. لا يُسمح بالقفز فوق حالة (مثلاً NEW → PREPARING مباشرة) إلا عبر
   إجراء إداري صريح مسجَّل بسبب (note إلزامي عند القفز).
2. الانتقال إلى CONFIRMED هو المُطلق الوحيد لحجز/خصم المخزون
   (`Inventory Movement: SALE` بحالة محجوز مبدئياً).
3. الانتقال إلى CANCELLED **قبل** SHIPPED يُطلق تلقائياً
   `Inventory Movement: RETURN` لإعادة الكمية المحجوزة.
4. SLA: الانتقال من NEW إلى CONTACTED يجب أن يُسجَّل بفارق زمني
   (`contacted_at - created_at`) يُستخدم كمؤشر أداء "سرعة الرد".

## نقطة التنفيذ الوحيدة
كل منطق الانتقال محصور في:
`Domain/Order/OrderStatusMap.php` — أي كود آخر (Admin UI, WooCommerce
hook) يستدعي هذه الطبقة فقط ولا يعدّل `_mkt_library_status` مباشرة.
