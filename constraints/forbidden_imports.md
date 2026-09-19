# Forbidden Imports (فحص آلي مقترح لاحقاً عبر CI بسيط)

| من | ممنوع استيراده |
|---|---|
| `Domain/**` | أي شيء من `Admin/`, `Frontend/`, `Integrations/` |
| `Domain/**` | WooCommerce classes (`WC_Product`, `WC_Order`, ...) مباشرة |
| `Domain/**` | WordPress hooks مباشرة (`add_action`, `add_filter`) |
| `wp-content/themes/**` | أي `global $wpdb` / استعلام SQL مباشر |
| `wp-content/themes/**` | أي Post Meta يبدأ بـ `_mkt_` من الحقول الداخلية
  (`_mkt_purchase_price`, `_mkt_shipping_cost`) |
| `Frontend/**`, `Admin/**` | WooCommerce classes مباشرة (يجب المرور عبر `Integrations/WooCommerce/`) |
