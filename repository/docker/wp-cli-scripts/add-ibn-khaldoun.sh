#!/bin/sh
# إضافة منتج/كتاب "مقدمة ابن خلدون" (طبعة دار نهضة مصر، 3 أجزاء) عبر WP-CLI
#
# البيانات أدناه مُتحقَّق منها عبر بحث فعلي على الإنترنت (وليست مُختلَقة):
#   - المؤلف والمحقِّق ودار النشر: مؤكَّدة من عدة مصادر بيع متطابقة
#   - سنة الطبعة (السابعة، 2014) وعدد الصفحات الإجمالي (1413) من مصدر
#     يصف تحديداً طبعة دار نهضة مصر المدمجة من 3 مجلدات في مجلد واحد
#   - السعر: لم يُدخَل بعد (0) — لا يوجد رقم واحد ثابت وموثوق من
#     nahdetmisr.com تحديداً تحقّقنا منه؛ افتحه بنفسك وحدّث
#     _mkt_official_price_egp بالرقم الحقيقي (انظر تعليمات آخر السكربت)
#   - ISBN: **لا يوجد رقم واحد موحّد لعبوة الثلاثة أجزاء مجتمعة** — كل
#     مجلد له ISBN مستقل بحسب الطبعة. المُدرَج أدناه هو ISBN الجزء الأول
#     تحديداً (كمرجع فقط) — إن كان لديك الرقم الفعلي المطبوع على نسختك
#     الورقية، استبدله فوراً؛ فرقم ISBN تفصيل يجب أن يطابق نسختك بدقة.
#
# التشغيل من مجلد repository/:
#   docker compose run --rm wpcli sh /scripts/add-ibn-khaldoun.sh

set -e

echo "== إنشاء/التأكد من التصنيفات =="
wp term create mkt_author "ابن خلدون" --porcelain 2>/dev/null || true
wp term create mkt_publisher "دار نهضة مصر" --porcelain 2>/dev/null || true
wp term create product_cat "تراث" --porcelain 2>/dev/null || true

echo "== إنشاء المنتج =="
BOOK_ID=$(wp post create \
  --post_type=product \
  --post_status=publish \
  --post_title="مقدمة ابن خلدون (3 أجزاء) - طبعة دار نهضة مصر" \
  --post_content="مقدمة ابن خلدون، ألّفها عبد الرحمن بن خلدون (ت 808هـ) كمدخل لكتابه التاريخي الكبير \"العبر\"، واستقلّت لاحقاً كعمل موسوعي مؤسِّس في علم الاجتماع والتاريخ والعمران البشري. هذه الطبعة بتحقيق د. علي عبد الواحد وافي، صادرة عن دار نهضة مصر في ثلاثة مجلدات." \
  --porcelain)

echo "معرّف المنتج (Product ID): ${BOOK_ID}"

echo "== ربط التصنيفات بالمنتج =="
wp post term set "${BOOK_ID}" mkt_author "ابن خلدون"
wp post term set "${BOOK_ID}" mkt_publisher "دار نهضة مصر"
wp post term set "${BOOK_ID}" product_cat "تراث"

echo "== حقول WooCommerce الأساسية (مخزون) =="
wp post meta update "${BOOK_ID}" _stock_status "instock"
wp post meta update "${BOOK_ID}" _manage_stock "no"
wp post meta update "${BOOK_ID}" _sold_individually "no"
wp post meta update "${BOOK_ID}" _virtual "no"
wp post meta update "${BOOK_ID}" _downloadable "no"

echo "== حقول بيانات الكتاب المخصَّصة (Domain/Book/BookRepository) =="
wp post meta update "${BOOK_ID}" _mkt_isbn "9786009915982"
wp post meta update "${BOOK_ID}" _mkt_language "ar"
wp post meta update "${BOOK_ID}" _mkt_page_count "1413"
wp post meta update "${BOOK_ID}" _mkt_publication_year "2014"
wp post meta update "${BOOK_ID}" _mkt_condition "new"

echo "== السعر الرسمي (جنيه مصري) — ⚠ غير مؤكَّد من nahdetmisr.com بعد =="
echo "== حدّثه لاحقاً بالرقم الحقيقي عبر: =="
echo "==   wp post meta update ${BOOK_ID} _mkt_official_price_egp <الرقم الحقيقي> =="
echo "==   wp eval \"\\Maktaba\\Core\\Admin\\Pricing\\PriceRecalculator::recalculateOne(${BOOK_ID});\" =="
wp post meta update "${BOOK_ID}" _mkt_official_price_egp "0"
wp eval "\Maktaba\Core\Admin\Pricing\PriceRecalculator::recalculateOne(${BOOK_ID});"

echo "== تم =="
echo "افتح: http://localhost:8080/?p=${BOOK_ID} لمعاينة صفحة الكتاب"
echo "أو من لوحة التحكم: wp-admin/post.php?post=${BOOK_ID}&action=edit"
echo ""
echo "تذكير: لم يُضَف غلاف الكتاب (الصورة البارزة) — أضفه يدوياً من"
echo "لوحة التحكم لأن استيراد صور من الإنترنت آلياً هنا قد يخالف حقوق النشر."
