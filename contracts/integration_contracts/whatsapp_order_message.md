# Integration Contract: WhatsApp Order Message

## المسار (Section 10 من المخطط)
```
صفحة الكتاب → [اطلب الكتاب] → رابط wa.me مُجهَّز مسبقاً → تأكيد يدوي
```

## قالب الرسالة الإلزامي (Message Template)
```
مرحباً، أريد طلب كتاب:
"{book_title}"

رابط الكتاب:
{book_permalink}
```

## قاعدة إلزامية
- الرابط يُبنى في `Frontend/OrderCTA/WhatsAppOrderButton.php` فقط.
- **ممنوع** إدراج أي حقل من `purchase_price` أو `shipping_cost` أو أي
  بيانات داخلية في نص الرسالة.
- رقم WhatsApp المستهدف يُقرأ حصراً من
  `Admin/Settings/GeneralSettings.php` (إعداد عام واحد) — لا يُكتب
  كنص ثابت (hardcoded) في أي ملف.
