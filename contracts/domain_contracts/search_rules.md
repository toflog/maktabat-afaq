# Domain Contract: Search Rules

## نطاق البحث الإلزامي (Section 25 من المخطط)
```
عنوان الكتاب | المؤلف | دار النشر | ISBN | التصنيف
```

## قاعدة
البحث يُبنى فوق WordPress/WooCommerce search الأساسي مع توسيع
الاستعلام (`pre_get_posts`) ليشمل Taxonomy terms (`mkt_author`,
`mkt_publisher`, `product_cat`) وحقل Meta (`_mkt_isbn`) — وليس محرك بحث
منفصل (يخالف مبدأ Zero-Cost/Zero-Complexity في V1).
