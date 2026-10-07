<div class="grid lg:grid-cols-3 gap-5 items-start" wire:poll.20s>
    @foreach ($columns as $key => $col)
        <section class="rounded-2xl bg-white/60 border border-line">
            <header class="flex items-center justify-between px-4 py-3.5">
                <x-badge :color="$col['color']">{{ $col['label'] }}</x-badge>
                <span class="text-sm font-semibold tabular-nums text-ink-soft">{{ $col['orders']->count() }}</span>
            </header>
            <div class="px-3 pb-3 space-y-3">
                @forelse ($col['orders'] as $o)
                    <article wire:key="o{{ $o->id }}" class="bg-white rounded-xl border border-line p-4 shadow-panel">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-semibold">{{ $o->test->name }}</p>
                                <p class="text-sm text-ink-soft">{{ $o->patient->name }}</p>
                            </div>
                            <span class="rounded-md bg-paper px-2 py-1 text-xs font-semibold" dir="ltr">{{ $o->test->code }}</span>
                        </div>
                        <p class="text-xs text-ink-mute mt-2">طلب {{ $o->visit->doctor->name }}، {{ $o->created_at->locale('ar')->diffForHumans() }}</p>
                        @if ($key === 'pending')
                            <button wire:click="collect({{ $o->id }})" class="btn-ghost w-full mt-3 py-2"><x-icon name="droplet" class="w-4 h-4 text-rose-500" />تم سحب العينة</button>
                        @elseif ($key === 'collected')
                            <button wire:click="openResult({{ $o->id }})" class="btn-primary w-full mt-3 py-2"><x-icon name="file-text" class="w-4 h-4" />إدخال النتيجة</button>
                        @else
                            <p class="mt-3 rounded-lg bg-emerald-50 text-emerald-700 px-3 py-2 text-sm font-semibold tabular-nums" dir="ltr">{{ $o->result }} {{ $o->test->unit }}</p>
                        @endif
                    </article>
                @empty
                    <p class="px-2 py-6 text-center text-sm text-ink-mute">مفيش طلبات هنا.</p>
                @endforelse
            </div>
        </section>
    @endforeach

    <x-modal show="showResult" :title="'نتيجة ' . ($current->test->name ?? '')">
        @if ($current)
            <form wire:submit="saveResult" class="space-y-4">
                <p class="text-sm text-ink-soft">{{ $current->patient->name }}. المعدل الطبيعي: <b dir="ltr">{{ $current->test->normal_range ?? 'حسب التقرير' }}</b></p>
                <div><label class="label">النتيجة @if($current->test->unit)<span class="text-ink-mute" dir="ltr">({{ $current->test->unit }})</span>@endif</label>
                    <input wire:model="result" class="input text-lg font-semibold" dir="ltr">@error('result')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
                <div class="flex justify-end gap-2"><button type="button" wire:click="$set('showResult', false)" class="btn-ghost">إلغاء</button><button class="btn-primary">حفظ النتيجة</button></div>
            </form>
        @endif
    </x-modal>
</div>
