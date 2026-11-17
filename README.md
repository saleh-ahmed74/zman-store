# Hima Store

متجر عربي/إنجليزي مبني على Laravel 12 وLivewire 3 وMySQL 8، باستخدام Blade وTailwind وAlpine المضمن مع Livewire.

## المتطلبات

- PHP 8.2+ مع `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `intl` و`zip`.
- Composer 2، Node.js 20+ وnpm، وMySQL 8.
- قاعدة بيانات MySQL فارغة. التطبيق لا يستخدم SQLite.

## التشغيل المحلي

1. انسخ `.env.example` إلى `.env`، وأنشئ قاعدة `hima_store` ومستخدم MySQL بصلاحيات عليها.
2. ضع بيانات الاتصال في `.env` (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`). لا ترفع `.env` إلى Git.
3. شغّل `composer install` ثم `php artisan key:generate`.
4. شغّل `php artisan migrate --seed` لإنشاء الجداول وبيانات Hima التجريبية.
5. لبذر حساب أدمن، اضبط `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` في `.env` ثم شغّل `php artisan db:seed`؛ لا يوجد تسجيل أدمن من واجهة الموقع.
6. شغّل `npm install` ثم `npm run build`، وبعدها `composer dev` للتطوير.

الكاش والجلسات يستخدمان الملفات، والـ queue يعمل بشكل متزامن ليتوافق مع الاستضافة المشتركة. الإعدادات قابلة للتغيير عبر `.env`.

## الطباعة والنشر

تفاصيل الطباعة الحرارية وA4، والنشر بدون SSH على Hostinger، والنسخ الاحتياطي والمراقبة ستضاف ضمن مراحل التشغيل في [سجل التنفيذ](execution-log.md).
