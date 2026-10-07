<div class="space-y-6" wire:poll.30s>
    {{-- لوحة الانتظار --}}
    <section class="rounded-2xl bg-shifa-800 text-white p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg">قاعة الانتظار الآن</h2>
            <span class="flex items-center gap-2 text-sm text-shifa-200"><span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>تحديث تلقائي</span>
        </div>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-3">
            @foreach ($queues as $q)
                <div class="rounded-xl bg-white/[.06] border border-white/10 p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $q['doctor']->department->color }}"></span>
                        <p class="text-sm font-medium truncate">{{ $q['doctor']->name }}</p>
                    </div>
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-xs text-shifa-200 mb-1">جوه دلوقتي</p>
                            <p class="text-4xl font-bold leading-none tabular-nums">{{ $q['current']?->queue_no ? sprintf('%02d', $q['current']->queue_no) : '—' }}</p>
                        </div>
                        <div class="text-left">
                            <p class="text-xs text-shifa-200 mb-1.5">التالي</p>
                            <div class="flex gap-1 justify-end">
                                @forelse ($q['waiting']->take(3) as $w)
                                    <span class="grid place-items-center w-8 h-8 rounded-lg bg-saffron-500 text-ink text-sm font-bold tabular-nums">{{ sprintf('%02d', $w->queue_no) }}</span>
                                @empty
                                    <span class="text-sm text-shifa-200">لا يوجد</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- أرقام اليوم --}}
    <section class="panel grid grid-cols-2 lg:grid-cols-4 divide-x divide-x-reverse divide-line">
        @php
            $stats = [
                ['calendar-days', 'مواعيد النهارده', $today->where('status', '!=', 'cancelled')->count(), $today->where('status', 'done')->count() . ' انتهى كشفهم'],
                ['clock', 'في الانتظار', $today->where('status', 'arrived')->count(), 'وصلوا المستشفى'],
                ['banknote', 'إيراد اليوم', number_format($income), 'ج.م محصّلة'],
                ['flask-conical', 'تحاليل منتظرة', $labPending, 'في المعمل'],
            ];
        @endphp
        @foreach ($stats as [$icon, $label, $value, $hint])
            <div class="p-5 flex items-start gap-4">
                <span class="grid place-items-center w-11 h-11 rounded-xl bg-shifa-50 text-shifa-700"><x-icon :name="$icon" /></span>
                <div>
                    <p class="text-sm text-ink-soft">{{ $label }}</p>
                    <p class="text-[28px] font-bold leading-tight tabular-nums">{{ $value }}</p>
                    <p class="text-xs text-ink-mute">{{ $hint }}</p>
                </div>
            </div>
        @endforeach
    </section>

    <div class="grid xl:grid-cols-[1fr_380px] gap-6">
        {{-- مواعيد اليوم --}}
        <section class="panel overflow-hidden">
            <div class="panel-head">
                <h2 class="text-base">مواعيد اليوم</h2>
                <a href="{{ route('appointments') }}" wire:navigate class="text-sm font-medium text-shifa-700 hover:underline">كل المواعيد</a>
            </div>
            <table class="table-base">
                <thead><tr><th>الدور</th><th>المريض</th><th>الطبيب</th><th>الميعاد</th><th>الحالة</th></tr></thead>
                <tbody>
                @foreach ($today->take(8) as $a)
                    <tr>
                        <td class="tabular-nums font-semibold text-ink-soft">{{ sprintf('%02d', $a->queue_no) }}</td>
                        <td>
                            <a href="{{ route('patients.show', $a->patient) }}" wire:navigate class="font-medium hover:text-shifa-700">{{ $a->patient->name }}</a>
                            <p class="text-xs text-ink-mute">{{ $a->patient->file_no }}</p>
                        </td>
                        <td>
                            <p>{{ $a->doctor->name }}</p>
                            <p class="text-xs text-ink-mute">{{ $a->doctor->department->name }}</p>
                        </td>
                        <td class="tabular-nums" dir="ltr">{{ substr($a->time, 0, 5) }}</td>
                        <td><x-badge :color="$a->statusColor()">{{ $a->statusLabel() }}</x-badge></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>

        <div class="space-y-6">
            {{-- الإيراد --}}
            <section class="panel p-5">
                @php $max = max(1, $days->max('total')); @endphp
                <div class="flex items-baseline justify-between mb-1">
                    <h2 class="text-base">إيراد آخر 14 يوم</h2>
                    <x-money :value="$days->sum('total')" class="font-semibold" />
                </div>
                <p class="text-xs text-ink-mute mb-5">التحصيل اليومي من الخزنة</p>
                <div class="flex items-end gap-1.5 h-36" dir="ltr">
                    @foreach ($days as $d)
                        <div class="flex-1 flex flex-col items-center gap-1.5 group">
                            <div class="w-full rounded-md {{ $loop->last ? 'bg-shifa-600' : 'bg-shifa-200 group-hover:bg-shifa-400' }} transition"
                                 style="height: {{ max(4, $d['total'] / $max * 120) }}px" title="{{ number_format($d['total']) }} ج.م"></div>
                            <span class="text-[10px] text-ink-mute tabular-nums">{{ $d['day']->format('j') }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- نواقص الصيدلية --}}
            <section class="panel">
                <div class="panel-head">
                    <h2 class="text-base">أدوية قربت تخلص</h2>
                    <a href="{{ route('pharmacy') }}" wire:navigate class="text-sm font-medium text-shifa-700 hover:underline">الصيدلية</a>
                </div>
                <ul class="divide-y divide-line">
                    @forelse ($lowStock as $m)
                        <li class="flex items-center justify-between px-5 py-3">
                            <div>
                                <p class="text-sm font-medium">{{ $m->name }} <span class="text-ink-mute font-normal" dir="ltr">{{ $m->strength }}</span></p>
                                <p class="text-xs text-ink-mute">{{ $m->form }}</p>
                            </div>
                            <span class="text-sm font-bold text-rose-700 tabular-nums">{{ $m->stock }} باقي</span>
                        </li>
                    @empty
                        <li class="px-5 py-6 text-sm text-ink-mute">كل الأدوية متوفرة.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</div>
