<div class="space-y-6">
    {{-- الأقسام --}}
    <div class="flex flex-wrap items-center gap-2">
        <button wire:click="$set('department', null)"
                @class(['rounded-full px-4 py-2 text-sm font-medium border transition',
                        'bg-ink text-white border-ink' => ! $department, 'bg-white border-line text-ink-soft hover:border-ink-mute' => $department])>
            كل الأقسام <span class="tabular-nums opacity-70">{{ $departments->sum('doctors_count') }}</span>
        </button>
        @foreach ($departments as $d)
            <button wire:click="$set('department', {{ $d->id }})" wire:key="d{{ $d->id }}"
                    @class(['flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium border transition',
                            'bg-ink text-white border-ink' => $department === $d->id, 'bg-white border-line text-ink-soft hover:border-ink-mute' => $department !== $d->id])>
                <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $d->color }}"></span>{{ $d->name }}
                <span class="tabular-nums opacity-70">{{ $d->doctors_count }}</span>
            </button>
        @endforeach
        <button wire:click="$set('showDept', true)" class="rounded-full px-4 py-2 text-sm font-medium text-shifa-700 hover:bg-shifa-50">+ قسم جديد</button>
        <button wire:click="create" class="btn-primary mr-auto"><x-icon name="plus" class="w-4 h-4" />إضافة طبيب</button>
    </div>

    {{-- الأطباء --}}
    <div class="grid md:grid-cols-2 2xl:grid-cols-3 gap-4">
        @foreach ($doctors as $doc)
            <article wire:key="doc{{ $doc->id }}" class="panel p-5 flex flex-col">
                <div class="flex items-start gap-4">
                    <span class="grid place-items-center w-14 h-14 rounded-2xl text-white" style="background: {{ $doc->department->color }}">
                        <x-icon name="stethoscope" class="w-6 h-6" />
                    </span>
                    <div class="flex-1">
                        <h3 class="text-base">{{ $doc->name }}</h3>
                        <p class="text-sm text-ink-soft">{{ $doc->title }} {{ $doc->department->name }}</p>
                    </div>
                    <button wire:click="edit({{ $doc->id }})" class="p-2 rounded-lg text-ink-mute hover:bg-paper hover:text-ink" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                </div>

                <div class="flex gap-1 mt-5" aria-label="أيام العمل">
                    @foreach (\App\Models\Doctor::SHORT_DAYS as $num => $name)
                        <span @class(['flex-1 text-center rounded-md py-1.5 text-[11px]',
                                      'bg-shifa-50 text-shifa-700 font-semibold' => in_array($num, $doc->work_days),
                                      'bg-paper text-ink-mute/60' => ! in_array($num, $doc->work_days)])>{{ $name }}</span>
                    @endforeach
                </div>

                <dl class="grid grid-cols-3 gap-3 mt-5 pt-4 border-t border-line text-sm">
                    <div><dt class="text-xs text-ink-mute">الدوام</dt><dd class="font-semibold tabular-nums mt-0.5" dir="ltr">{{ substr($doc->start_time, 0, 5) }} - {{ substr($doc->end_time, 0, 5) }}</dd></div>
                    <div><dt class="text-xs text-ink-mute">الكشف</dt><dd class="font-semibold mt-0.5"><x-money :value="$doc->fee" /></dd></div>
                    <div><dt class="text-xs text-ink-mute">مواعيد النهارده</dt><dd class="font-semibold tabular-nums mt-0.5">{{ $doc->today_count }}</dd></div>
                </dl>
            </article>
        @endforeach
    </div>

    <x-modal show="showForm" :title="$editing ? 'تعديل بيانات الطبيب' : 'إضافة طبيب'" width="max-w-2xl">
        <form wire:submit="save" class="grid sm:grid-cols-2 gap-4">
            <div><label class="label">الاسم</label><input wire:model="form.name" class="input" placeholder="د. ...">@error('form.name')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
            <div><label class="label">الدرجة</label><select wire:model="form.title" class="input"><option>أخصائي</option><option>استشاري</option><option>طبيب مقيم</option></select></div>
            <div><label class="label">القسم</label>
                <select wire:model="form.department_id" class="input"><option value="">اختار القسم</option>@foreach ($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
                @error('form.department_id')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
            <div><label class="label">التليفون</label><input wire:model="form.phone" class="input" dir="ltr">@error('form.phone')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2">
                <label class="label">أيام العمل</label>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Models\Doctor::DAYS as $num => $name)
                        <label class="flex items-center gap-2 rounded-lg border border-line px-3 py-2 text-sm has-[:checked]:border-shifa-500 has-[:checked]:bg-shifa-50">
                            <input type="checkbox" wire:model="form.work_days" value="{{ $num }}" class="rounded border-line text-shifa-600 focus:ring-shifa-500">{{ $name }}
                        </label>
                    @endforeach
                </div>
                @error('form.work_days')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div><label class="label">بداية الدوام</label><input type="time" wire:model="form.start_time" class="input"></div>
            <div><label class="label">نهاية الدوام</label><input type="time" wire:model="form.end_time" class="input">@error('form.end_time')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
            <div><label class="label">مدة الكشف (دقيقة)</label><input type="number" wire:model="form.slot_minutes" class="input"></div>
            <div><label class="label">سعر الكشف</label><input type="number" wire:model="form.fee" class="input"></div>
            <div class="sm:col-span-2 flex justify-end gap-2 pt-2">
                <button type="button" wire:click="$set('showForm', false)" class="btn-ghost">إلغاء</button>
                <button class="btn-primary">حفظ</button>
            </div>
        </form>
    </x-modal>

    <x-modal show="showDept" title="قسم جديد">
        <form wire:submit="saveDepartment" class="space-y-4">
            <div><label class="label">اسم القسم</label><input wire:model="deptName" class="input">@error('deptName')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
            <div><label class="label">اللون</label><input type="color" wire:model="deptColor" class="h-11 w-24 rounded-xl border border-line"></div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="$set('showDept', false)" class="btn-ghost">إلغاء</button><button class="btn-primary">إضافة القسم</button></div>
        </form>
    </x-modal>
</div>
