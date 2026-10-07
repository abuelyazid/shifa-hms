<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'شفاء' }} | نظام إدارة المستشفى</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen">
@php
    $user = auth()->user();
    $nav = [
        ['dashboard',    'layout-dashboard', 'لوحة التحكم',   ['reception','doctor','cashier','pharmacist','lab']],
        ['appointments', 'calendar-days',    'المواعيد',       ['reception','doctor']],
        ['patients',     'users',            'المرضى',         ['reception','doctor']],
        ['doctors',      'stethoscope',      'الأطباء والأقسام', ['reception']],
        ['invoices',     'receipt',          'الفواتير',       ['cashier','reception']],
        ['cashbox',      'wallet',           'الخزنة',         ['cashier']],
        ['pharmacy',     'pill',             'الصيدلية',       ['pharmacist']],
        ['lab',          'flask-conical',    'المعمل',         ['lab','doctor']],
    ];
@endphp
<div class="flex min-h-screen">
    {{-- الشريط الجانبي --}}
    <aside class="fixed inset-y-0 right-0 z-30 w-64 bg-shifa-800 flex flex-col">
        <div class="px-5 pt-6 pb-8"><x-logo /></div>
        <nav class="flex-1 px-3 space-y-1">
            @foreach ($nav as [$route, $icon, $label, $roles])
                @if ($user->can_access(...$roles))
                    <a href="{{ route($route) }}" wire:navigate
                       @class(['nav-link', 'active' => request()->routeIs($route . '*')])>
                        <x-icon :name="$icon" class="w-5 h-5 shrink-0" />{{ $label }}
                    </a>
                @endif
            @endforeach
        </nav>
        <div class="m-3 p-3 rounded-2xl bg-shifa-900/60 flex items-center gap-3">
            <span class="grid place-items-center w-10 h-10 rounded-full bg-shifa-500 text-white font-semibold">{{ mb_substr($user->name, 0, 1) }}</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ $user->name }}</p>
                <p class="text-xs text-shifa-200">{{ $user->roleName() }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="p-2 rounded-lg text-shifa-200 hover:bg-white/10 hover:text-white" title="تسجيل الخروج" aria-label="تسجيل الخروج">
                    <x-icon name="log-out" class="w-4 h-4" />
                </button>
            </form>
        </div>
    </aside>

    {{-- المحتوى --}}
    <main class="flex-1 mr-64 min-w-0">
        <header class="sticky top-0 z-20 bg-paper/85 backdrop-blur border-b border-line">
            <div class="flex items-center gap-4 px-8 h-[72px]">
                <div class="flex-1">
                    <h1 class="text-[22px]">{{ $title ?? '' }}</h1>
                    <p class="text-sm text-ink-mute">{{ now()->locale('ar')->translatedFormat('l، j F Y') }}</p>
                </div>
            </div>
        </header>
        <div class="p-8">{{ $slot }}</div>
    </main>
</div>
@livewireScripts
</body>
</html>
