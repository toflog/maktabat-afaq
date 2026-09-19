#!/bin/sh
# استيراد حزمة ناشر كاملة (manifest.json + صور أغلفة) بأمر واحد.
# انظر: دليل-تجميع-حزمة-دار-نشر.md للمواصفة الكاملة لبناء الحزمة.
#
# الاستخدام من مجلد repository/:
#   1) ضع ملف الحزمة (.zip) داخل docker/wp-cli-scripts/packs/
#      (أنشئ مجلد packs/ إن لم يكن موجوداً)
#   2) شغّل:
#        docker compose run --rm wpcli sh /scripts/import-publisher-pack.sh /scripts/packs/اسم-الحزمة.zip
#
# ملاحظة: المسار يبدأ بـ /scripts/ لأنه المسار *داخل الحاوية* (مربوط
# بمجلد docker/wp-cli-scripts/ عندك على القرص) — وليس مساراً على جهازك مباشرة.

set -e

ZIP_PATH="$1"

if [ -z "$ZIP_PATH" ]; then
    echo "خطأ: حدّد مسار ملف الحزمة."
    echo "الاستخدام: sh import-publisher-pack.sh /scripts/packs/اسم-الحزمة.zip"
    exit 1
fi

wp eval "
\$result = \Maktaba\Core\Admin\Import\PublisherPackImporter::importFromZip('${ZIP_PATH}');
echo \"== نتيجة الاستيراد ==\n\";
echo 'تم إنشاء: ' . \$result['created'] . \" كتاب\n\";
echo 'تم تحديث (ISBN مطابق لكتاب موجود): ' . \$result['updated'] . \" كتاب\n\";
echo 'أخطاء/كتب متجاوَزة: ' . count(\$result['errors']) . \"\n\";
foreach (\$result['errors'] as \$e) {
    echo '  - ' . \$e . \"\n\";
}
"
