# API Contract: Admin AJAX Endpoints

لا يوجد REST API عام مفتوح خارجياً في V1 (لا حاجة تجارية له —
لا تطبيق موبايل، لا تكامل خارجي فعلي). الحاجة الوحيدة هي نقاط WordPress
AJAX داخلية للوحة الإدارة فقط، محمية بـ `nonce` + `current_user_can()`.

## نقاط الاتصال المعتمدة (V1)
| Action | الغرض | الملف المسؤول |
|---|---|---|
| `mkt_add_inventory_movement` | تسجيل حركة مخزون يدوية (Adjustment/Damaged) | `Admin/MetaBoxes/InventoryMetaBox` → `Domain/Inventory/StockCalculator.php` |
| `mkt_change_order_status` | تغيير حالة الطلب مع تسجيل السبب | `Admin/MetaBoxes/OrderStatusMetaBox` → `Domain/Order/OrderStatusMap.php` |
| `mkt_generate_social_content` | توليد نص المنشور الجاهز لكتاب | `Admin/MetaBoxes/SocialContentMetaBox` → `Integrations/SocialContent` |
| `mkt_set_order_source` | تعيين/تحديث مصدر الطلب (يغذّي `mkt_customers` تلقائياً) | `Admin/MetaBoxes/OrderStatusMetaBox` → `Integrations/WooCommerce/OrderMetaBridge::setSource()` |
| `mkt_search_books` | بحث حيّ عن الكتب (عنوان أو ISBN) لاستخدامه في اختيار الكتاب بدل إدخال معرّف رقمي يدوياً | `Admin/Purchase/PurchaseAdminPage` → `Domain/Book/BookRepository.php` (قراءة فقط) |

## قاعدة إلزامية
كل Endpoint هنا يُستدعى فقط من `wp-admin` (`is_admin()` = true) —
**ممنوع أي endpoint AJAX يُستدعى من صفحات الموقع العامة (Frontend)**
باستثناء ما هو موثق صراحة (لا يوجد حالياً).
