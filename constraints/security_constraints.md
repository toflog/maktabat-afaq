# Security Constraints

## إلزامي من اليوم الأول
```
HTTPS
حساب Admin واحد قوي لكل شخص (ممنوع مشاركة حساب Admin بين عدة أشخاص)
2FA حيثما أمكن مجاناً
تحديث دوري: WordPress Core / WooCommerce / كل الإضافات
حماية صفحة /wp-admin (Rate limiting بسيط / تغيير مسار الدخول)
```

## قواعد خاصة بهذا المشروع
- `purchase_price`, `shipping_cost`, `actual_cost`, `profit`:
  ممنوع ظهورها في أي استجابة HTTP يراها الزائر العام (Templates,
  REST responses, رسائل WhatsApp).
- AJAX endpoints (`contracts/api_contracts/admin_ajax_endpoints.md`):
  إلزامي `nonce` + `current_user_can(manage_options)` على كل واحد.
- لا Endpoint عام (Frontend) يستقبل بيانات كتابة (POST) بدون nonce.
