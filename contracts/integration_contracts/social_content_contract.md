# Integration Contract: Social Content

## الهدف
تقليل وقت إعداد منشورات Facebook/Instagram لكل كتاب (Section 24 من المخطط).

## البنية المُنتَجة لكل كتاب (Book Social Content)
```json
{
  "title": "string",
  "short_description": "string",
  "price": "decimal",
  "cover_image": "url",
  "hashtags": ["string"],
  "call_to_action": "اطلب الآن عبر واتساب",
  "product_url": "url"
}
```

## نقطة التنفيذ
`Integrations/SocialContent/BookSocialContentBuilder.php` يبني هذه
البنية عند نشر/تعديل الكتاب، ويعرضها في لوحة الإدارة كـ "نص جاهز للنسخ"
— **لا يوجد نشر تلقائي API إلى Facebook/Instagram في V1** (يخالف
Zero-Cost/Zero-Complexity — النشر يدوي بالنسخ).
