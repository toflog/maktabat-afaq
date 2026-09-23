# Dependency Rules

## قواعد الاستيراد المسموحة
| من ↓ / إلى → | Shared | Domain | Integrations | Admin | Frontend |
|---|---|---|---|---|---|
| Shared        | – | ❌ | ❌ | ❌ | ❌ |
| Domain        | ✅ | – | ❌ | ❌ | ❌ |
| Integrations  | ✅ | ✅ | – | ❌ | ❌ |
| Admin         | ✅ | ✅ | ✅ | – | ❌ |
| Frontend      | ✅ | ✅ | ✅ | ❌ | – |

## اعتماديات خارجية مسموحة
- WordPress Core APIs: مسموحة في `Integrations/`, `Admin/`, `Frontend/` فقط.
- WooCommerce Classes/Hooks: مسموحة **حصراً** في `Integrations/WooCommerce/`.
- لا مكتبات JS/PHP خارجية إضافية دون تبرير مكتوب في هذا الملف (Zero-Cost).
