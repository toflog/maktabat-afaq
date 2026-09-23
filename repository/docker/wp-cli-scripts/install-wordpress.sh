#!/bin/sh
# إعداد كامل للموقع بأمر واحد — يُغني عن معالج التثبيت اليدوي بالكامل،
# وعن الضغط يدوياً على "تفعيل" لكل من WooCommerce والإضافة والثيم.
#
# طريقة الاستخدام:
#   1) عدّل القيم في القسم العلوي فقط (اسم الموقع، مستخدم المدير،
#      كلمة المرور، البريد الإلكتروني)
#   2) شغّله من مجلد repository/:
#        docker compose run --rm wpcli sh /scripts/install-wordpress.sh
#
# ⚠ لا يعمل إلا إذا كانت الحاويات (docker compose up -d) شغّالة فعلاً،
# ولا يعمل إلا مرة واحدة على تثبيت جديد بلا بيانات — إن كنت ثبّتّ
# ووردبريس يدوياً من قبل عبر المعالج، تجاهل هذا السكربت كلياً (لا داعي
# لتثبيت ثانٍ فوق تثبيت موجود).
#
# يحتاج اتصال إنترنت فعلي على جهازك (وليس هنا) لتحميل WooCommerce من
# مستودع ووردبريس الرسمي — هذا أمر طبيعي، الحاوية تتصل بالإنترنت من
# جهازك أنت مباشرة.

# ==================== عدّل هذه القيم فقط ====================

SITE_TITLE="مكتبة آفاق"
ADMIN_USER="admin"
ADMIN_PASSWORD="ChangeMe123!"          # غيّرها لكلمة مرور فعلية قوية
ADMIN_EMAIL="admin@example.com"
SITE_URL="http://localhost:8080"

# ================= لا تعدّل بعد هذا السطر =================

set -e

echo "== 1/4: تثبيت ووردبريس (بديل معالج التثبيت اليدوي) =="
wp core install \
  --url="${SITE_URL}" \
  --title="${SITE_TITLE}" \
  --admin_user="${ADMIN_USER}" \
  --admin_password="${ADMIN_PASSWORD}" \
  --admin_email="${ADMIN_EMAIL}" \
  --skip-email

echo "== 2/4: تثبيت وتفعيل WooCommerce =="
wp plugin install woocommerce --activate

echo "== 3/4: تفعيل Maktaba Core =="
wp plugin activate maktaba-core

echo "== 4/4: تثبيت Storefront (الثيم الأصلي) ثم تفعيل Maktaba Theme =="
# maktaba-theme هو Child Theme فوق Storefront — لا يعمل بلا تثبيت
# Storefront نفسه أولاً (ثيم مجاني من مستودع ووردبريس الرسمي، وليس
# جزءاً من كود هذا المشروع لأنه لا يخصّنا بالكامل).
wp theme install storefront
wp theme activate maktaba-theme

echo ""
echo "== تم كل شيء =="
echo "الموقع:        ${SITE_URL}"
echo "لوحة التحكم:   ${SITE_URL}/wp-admin"
echo "اسم المستخدم:  ${ADMIN_USER}"
echo "كلمة المرور:   ${ADMIN_PASSWORD}"
echo ""
echo "تذكير: غيّر كلمة المرور أعلاه في هذا الملف قبل التشغيل إن لم تكن قد فعلت بعد."
