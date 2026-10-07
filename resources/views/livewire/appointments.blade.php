<div class="space-y-6">
    @if (session('ok'))
        <div class="flex items-center gap-2 rounded-xl bg-emerald-50 text-emerald-700 px-4 py-3 text-sm font-medium"><x-icon name="check" class="w-4 h-4" />{{ session('ok') }}</div>
    @endif

    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center rounded-xl border border-line bg-white">
            <button wire:click="shiftDay(-1)" class="p-3 text-ink-soft hover:text-ink" aria-label="اليوم السابق"><x-icon name="chevron-left" class="w-4 h-4 rotate-180" /></button>
            <input type="date" wire:model.live="date" class="border-0 text-sm font-semibold focus:ring-0">
            <button wire:click="shiftDay(1)" class="p-3 text-ink-soft hover:text-ink" aria-label="اليوم التالي"><x-icon name="chevron-left" class="w-4 h-4" /></button>
        </div>
        <button wire:click="$set('date', '{{ today()->toDateString() }}')" class="btn-ghost">النهارده</button>
        <select wire:model.live="doctorFilter" class="input w-auto py-2.5">
            <option value="">كل الأطباء</option>
            @foreach ($doctors as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
        </select>
        <button wire:click="openForm" class="btn-primary mr-auto"><x-icon name="calendar-plus" class="w-4 h-4" />حجز ميعاد</button>
    </div>

    <p class="text-sm text-ink-soft">
        {{ $day->locale('ar')->translatedFormat('l j F') }}:
        <b class="text-ink">{{ $appointments->count() }}</b> ميعاد،
        <b class="text-saffron-700">{{ $counts['arrived'] ?? 0 }}</b> في الانتظار،
        <b class="text-emerald-700">{{ $counts['done'] ?? 0 }}</b> انتهوا
    </p>

    <section class="panel overflow-hidden">
        <table class="table-base">
            <thead><tr><th>الدور</th><th>الميعاد</th><th>المريض</th><th>الطبيب</th><th>الحالة</th><th class="text-left">الإجراء</th></tr></thead>
            <tbody>
            @forelse ($appointments as $a)
                <tr wire:key="a{{ $a->id }}" @class(['opacity-50' => $a->status === 'cancelled'])>
                    <td><span class="grid place-items-center w-9 h-9 rounded-lg bg-paper font-bold tabular-nums">{{ sprintf('%02d', $a->queue_no) }}</span></td>
                    <td class="tabular-nums font-semibold" dir="ltr">{{ substr($a->time, 0, 5) }}</td>
                    <td>
                        <a href="{{ route('patients.show', $a->patient) }}" wire:navigate class="font-medium hover:text-shifa-700">{{ $a->patient->name }}</a>
                        <p class="text-xs text-ink-mute tabular-nums" dir="ltr">{{ $a->patient->phone }}</p>
                    </td>
                    <td>
                        <p>{{ $a->doctor->name }}</p>
                        <p class="text-xs text-ink-mute">{{ $a->doctor->department->name }}</p>
                    </td>
                    <td><x-badge :color="$a->statusColor()">{{ $a->statusLabel() }}</x-badge></td>
                    <td>
                        <div class="flex items-center justify-end gap-2">
                            @switch($a->status)
                                @case('booked')
                                    <a href="{{ $this->whatsappLink($a) }}" target="_blank" class="btn-ghost py-1.5 px-3 text-emerald-700">تأكيد واتساب</a>
                                    <button wire:click="setStatus({{ $a->id }}, 'arrived')" class="btn-ghost py-1.5 px-3">وصل</button>
                                    <button wire:click="setStatus({{ $a->id }}, 'cancelled')" wire:confirm="إلغاء الميعاد؟" class="btn-danger py-1.5 px-3">إلغاء</button>
                                    @break
                                @case('arrived')
                                    <button wire:click="setStatus({{ $a->id }}, 'in_progress')" class="btn-primary py-1.5 px-3">دخول للطبيب</button>
                                    @break
                                @case('in_progress')
                                    <a href="{{ route('visits.show', $a) }}" wire:navigate class="btn-primary py-1.5 px-3">فتح الكشف</a>
                                    @break
                                @case('done')
                                    <a href="{{ route('visits.show', $a) }}" wire:navigate class="btn-ghost py-1.5 px-3">عرض الكشف</a>
                                    @break
                            @endswitch
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-14 text-center text-ink-mute">مفيش مواعيد في اليوم ده. احجز ميعاد جديد من الزرار اللي فوق.</td></tr>
            @endforelse
            </tbody>
        </table>
    </section>

    <x-modal show="showForm" title="حجز ميعاد جديد" width="max-w-2xl">
        <form wire:submit="book" class="space-y-5">
            {{-- المريض --}}
            <div>
                <label class="label">المريض</label>
                @if ($patient_id && ($p = \App\Models\Patient::find($patient_id)))
                    <div class="flex items-center justify-between rounded-xl border border-shifa-200 bg-shifa-50 px-4 py-3">
                        <div><p class="font-semibold">{{ $p->name }}</p><p class="text-xs text-ink-soft tabular-nums">{{ $p->file_no }}</p></div>
                        <button type="button" wire:click="$set('patient_id', null)" class="text-sm text-shifa-700 hover:underline">تغيير</button>
                    </div>
                @else
                    <input wire:model.live.debounce.250ms="patientSearch" class="input" placeholder="اكتب اسم المريض أو تليفونه">
                    @if ($this->patientResults->isNotEmpty())
                        <ul class="mt-2 rounded-xl border border-line divide-y divide-line overflow-hidden">
                            @foreach ($this->patientResults as $r)
                                <li><button type="button" wire:click="$set('patient_id', {{ $r->id }})" class="w-full flex justify-between px-4 py-2.5 text-sm hover:bg-paper">
                                    <span class="font-medium">{{ $r->name }}</span><span class="text-ink-mute tabular-nums" dir="ltr">{{ $r->phone }}</span></button></li>
                            @endforeach
                        </ul>
                    @endif
                @endif
                @error('patient_id') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="label">الطبيب</label>
                    <select wire:model.live="doctor_id" class="input">
                        <option value="">اختار الطبيب</option>
                        @foreach ($doctors as $d)<option value="{{ $d->id }}">{{ $d->name }} ({{ $d->department->name }})</option>@endforeach
                    </select>
                    @error('doctor_id') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">التاريخ</label>
                    <input type="date" wire:model.live="bookDate" min="{{ today()->toDateString() }}" class="input">
                </div>
            </div>

            {{-- المواعيد المتاحة --}}
            @if ($doctor_id)
                <div>
                    <label class="label">المواعيد المتاحة</label>
                    @if (empty($this->slots))
                        <p class="rounded-xl bg-paper px-4 py-3 text-sm text-ink-soft">الطبيب مش موجود في اليوم ده. اختار يوم تاني.</p>
                    @else
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2" dir="ltr">
                            @foreach ($this->slots as $s)
                                <button type="button" @disabled(! $s['free']) wire:click="$set('time', '{{ $s['time'] }}')"
                                    @class(['rounded-lg py-2 text-sm font-semibold tabular-nums border transition',
                                            'bg-shifa-600 text-white border-shifa-600' => $time === $s['time'],
                                            'bg-white border-line hover:border-shifa-400' => $s['free'] && $time !== $s['time'],
                                            'bg-paper text-ink-mute/60 border-transparent line-through cursor-not-allowed' => ! $s['free']])>{{ $s['time'] }}</button>
                            @endforeach
                        </div>
                    @endif
                    @error('time') <p class="mt-2 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>
            @endif

            <div>
                <label class="label">ملاحظات</label>
                <input wire:model="notes" class="input" placeholder="اختياري">
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" wire:click="$set('showForm', false)" class="btn-ghost">إلغاء</button>
                <button class="btn-primary">تأكيد الحجز</button>
            </div>
        </form>
    </x-modal>
</div>
