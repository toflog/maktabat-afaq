# مكتبة آفاق — نسخة معاينة تصميمية (GitHub Pages)

## تحديث مهم: كتالوج حقيقي بدل بيانات وهمية

بدءاً من هذه النسخة، `data.js` بقى فيه **40 كتاباً حقيقياً** بدل
الأربعة الوهميين — نفس الكتب الموجودة في حزم الناشرين الخمس اللي تم
تجهيزها للاستيراد في ووردبريس (العبيكان، جرير، دار أثر، جامعة الملك
سعود، دار التنوير). الهدف: المعاينة تبقى مرآة حقيقية قدر الإمكان
للمشروع الفعلي، مش مجرد بيانات تجريبية.

## خطوة يدوية متبقية: نسخ صور دار التنوير

الكود بقى يدعم عرض صور أغلفة حقيقية (`<img>`) بدل مربع "لا صورة"،
ومن أصل الـ40 كتاباً، **24 كتاب دار التنوير عندها صور جاهزة فعلاً**
(بعد إعادة تسميتها بحروف لاتينية). لكن أنا معنديش وصول لملفات
الصور الفعلية على قرصك — لازم تنسخهم بنفسك:

```
انسخ كل ملفات الصور الـ24 (بأسمائها الإنجليزية الجديدة) إلى:
maktabat-afaq-preview/covers/
```

أسماء الملفات المتوقعة بالظبط (لازم تتطابق حرفياً مع الموجود في
`data.js`):
```
serial-aldeen-alfalsafa-suqrat.jpg   moshkilat-takoon-shakhsan.jpg
al-anqaa-tahtariq.jpg                masna-alsahab.jpg
al-shaikh-wal-bahr.jpg               batal-min-hatha-alzaman.jpg
ibnat-aldabit.jpg                    al-murahiq.jpg
jisr-ala-nahr-drina.jpg              anni.jpg
al-zanbaqa-alsawdaa.jpg              afal-alsawab-afaluh-alan.jpg
al-khilafa-waldeen-waldawla.png      al-unsuriyya-fi-alkhaleej.png
al-arak-fi-jahannam.png              madinat-alaajeeb.png
naqd-alquwwa.png                     maweidona-fi-shahr-aab.png
rihla-ila-albuldan-alishtirakiyya.png  qisas-qasira.png
fadihat-alqarn.png                   thamaniyat-abnaa-umooma.jpg
jur-mihrathak.jpg                    wassi-madak.jpg
```

**الـ16 كتاب الباقيين** (العبيكان 6، جرير 5، أثر 1، جامعة الملك
سعود 4) حقل `cover` عندهم فاضي `""` عمداً — هيظهروا بمربع "لا صورة"
تلقائياً لحد ما تتوفر صور حقيقية لهم، وساعتها تحدّث `data.js` بنفس
الطريقة (اسم ملف داخل `covers/`).

## بخصوص الأسعار

39 من أصل 40 كتاباً سعرها الرسمي غير مؤكد بعد، فبدل اختلاق أرقام،
كل الكتب تعرض **"السعر قريباً"**. لما يتوفر سعر رسمي مؤكد لكتاب،
أضف له في `data.js`:
```js
official_price: 45, currency: "ر.س"
```
وهيظهر تلقائياً بدل "السعر قريباً".

**ملاحظة**: تبديل العملة (دولار/ليرة سورية) اتشال مؤقتاً من الواجهة
لحد ما يبقى عندنا عدد كافٍ من الأسعار الرسمية الحقيقية — مافيش داعي
له وهو فاضي من الأرقام.

## كيف تعمل على GitHub Pages هنا تحديداً

بما أن هذا المجلد (`maktabat-afaq-preview/`) هو مجلد فرعي داخل ريبو
المشروع الكامل، فإن "Deploy from a branch" العادي في إعدادات Pages
لن يعمل. لذلك تم إعداد GitHub Action مخصصة:
`.github/workflows/deploy-preview-pages.yml`

من إعدادات الريبو: **Settings ← Pages ← Source: GitHub Actions**.
أي `push` على `main` يلمس هذا المجلد سينشر تلقائياً.

## طريقة العمل اليومية

عدّل الملفات في هذا المجلد مباشرة، ثم:
```
git add maktabat-afaq-preview
git commit -m "وصف التعديل"
git push
```

## القيود المتبقية

- لا بحث فعلي، لا سلة فعلية
- زر واتساب يفتح فعلياً لكنه يشير لرقم وهمي (`963000000000`)
- روابط "تصفح حسب التصنيف" حالياً وهمية (`#`)
- 16 كتاباً من أصل 40 لسه من غير صورة غلاف حقيقية
