<div class="grid xl:grid-cols-[320px_1fr] gap-6 items-start">
    {{-- ملخص المريض --}}
    <aside class="panel p-5 space-y-5 xl:sticky xl:top-24">
        <div class="flex items-center gap-3">
            <span class="grid place-items-center w-12 h-12 rounded-xl bg-shifa-50 text-shifa-700 font-bold">{{ $patient->initials }}</span>
            <div>
                <a href="{{ route('patients.show', $patient) }}" wire:navigate class="font-semibold hover:text-shifa-700">{{ $patient->name }}</a>
                <p class="text-xs text-ink-mute">{{ $patient->age }} سنة، {{ $patient->gender === 'male' ? 'ذكر' : 'أنثى' }}، <span dir="ltr">{{ $patient->blood_type }}</span></p>
            </div>
        </div>
        <div class="flex items-center justify-between rounded-xl bg-paper px-4 py-3 text-sm">
            <span class="text-ink-soft">رقم الدور</span><span class="text-2xl font-bold tabular-nums">{{ sprintf('%02d', $appointment->queue_no) }}</span>
        </div>
        @if ($patient->allergies)
            <p class="flex items-center gap-2 rounded-xl bg-rose-50 text-rose-700 px-3 py-2.5 text-sm font-medium"><x-icon name="alert-triangle" class="w-4 h-4" />{{ $patient->allergies }}</p>
        @endif
        <div>
            <h3 class="text-sm text-ink-soft mb-3">زيارات سابقة</h3>
            <ul class="space-y-3">
                @forelse ($history as $h)
                    <li class="text-sm border-r-2 border-shifa-200 pr-3">
                        <p class="font-medium">{{ $h->diagnosis }}</p>
                        <p class="text-xs text-ink-mute">{{ $h->created_at->format('Y/m/d') }}، {{ $h->doctor->name }}</p>
                    </li>
                @empty
                    <li class="text-sm text-ink-mute">أول زيارة للمريض.</li>
                @endforelse
            </ul>
        </div>
    </aside>

    {{-- الكشف --}}
    <form wire:submit="finish" class="space-y-6">
        <section class="panel p-5">
            <h2 class="text-base mb-4">العلامات الحيوية</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach (['bp' => ['الضغط', 'heart-pulse', '120/80'], 'pulse' => ['النبض', 'activity', '80'], 'temp' => ['الحرارة', 'thermometer', '37'], 'weight' => ['الوزن', 'scale', 'كجم']] as $key => [$label, $icon, $ph])
                    <label class="block">
                        <span class="label flex items-center gap-1.5"><x-icon :name="$icon" class="w-4 h-4 text-shifa-600" />{{ $label }}</span>
                        <input wire:model="vitals.{{ $key }}" class="input text-center font-semibold tabular-nums" dir="ltr" placeholder="{{ $ph }}">
                        @error("vitals.$key")<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                    </label>
                @endforeach
            </div>
        </section>

        <section class="panel p-5 grid md:grid-cols-2 gap-4">
            <div><label class="label">الشكوى</label><textarea wire:model="complaint" rows="3" class="input" placeholder="المريض بيشتكي من..."></textarea></div>
            <div><label class="label">التشخيص</label><textarea wire:model="diagnosis" rows="3" class="input"></textarea>@error('diagnosis')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <h2 class="text-base flex items-center gap-2"><x-icon name="pill" class="w-5 h-5 text-shifa-600" />الروشتة</h2>
                <button type="button" wire:click="addRx" class="btn-ghost py-1.5 px-3"><x-icon name="plus" class="w-4 h-4" />دواء</button>
            </div>
            <div class="p-5 space-y-3">
                @foreach ($rx as $i => $r)
                    <div wire:key="rx{{ $i }}" class="grid grid-cols-[1fr_1fr_90px_auto] gap-3 items-start">
                        <select wire:model="rx.{{ $i }}.medicine_id" class="input">
                            <option value="">اختار الدواء</option>
                            @foreach ($medicines as $m)<option value="{{ $m->id }}" @disabled($m->stock < 1)>{{ $m->name }} {{ $m->strength }}{{ $m->stock < 1 ? ' (غير متوفر)' : '' }}</option>@endforeach
                        </select>
                        <div><input wire:model="rx.{{ $i }}.dose" class="input" placeholder="الجرعة: قرص كل 8 ساعات">@error("rx.$i.dose")<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror</div>
                        <input type="number" min="1" wire:model="rx.{{ $i }}.quantity" class="input text-center" title="الكمية">
                        <button type="button" wire:click="removeRx({{ $i }})" class="p-2.5 rounded-xl text-ink-mute hover:bg-rose-50 hover:text-rose-700" aria-label="حذف"><x-icon name="trash-2" class="w-4 h-4" /></button>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="panel p-5">
            <h2 class="text-base flex items-center gap-2 mb-4"><x-icon name="flask-conical" class="w-5 h-5 text-shifa-600" />طلب تحاليل</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-2">
                @foreach ($tests as $t)
                    <label class="flex items-center gap-2.5 rounded-xl border border-line px-3 py-2.5 text-sm cursor-pointer has-[:checked]:border-shifa-500 has-[:checked]:bg-shifa-50">
                        <input type="checkbox" wire:model="labs" value="{{ $t->id }}" class="rounded border-line text-shifa-600 focus:ring-shifa-500">
                        <span class="flex-1">{{ $t->name }}</span><span class="text-xs text-ink-mute" dir="ltr">{{ $t->code }}</span>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="panel p-5"><label class="label">ملاحظات للمريض</label><textarea wire:model="notes" rows="2" class="input" placeholder="مثلاً: متابعة بعد أسبوعين"></textarea></section>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('appointments') }}" wire:navigate class="btn-ghost">رجوع</a>
            <button class="btn-primary px-6"><x-icon name="check" class="w-4 h-4" />إنهاء الكشف</button>
        </div>
    </form>
</div>
