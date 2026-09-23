# Naming Conventions

## عام
| العنصر | الاصطلاح | مثال |
|---|---|---|
| Plugin slug | kebab-case | `maktaba-core` |
| Theme slug | kebab-case | `maktaba-theme` |
| PHP Namespace | PascalCase | `Maktaba\\Core\\Domain\\Book` |
| PHP Class | PascalCase | `StockCalculator`, `OrderStatusMap` |
| PHP Function/Method | camelCase | `calculateStock()`, `syncLibraryStatus()` |
| Custom Post Type slug | prefix `mkt_` + snake_case | `mkt_supplier`, `mkt_purchase` |
| Custom Taxonomy slug | prefix `mkt_` + snake_case | `mkt_author`, `mkt_publisher` |
| Post/Term Meta key | prefix `_mkt_` (خاص) أو `mkt_` (عام) | `_mkt_purchase_price`, `mkt_author_biography` |
| جداول DB مخصصة | prefix `{$wpdb->prefix}mkt_` + snake_case جمع | `wp_mkt_inventory_movements` |
| Hook/Action مخصص | prefix `mkt_` + snake_case | `mkt_stock_recalculated` |
| AJAX action | prefix `mkt_` + snake_case فعل | `mkt_add_inventory_movement` |
| Settings key | prefix `mkt_` + snake_case | `mkt_whatsapp_number`, `mkt_free_shipping_threshold` |

## قاعدة صارمة
أي اسم جديد (Class, Meta Key, DB Table) يجب أن يلتزم بهذا الجدول قبل
الدمج. اسم لا يتبع الاصطلاح = رفض تلقائي في المراجعة.
