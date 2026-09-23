# Data Contract: Supplier

## القرار المعماري
Custom Post Type: `mkt_supplier` (وليس Taxonomy) لأن المورد كيان تشغيلي
غني بالبيانات (جهة اتصال، هاتف، عنوان) وله علاقة مباشرة بسجلات Purchase.

## الحقول (Post Meta)
```
name            → post_title (native)
contact_name    → Post Meta: _mkt_contact_name
phone           → Post Meta: _mkt_phone
address         → Post Meta: _mkt_address
notes           → Post Meta: _mkt_notes
supplier_type   → Post Meta: _mkt_supplier_type
                  قيم: direct_publisher | authorized_distributor | secondary_market
is_active       → post_status (native: publish/draft)
```

## قاعدة
`supplier_type` إلزامي عند إنشاء أي Purchase جديد — يُستخدم لاحقاً
لدعم رسالة "كتب أصلية" ببيانات فعلية وليس شعاراً تسويقياً فقط.
