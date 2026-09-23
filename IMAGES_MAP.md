# خريطة مجلدات الصور في المشروع (مرجع للمزامنة مع ريبو الصور المنفصل)

هذا الملف مرجع سريع لمكان كل مجلد صور في هذا الريبو، عشان أي تحديث
جاي من ريبو الصور (رقم 2) يترجم فوراً لمسار دقيق هنا.

## 1. معاينة GitHub Pages
```
maktabat-afaq-preview/covers/
```
الأسماء المتوقعة (24 من دار التنوير، جاهزة الأسماء بالفعل):
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
باقي 16 كتاباً (العبيكان، جرير، أثر، جامعة الملك سعود) لسه بلا صور
حقيقية — أسماء ملفاتهم لسه غير محدَّدة نهائياً، هتتحدد وقت توفر
الصورة الفعلية.

## 2. حزم الاستيراد الحقيقي في ووردبريس
```
repository/docker/wp-cli-scripts/packs/dar-alobeikan-pack/covers/
repository/docker/wp-cli-scripts/packs/maktabat-jarir-pack/covers/
repository/docker/wp-cli-scripts/packs/dar-athar-pack/covers/
repository/docker/wp-cli-scripts/packs/ksu-press-pack/covers/
repository/docker/wp-cli-scripts/packs/dar-tanweer-pack/covers/
```
أسماء الملفات المطلوبة لكل حزمة مطابقة تماماً لحقل `cover_image` في
`manifest.json` الخاص بها (بدون بادئة `covers/` وقت التسمية، لأنها
مضافة بالفعل في المسار المخزَّن).

**ملاحظة مهمة**: هذه المجلدات حالياً **مجلدات عادية غير مضغوطة** في
الريبو (سهولة إضافة الصور عبر git). وقت الاستيراد الفعلي في Docker،
كل مجلد حزمة لازم يُضغط أولاً إلى `.zip` (حسب مواصفة
`PublisherPackImporter.php`) قبل تشغيل `import-publisher-pack.sh`.

## آلية التحديث المقترحة

كل ما تحط صور جديدة في ريبو الصور (رقم 2)، ابعتلي رابط الريبو (لو
عام) أو قائمة أسماء الملفات، وأنا أقولك:
1. أي صورة تروح لأنهي مسار بالظبط من فوق
2. هل الاسم مطابق للمتوقع في `data.js` / `manifest.json`، ولا محتاج
   إعادة تسمية أول
