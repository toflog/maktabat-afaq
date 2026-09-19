# Layer Rules

## الطبقات (من الأعمق إلى الأسطح)
```
Shared        (ثوابت، دوال مساعدة عامة — بلا منطق عمل)
   ▲
Domain        (منطق العمل الصافي: قواعد، حسابات، انتقالات حالة)
   ▲
Integrations  (جسور خارجية: WooCommerce, WhatsApp, Social)
   ▲
Admin / Frontend (واجهات فقط — تستدعي الطبقات الأدنى ولا تحتوي منطقاً)
```

## قاعدة الاتجاه
الاعتماد يذهب للأعلى فقط تجاه الطبقات الأدنى. لا يوجد اعتماد عكسي:
- `Domain/` لا يعرف شيئاً عن `Admin/` أو `Frontend/` أو `Integrations/`.
- `Integrations/` تعرف `Domain/` و `Shared/` فقط.
- `Admin/` و `Frontend/` يعرفان `Domain/`, `Integrations/`, `Shared/`.

## اختبار بسيط لأي Pull Request
> إذا وجدت استيراد (`require`/`use`) من طبقة أدنى إلى طبقة أعلى
> (مثلاً `Domain/` يستدعي شيئاً من `Admin/`) — هذا مرفوض فوراً.
