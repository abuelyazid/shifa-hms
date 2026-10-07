<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول | شفاء</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen grid lg:grid-cols-[1fr_1.1fr]">
    <section class="flex flex-col justify-center px-8 sm:px-16 py-12 bg-white">
        <x-logo dark class="mb-14" />
        <div class="max-w-sm w-full">
            <h1 class="text-3xl mb-2">أهلاً بيك</h1>
            <p class="text-ink-soft mb-8">سجّل دخولك عشان تبدأ يومك في المستشفى.</p>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="label" for="email">البريد الإلكتروني</label>
                    <input id="email" name="email" type="email" value="{{ old('email', 'admin@shifa.test') }}" class="input py-3" dir="ltr" required autofocus>
                    @error('email') <p class="mt-2 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="password">كلمة المرور</label>
                    <input id="password" name="password" type="password" value="password" class="input py-3" dir="ltr" required>
                </div>
                <label class="flex items-center gap-2 text-sm text-ink-soft">
                    <input type="checkbox" name="remember" class="rounded border-line text-shifa-600 focus:ring-shifa-500"> افتكرني على الجهاز ده
                </label>
                <button class="btn-primary w-full py-3 text-base">تسجيل الدخول</button>
            </form>
        </div>
    </section>

    <section class="hidden lg:flex relative overflow-hidden bg-shifa-800 text-white p-14 flex-col justify-end">
        {{-- نبض القلب: العنصر المميز في الصفحة --}}
        <svg class="absolute inset-x-0 top-1/3 w-full h-40 text-shifa-500/50" viewBox="0 0 600 120" preserveAspectRatio="none" fill="none" aria-hidden="true">
            <path d="M0 60h170l18-34 26 70 22-58 14 22h350" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
        </svg>
        <div class="absolute -left-24 -top-24 w-96 h-96 rounded-full border border-white/10"></div>
        <div class="absolute -left-10 -top-10 w-64 h-64 rounded-full border border-white/10"></div>
        <div class="relative max-w-md">
            <p class="text-4xl font-bold leading-snug mb-5">كل ملفات المرضى، المواعيد، والفواتير في مكان واحد.</p>
            <p class="text-shifa-100/80 leading-relaxed">من الاستقبال لحد الصيدلية والمعمل والخزنة، كل قسم شايف شغله وبس.</p>
        </div>
    </section>
</body>
</html>
