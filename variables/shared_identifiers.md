# Shared Identifiers

## معرّفات ثابتة يُشار إليها من أكثر من طبقة (يجب أن تبقى متطابقة الاسم دائماً)

### حالات الطلب (Order Status enum) — يُشار إليه من Domain و Admin
```
NEW, CONTACTED, CONFIRMED, PREPARING, SHIPPED, DELIVERED,
CANCELLED, RETURNED, FAILED
```
مصدر التعريف الوحيد: `Domain/Order/OrderStatusMap.php`

### أنواع حركة المخزون (Inventory Movement Type enum)
```
PURCHASE, SALE, RETURN, DAMAGED, ADJUSTMENT
```
مصدر التعريف الوحيد: `Domain/Inventory/InventoryMovement.php`

### مصدر العميل/الطلب (Source enum)
```
WEBSITE, FACEBOOK, INSTAGRAM, WHATSAPP, DIRECT, OTHER
```
مصدر التعريف الوحيد: `Shared/Constants.php`

### نوع المورد (Supplier Type enum)
```
direct_publisher, authorized_distributor, secondary_market
```
مصدر التعريف الوحيد: `Shared/Constants.php`

## قاعدة
هذه القوائم تُعرَّف **مرة واحدة فقط** في الملف المذكور، وكل مكان آخر
يستوردها (`use`) — يُمنع تكرار كتابة القائمة كنص حرفي (string literal)
في أكثر من ملف.
