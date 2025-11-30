Rate — منصة لاكتشاف وتصنيف وتقييم الأفلام

## وصف سريع

Rate هو مشروع Laravel كامل (PHP + MySQL) مع واجهة بسيطة بـ HTML/CSS/JS، مصمم لاستخدامه كمحفظة عمل أو كقاعدة لتطبيق تقييم أفلام.

## تفاصيل صفحة الفيلم (Spec)

الهدف من الصفحة: تقديم كل المعلومات الضرورية عن فيلم محدد على صفحة واحدة ليتمكن الزائر من اتخاذ قرار سريع: مشاهدة، تقييم، أو حفظ الفيلم.

أقسام الصفحة الرئيسية (مرتبة حسب الأهمية):

-   Breadcrumbs / Navigation: مسار العودة والتصنيف.
-   Hero (Poster + Title): غلاف الفيلم كبير، العنوان، وسنة الإصدار، ووسوم التصنيفات.
-   Primary Actions: أزرار `مشاهدة المقطورة`، `أضف للمفضلة`، `قييم`، `مشاركة`.
-   Quick Facts (Metadata): مدة العرض، تاريخ الإصدار، اللغة، البلد، تصنيف العمر.
-   Rating Summary: متوسط التقييم (نجمات + رقم)، عدد التقييمات، توزيع التقييمات، زر إضافة تقييم.
-   Synopsis: ملخّص قصير مع زر `اقرأ المزيد` لعرض الوصف الكامل.
-   Cast & Crew: أسماء الممثلين الرئيسيين وأدوارهم (روابط إن وُجدت).
-   Media (Trailer & Gallery): مشغّل المقطورة، معرض صور قابل للتكبير.
-   User Reviews: قائمة مراجعات مع نموذج إضافة مراجعة (rating + نص) وفرز/فلترة.
-   Similar / Recommended: أفلام مشابهة للمتابعة.
-   External Links / IDs: روابط ومُعرّفات خارجية (`tmdb_id`, `imdb_id`).
-   Admin Controls (إذا كان المدير متصل): تحرير الفيلم، استيراد بيانات من TMDB.
-   SEO & Structured Data: تضمين JSON-LD (schema.org Movie) وmeta tags وصفية.
-   Accessibility: نص بديل للصور، عناصر قابلة للوصول بلوحة المفاتيح، ARIA labels.

نصائح تنفيذية:

-   تقسيم تحميل البيانات: احمل العناصر الأساسية أولاً (hero + metadata + rating)، ثم استخدم lazy-load للمراجعات والمعرض.
-   واجهات API المقترحة:
    -   `GET /api/movies/{id}`: بيانات أساسية + روابط للـ credits وreviews.
    -   `GET /api/movies/{id}/credits` و `GET /api/movies/{id}/reviews` للتحميل المتأخر.
    -   `POST /api/movies/{id}/rate` body: `{ "rating": 4, "review": "..." }`.
-   حقول النموذج المقترحة في قاعدة البيانات: `id, title, slug, overview, runtime, release_date, poster_path, backdrop_path, avg_rating, rating_count, tmdb_id, imdb_id, created_at, updated_at`.

استخدام داخل المشروع:

-   هذا القسم يعمل كمرجع للمصمّم/المطوّر والـProduct owner عند بناء `resources/views/movie/show.blade.php`، ويمكن نسخه كقالب لمراجعة التصميم قبل التنفيذ.

## أبرز الميزات

-   تصفّح وبحث أفلام (عبر العنوان والتصنيف)
-   صفحات تفاصيل الفيلم (ملخص، مدة، تاريخ إصدار)
-   إدارة تصنيفات وأصناف مخصصة
-   نظام تقييم ومراجعات سهل الاستخدام
-   أدوات استيراد بيانات (مثال: خدمة TMDB)

## التقنيات

-   Backend: PHP 8+, Laravel, Eloquent
-   Database: MySQL / MariaDB
-   Frontend: HTML, CSS, Vanilla JS, (اختياري: Bootstrap, Vite)
-   Tooling: Composer, NPM, Pest / PHPUnit

## تركيب سريع (محلي)

1. استنساخ المشروع:

```bash
git clone https://github.com/yousefgamal1998/RATE.git
cd RATE
```

2. تثبيت تبعيات PHP وNode:

```bash
composer install
npm install
```

3. إعداد البيئة:

```bash
copy .env.example .env    # Windows
php artisan key:generate
```

4. إعداد قاعدة البيانات وتشغيل المهاجرات:

```bash
php artisan migrate --seed
```

5. تشغيل الواجهة محلياً:

```bash
npm run dev    # لتطوير الواجهة (Vite)
php artisan serve --host=127.0.0.1 --port=8000
```

## واجهات برمجة التطبيقات — أمثلة سريعة

-   `GET /api/movies` — قائمة الأفلام (فلترة/تصفّح)
-   `GET /api/movies/{id}` — تفاصيل فيلم
-   `POST /api/movies/{id}/rate` — إرسال تقييم `{ "rating": 4, "review": "..." }`
-   `GET /api/categories` — قائمة التصنيفات
-   مصادقة: `POST /api/auth/login`, `POST /api/auth/register` (إن وُجدت)

## هيكل المشروع (مختصر)

-   `app/` — موديلات، كونترولرز، خدمات
-   `routes/` — تعريفات `web.php` و `api.php`
-   `resources/` — واجهة المستخدم وملفات الـassets
-   `scripts/` — سكربتات مساعدة للاستيراد والتشخيص
-   `tests/` — اختبارات وحدة/تكامل

## اختبارات

تشغيل الاختبارات (Pest / PHPUnit):

```bash
./vendor/bin/pest
# أو
./vendor/bin/phpunit
```

## إرشادات للمساهمة

-   افتح Issue أو Fork puis ارسل Pull Request.
-   اتبع نموذج الـcommit مختصر وواضح، وأضف اختبارات عند الإمكان.

## ترخيص

MIT — انظر ملف `LICENSE` للمزيد.

## ملاحظات نهائية

هذا README موجز وموجّه للاستخدام العملي والسريع. إن رغبت بتوسيع قسم الـAPI ليتطابق بدقة مع `routes/api.php` أو بإضافة ملف إعداد Docker، أخبرني وسأقوم بذلك.
