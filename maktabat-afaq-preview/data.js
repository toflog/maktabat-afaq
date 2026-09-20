// مكتبة آفاق — الكتالوج الحقيقي (40 كتاب عبر 5 دور نشر)
// هذا الملف بقى يعكس فعلياً حزم الناشرين اللي جهزناها:
// العبيكان (6) + جرير (5) + دار أثر (1) + جامعة الملك سعود (4) + دار التنوير (24)
//
// ملاحظة أمانة مهمة: 39 من أصل 40 كتاباً سعرها الرسمي غير مؤكد بعد،
// فبدل اختلاق أرقام، كل الكتب تعرض "السعر قريباً" حالياً. حقل
// cover فاضي "" لأي كتاب لسه معندوش صورة غلاف حقيقية — هيظهر بمربع
// "لا صورة" تلقائياً لحد ما تتوفر الصورة وتُحدَّث هنا.

const MKT_BOOKS = [
    // ===== دار العبيكان للنشر =====
    { slug: "la-tahzan", title: "لا تحزن", author: "عائض القرني", category: "تنمية بشرية وفكر إسلامي", publisher: "دار العبيكان للنشر", cover: "", badge: "الأكثر طلباً" },
    { slug: "ashaar-khaled-alfaisal", title: "أشعار خالد الفيصل", author: "خالد الفيصل", category: "شعر", publisher: "دار العبيكان للنشر", cover: "", badge: null },
    { slug: "fi-qalb-aljihad", title: "في قلب الجهاد", author: "", category: "سيرة ذاتية وسياسة", publisher: "دار العبيكان للنشر", cover: "", badge: null },
    { slug: "indama-tusbih-alhaqiqa-khiyana", title: "عندما تصبح الحقيقة خيانة", author: "", category: "سياسة وتاريخ", publisher: "دار العبيكان للنشر", cover: "", badge: null },
    { slug: "hillary-clinton-kursi-aletiraf", title: "هيلاري كلينتون على كرسي الاعتراف", author: "", category: "سيرة ذاتية وسياسة", publisher: "دار العبيكان للنشر", cover: "", badge: null },
    { slug: "al-rawd-al-murabbi", title: "الروض المربع بشرح زاد المستقنع", author: "منصور بن يونس البهوتي", category: "فقه إسلامي", publisher: "دار العبيكان للنشر", cover: "", badge: null },

    // ===== مكتبة جرير =====
    { slug: "al-rahib-alathi-baa-sayaratah-alferrari", title: "الراهب الذي باع سيارته الفيراري", author: "روبن شارما", category: "تنمية بشرية", publisher: "مكتبة جرير", cover: "", badge: null },
    { slug: "lughat-aljasad", title: "لغة الجسد - علم وأسرار لغة الجسد في العمل", author: "", category: "تنمية بشرية", publisher: "مكتبة جرير", cover: "", badge: null },
    { slug: "al-aadat-alsabaa-lilmurahiqeen", title: "العادات السبع للمراهقين الأكثر فعالية", author: "شون كوفي", category: "تنمية بشرية", publisher: "مكتبة جرير", cover: "", badge: null },
    { slug: "tawaqaf-an-islah-nafsak", title: "توقف عن إصلاح نفسك: استيقظ، كل شيء على ما يرام", author: "", category: "تنمية بشرية", publisher: "مكتبة جرير", cover: "", badge: null },
    { slug: "iktashif-alqaed-alathi-bidakhilik", title: "اكتشف القائد الذي بداخلك: فن القيادة في العمل", author: "", category: "إدارة وقيادة", publisher: "مكتبة جرير", cover: "", badge: null },

    // ===== دار أثر للنشر والتوزيع =====
    { slug: "al-buasaa", title: "البؤساء (4 مجلدات)", author: "فيكتور هوجو", category: "روايات مترجمة", publisher: "دار أثر للنشر والتوزيع", cover: "", badge: "الأكثر طلباً" },

    // ===== دار جامعة الملك سعود للنشر =====
    { slug: "usr-alhisab", title: "عسر الحساب: الدليل الإرشادي لأهم الأعراض", author: "دانييلا لوكانجيلي", category: "علم النفس التربوي", publisher: "دار جامعة الملك سعود للنشر", cover: "", badge: null },
    { slug: "alsuluk-altanzimi", title: "السلوك التنظيمي في المؤسسات الصحية وسلامة المريض", author: "", category: "إدارة صحية", publisher: "دار جامعة الملك سعود للنشر", cover: "", badge: null },
    { slug: "taamiq-alfahm-almuhasaba", title: "تعميق الفهم في الإطار المفاهيمي للمحاسبة المالية", author: "خالد بن رشيد العديم", category: "محاسبة", publisher: "دار جامعة الملك سعود للنشر", cover: "", badge: null },
    { slug: "alfashal-almutakarrir", title: "الفشل المتكرر لانغراس الأجنة في محاولات أطفال الأنابيب", author: "", category: "طب", publisher: "دار جامعة الملك سعود للنشر", cover: "", badge: null },

    // ===== دار التنوير (24 كتاباً — صورها جاهزة فعلياً) =====
    { slug: "serial-aldeen-alfalsafa-suqrat", title: "صراع الدين والفلسفة في محاكمة سقراط", author: "", category: "فلسفة", publisher: "دار التنوير", cover: "serial-aldeen-alfalsafa-suqrat.jpg", badge: null },
    { slug: "moshkilat-takoon-shakhsan", title: "مشكلة أن تكون شخصًا", author: "", category: "فلسفة", publisher: "دار التنوير", cover: "moshkilat-takoon-shakhsan.jpg", badge: null },
    { slug: "al-anqaa-tahtariq", title: "العنقاء تحترق", author: "", category: "أدب", publisher: "دار التنوير", cover: "al-anqaa-tahtariq.jpg", badge: null },
    { slug: "masna-alsahab", title: "مصنع السحاب", author: "", category: "أدب", publisher: "دار التنوير", cover: "masna-alsahab.jpg", badge: null },
    { slug: "al-shaikh-wal-bahr", title: "الشيخ والبحر", author: "إرنست همنغواي", category: "روايات مترجمة - كلاسيكيات", publisher: "دار التنوير", cover: "al-shaikh-wal-bahr.jpg", badge: "الأكثر طلباً" },
    { slug: "batal-min-hatha-alzaman", title: "بطل من هذا الزمان", author: "ميخائيل ليرمنتوف", category: "روايات مترجمة - كلاسيكيات روسية", publisher: "دار التنوير", cover: "batal-min-hatha-alzaman.jpg", badge: null },
    { slug: "ibnat-aldabit", title: "ابنة الضابط", author: "ألكسندر بوشكين", category: "روايات مترجمة - كلاسيكيات روسية", publisher: "دار التنوير", cover: "ibnat-aldabit.jpg", badge: null },
    { slug: "al-murahiq", title: "المراهق", author: "فيودور دوستويفسكي", category: "روايات مترجمة - كلاسيكيات روسية", publisher: "دار التنوير", cover: "al-murahiq.jpg", badge: null },
    { slug: "jisr-ala-nahr-drina", title: "جسر على نهر درينا", author: "إيفو أندريتش", category: "روايات مترجمة - أدب البلقان", publisher: "دار التنوير", cover: "jisr-ala-nahr-drina.jpg", badge: null },
    { slug: "anni", title: "عني", author: "", category: "سيرة ذاتية", publisher: "دار التنوير", cover: "anni.jpg", badge: null },
    { slug: "al-zanbaqa-alsawdaa", title: "الزنبقة السوداء", author: "ألكسندر دوما", category: "روايات مترجمة - كلاسيكيات فرنسية", publisher: "دار التنوير", cover: "al-zanbaqa-alsawdaa.jpg", badge: null },
    { slug: "afal-alsawab-afaluh-alan", title: "أفعل الصواب أفعله الآن", author: "", category: "تنمية بشرية", publisher: "دار التنوير", cover: "afal-alsawab-afaluh-alan.jpg", badge: null },
    { slug: "al-khilafa-waldeen-waldawla", title: "الخلافة والدين والدولة", author: "", category: "فكر سياسي إسلامي", publisher: "دار التنوير", cover: "al-khilafa-waldeen-waldawla.png", badge: null },
    { slug: "al-unsuriyya-fi-alkhaleej", title: "العنصرية في الخليج", author: "", category: "دراسات اجتماعية", publisher: "دار التنوير", cover: "al-unsuriyya-fi-alkhaleej.png", badge: null },
    { slug: "al-arak-fi-jahannam", title: "العراك في جهنم", author: "", category: "أدب", publisher: "دار التنوير", cover: "al-arak-fi-jahannam.png", badge: null },
    { slug: "madinat-alaajeeb", title: "مدينة الأعاجيب", author: "", category: "روايات مترجمة", publisher: "دار التنوير", cover: "madinat-alaajeeb.png", badge: null },
    { slug: "naqd-alquwwa", title: "نقد القوة", author: "", category: "فلسفة اجتماعية", publisher: "دار التنوير", cover: "naqd-alquwwa.png", badge: null },
    { slug: "maweidona-fi-shahr-aab", title: "موعدنا في شهر آب", author: "غابرييل غارسيا ماركيز", category: "روايات مترجمة", publisher: "دار التنوير", cover: "maweidona-fi-shahr-aab.png", badge: "الأكثر طلباً" },
    { slug: "rihla-ila-albuldan-alishtirakiyya", title: "رحلة إلى البلدان الاشتراكية", author: "", category: "أدب رحلات", publisher: "دار التنوير", cover: "rihla-ila-albuldan-alishtirakiyya.png", badge: null },
    { slug: "qisas-qasira", title: "قصص قصيرة", author: "", category: "قصص قصيرة", publisher: "دار التنوير", cover: "qisas-qasira.png", badge: null },
    { slug: "fadihat-alqarn", title: "فضيحة القرن", author: "غابرييل غارسيا ماركيز", category: "مقالات وصحافة أدبية", publisher: "دار التنوير", cover: "fadihat-alqarn.png", badge: null },
    { slug: "thamaniyat-abnaa-umooma", title: "ثمانية أبناء عمومة", author: "لويزا ماي ألكوت", category: "روايات مترجمة - أدب يافعين كلاسيكي", publisher: "دار التنوير", cover: "thamaniyat-abnaa-umooma.jpg", badge: null },
    { slug: "jur-mihrathak", title: "جر محراثك", author: "", category: "أدب/شذرات", publisher: "دار التنوير", cover: "jur-mihrathak.jpg", badge: null },
    { slug: "wassi-madak", title: "وسع مداك", author: "", category: "تنمية بشرية", publisher: "دار التنوير", cover: "wassi-madak.jpg", badge: null }
];

// ===================================================================
// عرض السعر: كل الكتب حالياً "السعر قريباً" لأن الأسعار الرسمية غير
// مؤكدة (39 من 40). دالة موحّدة عشان لو حدّثنا سعر كتاب لاحقاً في
// المصفوفة فوق (بإضافة official_price)، يظهر تلقائياً بدل النص.
// ===================================================================

function mktPriceHTML(book) {
    if (typeof book.official_price === "number" && book.official_price > 0) {
        return '<span class="mkt-price-value">' +
            book.official_price.toLocaleString("en-US") + ' ' + (book.currency || "ر.س") +
            '</span>';
    }
    return '<span class="mkt-price-soon">السعر قريباً</span>';
}
