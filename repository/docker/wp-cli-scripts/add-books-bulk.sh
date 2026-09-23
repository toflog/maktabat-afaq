#!/bin/sh
# استيراد دفعي لقاعدة بيانات كتب دار نهضة مصر الأكثر طلباً
# يقرأ: docker/wp-cli-scripts/data/dar-nahdet-misr-top-books.psv
# (ملف بفاصل "|" pipe-delimited — وليس CSV عادي، لتجنّب تعارض الفواصل
# العادية مع النصوص العربية والوصف الطويل)
#
# التشغيل من مجلد repository/:
#   docker compose run --rm wpcli sh /scripts/add-books-bulk.sh
#
# ⚠ مهم قبل الاستخدام الفعلي: راجع ملف البيانات نفسه أولاً —
# الحقول (isbn, page_count, publication_year, price) فارغة أو صفر لمعظم
# الكتب لأنني لم أجد رقماً واحداً موثوقاً وثابتاً لها عبر عدة مصادر
# (تتفاوت الأسعار/الإصدارات بين المكتبات والطبعات). عدّل هذه الحقول
# بالأرقام الفعلية المطبوعة على نسختك الورقية أو حسب سعر بيعك قبل
# الاستيراد، وإلا ستُحفظ كحقول فارغة/صفرية يجب تعديلها لاحقاً يدوياً.

set -e

DATA_FILE="/scripts/data/dar-nahdet-misr-top-books.psv"

# تخطّي السطر الأول (رؤوس الأعمدة)
tail -n +2 "$DATA_FILE" | while IFS='|' read -r TITLE AUTHOR PUBLISHER CATEGORY ISBN LANGUAGE PAGE_COUNT PUBLICATION_YEAR OFFICIAL_PRICE_EGP DESCRIPTION; do
    [ -z "$TITLE" ] && continue

    echo "=================================================="
    echo "== ${TITLE}"
    echo "=================================================="

    wp term create mkt_author "${AUTHOR}" --porcelain 2>/dev/null || true
    wp term create mkt_publisher "${PUBLISHER}" --porcelain 2>/dev/null || true
    wp term create product_cat "${CATEGORY}" --porcelain 2>/dev/null || true

    BOOK_ID=$(wp post create \
      --post_type=product \
      --post_status=publish \
      --post_title="${TITLE}" \
      --post_content="${DESCRIPTION}" \
      --porcelain)

    wp post term set "${BOOK_ID}" mkt_author "${AUTHOR}"
    wp post term set "${BOOK_ID}" mkt_publisher "${PUBLISHER}"
    wp post term set "${BOOK_ID}" product_cat "${CATEGORY}"

    wp post meta update "${BOOK_ID}" _stock_status "instock"
    wp post meta update "${BOOK_ID}" _manage_stock "no"
    wp post meta update "${BOOK_ID}" _sold_individually "no"
    wp post meta update "${BOOK_ID}" _virtual "no"
    wp post meta update "${BOOK_ID}" _downloadable "no"

    wp post meta update "${BOOK_ID}" _mkt_isbn "${ISBN}"
    wp post meta update "${BOOK_ID}" _mkt_language "${LANGUAGE:-ar}"
    wp post meta update "${BOOK_ID}" _mkt_page_count "${PAGE_COUNT:-0}"
    wp post meta update "${BOOK_ID}" _mkt_publication_year "${PUBLICATION_YEAR:-0}"
    wp post meta update "${BOOK_ID}" _mkt_condition "new"
    wp post meta update "${BOOK_ID}" _mkt_official_price_egp "${OFFICIAL_PRICE_EGP:-0}"

    wp eval "\Maktaba\Core\Admin\Pricing\PriceRecalculator::recalculateOne(${BOOK_ID});"

    echo "تم — معرّف المنتج: ${BOOK_ID}"
    echo ""
done

echo "== انتهى الاستيراد =="
echo "تذكير: أي كتاب بحقل official_price_egp = 0 لن يظهر له سعر للزوار"
echo "حتى تدخل سعره الرسمي الحقيقي من موقع دار نهضة مصر لاحقاً — راجع"
echo "دليل منهجية جمع البيانات قبل نشر أي كتاب فعلياً للزوار."
