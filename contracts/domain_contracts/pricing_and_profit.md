# Domain Contract: Pricing & Profit

## معادلة التكلفة الفعلية
```
actual_cost = purchase_price + shipping_cost + additional_costs
```

## معادلة الربح
```
profit = selling_price - actual_cost
```

## قاعدة عزل البيانات
- `purchase_price`, `shipping_cost`, `actual_cost`, `profit` = **داخلية بالكامل**.
- محظور تماماً ظهورها في: أي Template في `wp-content/themes/`, أي REST
  response عام, أي رسالة WhatsApp تلقائية.
- الحساب يتم حصراً في `Domain/Pricing/ProfitCalculator.php` ويُستهلك
  فقط من `Admin/Reports/`.
