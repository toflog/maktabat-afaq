# Domain Contract: Inventory Rules

## المصدر الوحيد للحقيقة
`mkt_inventory_movements` (انظر contracts/data_contracts/inventory_movement.md)

## معادلة المخزون
```
stock_quantity(book) = SUM(quantity) FROM mkt_inventory_movements
                        WHERE book_id = book AND type IN (...)
```
- PURCHASE  → quantity موجبة
- SALE      → quantity سالبة
- RETURN    → quantity موجبة
- DAMAGED   → quantity سالبة
- ADJUSTMENT→ موجبة أو سالبة حسب السياق (يتطلب note إلزامي دائماً)

## قاعدة صارمة
لا يوجد أي مسار في النظام يُعدّل `stock_quantity` مباشرة. كل تغيير
= إضافة سطر جديد في `mkt_inventory_movements` ثم إعادة حساب عبر
`StockCalculator.php`. هذا يحقق معيار القبول:
"يمكن معرفة سبب كل تغيير في المخزون" (Section 16 من المخطط).
