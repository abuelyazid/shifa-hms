# شفاء | نظام إدارة المستشفى 🏥

مشروع سلسلة **"نظام مستشفى من الصفر"** على قناة CODE X.

## الأقسام
- **الاستقبال والمواعيد:** حجز بالمواعيد الفاضية فقط، رقم دور، تأكيد على واتساب، وقاعة انتظار لايف
- **المرضى:** ملف طبي كامل فيه الزيارات والتحاليل والفواتير
- **الأطباء والأقسام:** أيام العمل، الدوام، مدة الكشف، وسعره
- **شاشة الكشف:** العلامات الحيوية، التشخيص، الروشتة، وطلب التحاليل
- **الفواتير والخزنة:** فاتورة تلقائية من الكشف، تحصيل كاش وفيزا، خصم، مصروفات، وصافي يومي
- **الصيدلية:** صرف الروشتات وخصم المخزون، وتنبيه للأدوية اللي قربت تخلص
- **المعمل:** من سحب العينة لحد إدخال النتيجة

## التقنيات
Laravel 11 / Livewire 3 / Tailwind CSS / MySQL أو SQLite

## التشغيل
```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

افتح `http://localhost:8000` وسجّل دخول:

| الدور | البريد | كلمة المرور |
|---|---|---|
| مدير النظام | admin@shifa.test | password |
| استقبال | reception@shifa.test | password |
| طبيب | doctor@shifa.test | password |
| خزنة | cashier@shifa.test | password |
| صيدلي | pharmacy@shifa.test | password |
| معمل | lab@shifa.test | password |

---
📺 [CODE X على يوتيوب](https://www.youtube.com/@CHANNEL_HANDLE)
