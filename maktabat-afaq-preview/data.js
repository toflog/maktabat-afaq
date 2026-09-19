// مكتبة آفاق — بيانات وهمية للمعاينة البصرية فقط
// هذه ليست بيانات حقيقية من قاعدة البيانات — مكتوبة يدوياً هنا فقط
// لملء التصميم بمحتوى واقعي الشكل. في الموقع الحقيقي (ووردبريس) هذه
// البيانات تأتي من WooCommerce + BookRepository، وليس من هذا الملف.

const MKT_BOOKS = [
    {
        slug: "ibn-khaldoun",
        title: "مقدمة ابن خلدون (3 أجزاء)",
        author: "ابن خلدون",
        category: "تراث",
        price_usd: 3.60,
        price_syp: 481,
        description: "مقدمة ابن خلدون، ألّفها عبد الرحمن بن خلدون كمدخل لكتابه العبر، واستقلّت لاحقاً كعمل موسوعي مؤسِّس في علم الاجتماع والتاريخ والعمران البشري. هذه الطبعة بتحقيق د. علي عبد الواحد وافي في ثلاثة مجلدات.",
        badge: "الأكثر طلباً"
    },
    {
        slug: "al-shaikh-wal-bahr",
        title: "الشيخ والبحر",
        author: "إرنست همنغواي",
        category: "أدب مترجم",
        price_usd: 4.20,
        price_syp: 562,
        description: "رواية قصيرة تدور حول صراع صياد عجوز مع سمكة ضخمة في عرض البحر — من أشهر أعمال همنغواي، وأحد الأعمال المؤسِّسة للأدب الحديث المختصر.",
        badge: "وصل حديثاً"
    },
    {
        slug: "asr-musamama",
        title: "أسر مسممة",
        author: "سوزان فوروارد",
        category: "تنمية بشرية وعلم نفس",
        price_usd: 3.00,
        price_syp: 401,
        description: "من أكثر كتب علم النفس الأسري مبيعاً عالمياً، يناقش أثر أساليب التربية الخاطئة على تكوين شخصية الأبناء.",
        badge: null
    },
    {
        slug: "jisr-ala-nahr-drina",
        title: "جسر على نهر درينا",
        author: "إيفو أندريتش",
        category: "أدب مترجم",
        price_usd: 4.80,
        price_syp: 642,
        description: "رواية تتتبّع تاريخ جسر في البوسنة عبر قرون، وتحوّلاته إلى شاهد صامت على تحوّلات المجتمع من حوله.",
        badge: null
    }
];

// ===================================================================
// تبديل العملة (دولار/ليرة سورية) — نفس منطق Frontend/Pricing/CurrencyToggle
// الحقيقي في الإضافة، لكن مكرَّر هنا لأن هذه نسخة ثابتة منفصلة تماماً.
// ===================================================================

(function () {
    var STORAGE_KEY = "mkt_preferred_currency";

    function applyCurrency(currency) {
        document.querySelectorAll(".mkt-price-usd").forEach(function (el) {
            el.style.display = currency === "usd" ? "" : "none";
        });
        document.querySelectorAll(".mkt-price-syp").forEach(function (el) {
            el.style.display = currency === "syp" ? "" : "none";
        });
        document.querySelectorAll("#mkt-currency-toggle button").forEach(function (btn) {
            btn.classList.toggle("active", btn.dataset.currency === currency);
        });
    }

    function initToggle() {
        var toggle = document.getElementById("mkt-currency-toggle");
        if (!toggle) return;

        toggle.querySelectorAll("button").forEach(function (btn) {
            btn.addEventListener("click", function () {
                var currency = btn.dataset.currency;
                try { localStorage.setItem(STORAGE_KEY, currency); } catch (e) {}
                applyCurrency(currency);
            });
        });

        var saved = "usd";
        try { saved = localStorage.getItem(STORAGE_KEY) || "usd"; } catch (e) {}
        applyCurrency(saved);
    }

    document.addEventListener("DOMContentLoaded", initToggle);
})();

function mktFormatMoney(n) {
    // نستخدم en-US عمداً بدل ar لضمان أرقام غربية (3.60) بدل الأرقام
    // الهندية الشرقية (٣٫٦٠) التي يفرضها لوكال "ar" في متصفحات كروم —
    // نفس القاعدة المتبعة في أغلب متاجر الكتب العربية التجارية (جرير، نون).
    return n.toLocaleString("en-US", { minimumFractionDigits: n % 1 === 0 ? 0 : 2 });
}
