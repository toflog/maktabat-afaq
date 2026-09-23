#!/bin/sh
# قالب عام لإضافة أي كتاب عبر WP-CLI — بلا الحاجة لأي ملف جديد من Claude.
#
# طريقة الاستخدام:
#   1) انسخ هذا الملف بنفسك (نفس المجلد) باسم جديد، مثلاً:
#        copy add-book-template.sh add-book-lisan-al-arab.sh   (Windows)
#        cp add-book-template.sh add-book-lisan-al-arab.sh     (Git Bash / WSL)
#   2) افتح النسخة الجديدة بأي محرر نصوص (Notepad++, VS Code...) وعدّل
#      القيم بين علامتي التنصيص أدناه فقط — لا تلمس ما بعد سطر "لا تعدّل
#      بعد هذا السطر".
#   3) شغّله من مجلد repository/:
#        docker compose run --rm wpcli sh /scripts/add-book-lisan-al-arab.sh
#
# ملاحظة مهمة: يجب أن يُحفَظ الملف بترميز UTF-8 (بلا BOM) كي يظهر النص
# العربي بشكل صحيح — أغلب المحررات الحديثة (VS Code, Notepad++) تحفظ
# بهذا الترميز افتراضياً.

# ==================== عدّل هذه القيم فقط ====================

TITLE="عنوان الكتاب هنا"
AUTHOR="اسم المؤلف هنا"
PUBLISHER="اسم دار النشر هنا"
CATEGORY="اسم التصنيف هنا"          # مثال: روايات / تراث / فكر وفلسفة
DESCRIPTION="وصف قصير للكتاب هنا."

ISBN="0000000000000"                 # اتركه فارغاً "" إن لم يتوفر
LANGUAGE="ar"                        # ar / en / fr ... إلخ
PAGE_COUNT="0"                       # اتركه 0 إن لم يتوفر
PUBLICATION_YEAR="0"                 # اتركه 0 إن لم يتوفر
CONDITION="new"                      # new أو used فقط (حسب Constants.php)
OFFICIAL_PRICE_EGP="0"               # السعر كما يظهر حرفياً على nahdetmisr.com
                                      # (جنيه مصري) لهذا الكتاب تحديداً — وليس
                                      # سعر أي مكتبة أو موزّع آخر. اتركه 0 إن لم
                                      # تتحقق منه بعد؛ لن يظهر سعر للزوار حتى
                                      # تُدخله لاحقاً من لوحة التحكم أو هنا.
                                      # كل شيء تحته (دولار/ليرة سورية) يُحسَب
                                      # تلقائياً من هذا الرقم وحده — لا تُدخله يدوياً.

# ================= لا تعدّل بعد هذا السطر =================

set -e

echo "== إنشاء/التأكد من التصنيفات =="
wp term create mkt_author "${AUTHOR}" --porcelain 2>/dev/null || true
wp term create mkt_publisher "${PUBLISHER}" --porcelain 2>/dev/null || true
wp term create product_cat "${CATEGORY}" --porcelain 2>/dev/null || true

echo "== إنشاء المنتج =="
BOOK_ID=$(wp post create \
  --post_type=product \
  --post_status=publish \
  --post_title="${TITLE}" \
  --post_content="${DESCRIPTION}" \
  --porcelain)

echo "معرّف المنتج (Product ID): ${BOOK_ID}"

echo "== ربط التصنيفات بالمنتج =="
wp post term set "${BOOK_ID}" mkt_author "${AUTHOR}"
wp post term set "${BOOK_ID}" mkt_publisher "${PUBLISHER}"
wp post term set "${BOOK_ID}" product_cat "${CATEGORY}"

echo "== حقول WooCommerce الأساسية (مخزون) =="
wp post meta update "${BOOK_ID}" _stock_status "instock"
wp post meta update "${BOOK_ID}" _manage_stock "no"
wp post meta update "${BOOK_ID}" _sold_individually "no"
wp post meta update "${BOOK_ID}" _virtual "no"
wp post meta update "${BOOK_ID}" _downloadable "no"

echo "== حقول بيانات الكتاب المخصَّصة (Domain/Book/BookRepository) =="
wp post meta update "${BOOK_ID}" _mkt_isbn "${ISBN}"
wp post meta update "${BOOK_ID}" _mkt_language "${LANGUAGE}"
wp post meta update "${BOOK_ID}" _mkt_page_count "${PAGE_COUNT}"
wp post meta update "${BOOK_ID}" _mkt_publication_year "${PUBLICATION_YEAR}"
wp post meta update "${BOOK_ID}" _mkt_condition "${CONDITION}"
wp post meta update "${BOOK_ID}" _mkt_official_price_egp "${OFFICIAL_PRICE_EGP}"

echo "== حساب السعر بالدولار والليرة السورية (Admin/Pricing/PriceRecalculator) =="
wp eval "\Maktaba\Core\Admin\Pricing\PriceRecalculator::recalculateOne(${BOOK_ID});"

echo "== تم =="
echo "افتح: http://localhost:8080/?p=${BOOK_ID} لمعاينة صفحة الكتاب"
echo "أو من لوحة التحكم: wp-admin/post.php?post=${BOOK_ID}&action=edit"
echo ""
echo "تذكير: أضف غلاف الكتاب (الصورة البارزة) يدوياً من لوحة التحكم."
