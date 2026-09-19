# Technology Constraints

## معتمد في V1
```
WordPress + WooCommerce + Theme مجاني (Child Theme) + إضافات مجانية
عند الحاجة الواضحة فقط (SEO, Caching, Security, Backup)
PHP 8.3+ (موصى به) / MySQL 8.0+ أو MariaDB 10.11+ / HTTPS إلزامي
```

## ممنوع في V1 (من مخطط المشروع Section 41 مباشرة)
```
❌ تطبيق Android / iOS
❌ بوابة دفع إلكتروني
❌ اشتراكات (Subscriptions)
❌ Multi-vendor
❌ حسابات عملاء إجبارية
❌ نظام ولاء
❌ AI (بما فيه أي نشر/بحث تلقائي بالذكاء الاصطناعي)
❌ Microservices
❌ Redis
❌ Kubernetes
❌ نظام محاسبة ضخم
❌ نظام شحن دولي
❌ إضافات كثيرة "قد تفيد لاحقاً"
```

## قاعدة التبرير
أي إضافة (Plugin) أو مكتبة جديدة تحتاج سطراً مكتوباً في
`architecture/dependency_rules.md` يوضح الحاجة التجارية/التقنية
الواضحة لها — وإلا تُرفض تلقائياً (مبدأ Zero-Cost First).
