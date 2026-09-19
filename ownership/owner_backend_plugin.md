# Ownership: Backend / Plugin (maktaba-core)

## يملك
```
wp-content/plugins/maktaba-core/**
db/migrations/**
contracts/data_contracts/**
contracts/domain_contracts/**
contracts/integration_contracts/woocommerce_integration.md
```

## المسؤوليات
- كل منطق العمل (Domain, Integrations, Admin screens, AJAX endpoints).
- سلامة عقود البيانات (Data Contracts) وتطبيقها فعلياً في الكود.
- إدارة جدول `mkt_inventory_movements` وربطه بـ WooCommerce.

## ممنوع عليه
- التعديل المباشر على ملفات `wp-content/themes/maktaba-theme/**`
  (فقط عبر واجهات/Hooks معرّفة في `interfaces/integration_points.md`).
