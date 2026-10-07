<div class="space-y-6">
    {{-- بطاقة المريض --}}
    <section class="panel p-6">
        <div class="flex flex-wrap items-start gap-5">
            <span @class(['grid place-items-center w-16 h-16 rounded-2xl text-xl font-bold',
                          'bg-shifa-50 text-shifa-700' => $patient->gender === 'male', 'bg-rose-50 text-rose-700' => $patient->gender === 'female'])>{{ $patient->initials }}</span>
            <div class="flex-1 min-w-[240px]">
                <h2 class="text-2xl">{{ $patient->name }}</h2>
                <p class="text-ink-soft mt-1">ملف رقم <span class="font-semibold tabular-nums">{{ $patient->file_no }}</span></p>
            </div>
            <a href="{{ route('appointments', ['patient' => $patient->id]) }}" wire:navigate class="btn-primary"><x-icon name="calendar-plus" class="w-4 h-4" />حجز ميعاد</a>
        </div>

        <dl class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-6 pt-6 border-t border-line">
            <div><dt class="text-xs text-ink-mute">السن</dt><dd class="font-semibold mt-1">{{ $patient->age }} سنة</dd></div>
            <div><dt class="text-xs text-ink-mute">النوع</dt><dd class="font-semibold mt-1">{{ $patient->gender === 'male' ? 'ذكر' : 'أنثى' }}</dd></div>
            <div><dt class="text-xs text-ink-mute">فصيلة الدم</dt><dd class="font-semibold mt-1 text-rose-700" dir="ltr">{{ $patient->blood_type ?? '—' }}</dd></div>
            <div><dt class="text-xs text-ink-mute">التليفون</dt><dd class="font-semibold mt-1 tabular-nums" dir="ltr">{{ $patient->phone }}</dd></div>
            <div><dt class="text-xs text-ink-mute">العنوان</dt><dd class="font-semibold mt-1">{{ $patient->address ?? '—' }}</dd></div>
        </dl>

        @if ($patient->allergies)
            <div class="mt-5 flex items-center gap-3 rounded-xl bg-rose-50 text-rose-700 px-4 py-3 text-sm font-medium">
                <x-icon name="alert-triangle" class="w-5 h-5" />{{ $patient->allergies }}
            </div>
        @endif
    </section>

    {{-- التبويبات --}}
    <nav class="flex gap-1 border-b border-line">
        @foreach (['visits' => ['الزيارات', $patient->visits->count()], 'labs' => ['التحاليل', $patient->labOrders->count()], 'invoices' => ['الفواتير', $patient->invoices->count()]] as $key => [$label, $count])
            <button wire:click="$set('tab', '{{ $key }}')"
                    @class(['px-4 py-3 text-sm font-medium border-b-2 -mb-px transition',
                            'border-shifa-600 text-shifa-700' => $tab === $key, 'border-transparent text-ink-soft hover:text-ink' => $tab !== $key])>
                {{ $label }} <span class="mr-1 text-xs text-ink-mute tabular-nums">{{ $count }}</span>
            </button>
        @endforeach
    </nav>

    @if ($tab === 'visits')
        <ol class="relative border-r-2 border-line mr-3 space-y-5">
            @forelse ($patient->visits as $v)
                <li class="relative pr-8">
                    <span class="absolute -right-[9px] top-5 w-4 h-4 rounded-full border-4 border-paper" style="background: {{ $v->doctor->department->color }}"></span>
                    <article class="panel p-5">
                        <header class="flex flex-wrap items-baseline gap-x-4 gap-y-1 mb-3">
                            <h3 class="text-base">{{ $v->diagnosis }}</h3>
                            <p class="text-sm text-ink-mute">{{ $v->doctor->name }}، {{ $v->doctor->department->name }}</p>
                            <time class="text-sm text-ink-mute mr-auto tabular-nums">{{ $v->created_at->locale('ar')->translatedFormat('j F Y') }}</time>
                        </header>
                        <p class="text-sm text-ink-soft mb-4">{{ $v->complaint }}</p>
                        @if ($v->vitals)
                            <div class="flex flex-wrap gap-2 mb-4 text-xs">
                                <span class="rounded-lg bg-paper px-2.5 py-1.5">الضغط <b class="tabular-nums" dir="ltr">{{ $v->vitals['bp'] }}</b></span>
                                <span class="rounded-lg bg-paper px-2.5 py-1.5">النبض <b class="tabular-nums">{{ $v->vitals['pulse'] }}</b></span>
                                <span class="rounded-lg bg-paper px-2.5 py-1.5">الحرارة <b class="tabular-nums">{{ $v->vitals['temp'] }}°</b></span>
                                <span class="rounded-lg bg-paper px-2.5 py-1.5">الوزن <b class="tabular-nums">{{ $v->vitals['weight'] }} كجم</b></span>
                            </div>
                        @endif
                        @if ($v->prescriptions->isNotEmpty())
                            <ul class="space-y-1.5">
                                @foreach ($v->prescriptions as $rx)
                                    <li class="flex items-center gap-2 text-sm"><x-icon name="pill" class="w-4 h-4 text-shifa-600" /><span class="font-medium">{{ $rx->medicine->name }}</span><span class="text-ink-mute">{{ $rx->dose }}</span></li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                </li>
            @empty
                <li class="pr-8 text-ink-mute">لسه مفيش زيارات. احجز أول ميعاد للمريض.</li>
            @endforelse
        </ol>
    @elseif ($tab === 'labs')
        <section class="panel overflow-hidden">
            <table class="table-base">
                <thead><tr><th>التحليل</th><th>التاريخ</th><th>النتيجة</th><th>المعدل الطبيعي</th><th>الحالة</th></tr></thead>
                <tbody>
                @forelse ($patient->labOrders as $o)
                    @php [$label, $color] = \App\Models\LabOrder::STATUSES[$o->status]; @endphp
                    <tr>
                        <td class="font-medium">{{ $o->test->name }} <span class="text-xs text-ink-mute" dir="ltr">{{ $o->test->code }}</span></td>
                        <td class="tabular-nums">{{ $o->created_at->format('Y/m/d') }}</td>
                        <td class="font-semibold tabular-nums" dir="ltr">{{ $o->result ? $o->result . ' ' . $o->test->unit : '—' }}</td>
                        <td class="text-ink-soft tabular-nums" dir="ltr">{{ $o->test->normal_range ?? '—' }}</td>
                        <td><x-badge :color="$color">{{ $label }}</x-badge></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-ink-mute">مفيش تحاليل للمريض ده.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>
    @else
        <section class="panel overflow-hidden">
            <table class="table-base">
                <thead><tr><th>رقم الفاتورة</th><th>التاريخ</th><th>الصافي</th><th>المدفوع</th><th>الحالة</th></tr></thead>
                <tbody>
                @forelse ($patient->invoices as $i)
                    @php [$label, $color] = \App\Models\Invoice::STATUSES[$i->status]; @endphp
                    <tr>
                        <td class="font-medium tabular-nums">{{ $i->number }}</td>
                        <td class="tabular-nums">{{ $i->date->format('Y/m/d') }}</td>
                        <td><x-money :value="$i->net" /></td>
                        <td><x-money :value="$i->paid" /></td>
                        <td><x-badge :color="$color">{{ $label }}</x-badge></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-ink-mute">مفيش فواتير للمريض ده.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>
    @endif
</div>
