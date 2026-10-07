<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <input type="date" wire:model.live="date" class="input w-auto font-semibold">
        <button wire:click="$set('showExpense', true)" class="btn-ghost mr-auto"><x-icon name="arrow-up-right" class="w-4 h-4" />تسجيل مصروف</button>
    </div>

    {{-- رصيد اليوم: العنصر الأبرز --}}
    <section class="rounded-2xl bg-white border border-line overflow-hidden">
        <div class="grid md:grid-cols-[1.3fr_1fr_1fr_1fr] divide-y md:divide-y-0 md:divide-x md:divide-x-reverse divide-line">
            <div class="p-6 bg-shifa-800 text-white">
                <p class="text-sm text-shifa-200">صافي الخزنة {{ $day->isToday() ? 'النهارده' : $day->locale('ar')->translatedFormat('j F') }}</p>
                <p class="text-5xl font-bold tabular-nums mt-2">{{ number_format($cash + $card - $out) }}</p>
                <p class="text-sm text-shifa-200 mt-1">جنيه مصري</p>
            </div>
            <div class="p-6"><p class="text-sm text-ink-soft flex items-center gap-1.5"><x-icon name="banknote" class="w-4 h-4 text-emerald-600" />تحصيل كاش</p><x-money :value="$cash" class="block text-2xl font-bold mt-2" /></div>
            <div class="p-6"><p class="text-sm text-ink-soft flex items-center gap-1.5"><x-icon name="receipt" class="w-4 h-4 text-shifa-600" />تحصيل فيزا</p><x-money :value="$card" class="block text-2xl font-bold mt-2" /></div>
            <div class="p-6"><p class="text-sm text-ink-soft flex items-center gap-1.5"><x-icon name="arrow-up-right" class="w-4 h-4 text-rose-500" />مصروفات</p><x-money :value="$out" class="block text-2xl font-bold mt-2 text-rose-700" /></div>
        </div>
    </section>

    <section class="panel overflow-hidden">
        <div class="panel-head"><h2 class="text-base">حركة الخزنة</h2><span class="text-sm text-ink-mute tabular-nums">{{ $movements->count() }} حركة</span></div>
        <table class="table-base">
            <thead><tr><th>الوقت</th><th>البيان</th><th>الطرف</th><th>الطريقة</th><th class="text-left">المبلغ</th></tr></thead>
            <tbody>
            @forelse ($movements as $m)
                <tr>
                    <td class="tabular-nums text-ink-soft" dir="ltr">{{ $m['at']->format('H:i') }}</td>
                    <td class="flex items-center gap-2.5">
                        <span @class(['grid place-items-center w-8 h-8 rounded-lg', 'bg-emerald-50 text-emerald-700' => $m['in'], 'bg-rose-50 text-rose-700' => ! $m['in']])>
                            <x-icon :name="$m['in'] ? 'arrow-down-left' : 'arrow-up-right'" class="w-4 h-4" />
                        </span>{{ $m['title'] }}
                    </td>
                    <td>{{ $m['who'] }}</td>
                    <td class="text-ink-soft">{{ $m['method'] === 'cash' ? 'كاش' : 'فيزا' }}</td>
                    <td @class(['text-left font-semibold tabular-nums', 'text-emerald-700' => $m['in'], 'text-rose-700' => ! $m['in']])>{{ $m['in'] ? '+' : '-' }}{{ number_format($m['amount']) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-14 text-center text-ink-mute">مفيش حركة في اليوم ده.</td></tr>
            @endforelse
            </tbody>
        </table>
    </section>

    <x-modal show="showExpense" title="تسجيل مصروف">
        <form wire:submit="addExpense" class="space-y-4">
            <div><label class="label">البند</label><input wire:model="title" class="input" placeholder="مثلاً: صيانة جهاز الأشعة">@error('title')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
            <div><label class="label">المبلغ</label><input wire:model="amount" type="number" class="input">@error('amount')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="$set('showExpense', false)" class="btn-ghost">إلغاء</button><button class="btn-primary">تسجيل</button></div>
        </form>
    </x-modal>
</div>
