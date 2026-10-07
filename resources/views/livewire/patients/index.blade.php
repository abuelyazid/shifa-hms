<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[260px] max-w-md">
            <x-icon name="search" class="w-5 h-5 absolute right-3.5 top-1/2 -translate-y-1/2 text-ink-mute" />
            <input wire:model.live.debounce.300ms="search" type="search" class="input pr-11 py-3" placeholder="ابحث بالاسم أو رقم التليفون أو رقم الملف">
        </div>
        <button wire:click="$set('showForm', true)" class="btn-primary py-3 mr-auto"><x-icon name="plus" class="w-4 h-4" />مريض جديد</button>
    </div>

    <section class="panel overflow-hidden">
        <table class="table-base">
            <thead><tr><th>المريض</th><th>رقم الملف</th><th>التليفون</th><th>السن</th><th>فصيلة الدم</th><th>الزيارات</th><th></th></tr></thead>
            <tbody>
            @forelse ($patients as $p)
                <tr wire:key="p{{ $p->id }}">
                    <td>
                        <div class="flex items-center gap-3">
                            <span @class(['grid place-items-center w-10 h-10 rounded-full text-sm font-semibold',
                                          'bg-shifa-50 text-shifa-700' => $p->gender === 'male', 'bg-rose-50 text-rose-700' => $p->gender === 'female'])>{{ $p->initials }}</span>
                            <div>
                                <p class="font-medium">{{ $p->name }}</p>
                                <p class="text-xs text-ink-mute">{{ $p->gender === 'male' ? 'ذكر' : 'أنثى' }}، {{ $p->address }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="tabular-nums text-ink-soft">{{ $p->file_no }}</td>
                    <td class="tabular-nums" dir="ltr">{{ $p->phone }}</td>
                    <td class="tabular-nums">{{ $p->age }} سنة</td>
                    <td><span class="font-semibold text-rose-700" dir="ltr">{{ $p->blood_type }}</span></td>
                    <td class="tabular-nums">{{ $p->visits_count }}</td>
                    <td class="text-left"><a href="{{ route('patients.show', $p) }}" wire:navigate class="btn-ghost py-1.5 px-3">فتح الملف</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-14 text-center text-ink-mute">مفيش مريض بالبيانات دي. جرّب تبحث برقم التليفون، أو سجّل مريض جديد.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $patients->links() }}</div>
    </section>

    <x-modal show="showForm" title="تسجيل مريض جديد" width="max-w-2xl">
        <form wire:submit="save" class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="label">الاسم بالكامل</label>
                <input wire:model="name" class="input">
                @error('name') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">النوع</label>
                <select wire:model="gender" class="input"><option value="male">ذكر</option><option value="female">أنثى</option></select>
            </div>
            <div>
                <label class="label">رقم التليفون</label>
                <input wire:model="phone" class="input" dir="ltr" placeholder="01xxxxxxxxx">
                @error('phone') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">تاريخ الميلاد</label>
                <input wire:model="birth_date" type="date" class="input">
            </div>
            <div>
                <label class="label">الرقم القومي</label>
                <input wire:model="national_id" class="input" dir="ltr">
                @error('national_id') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">فصيلة الدم</label>
                <select wire:model="blood_type" class="input">
                    <option value="">غير معروفة</option>
                    @foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $b)<option>{{ $b }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="label">الحساسية</label>
                <input wire:model="allergies" class="input" placeholder="مثلاً: البنسلين">
            </div>
            <div class="sm:col-span-2 flex justify-end gap-2 pt-2">
                <button type="button" wire:click="$set('showForm', false)" class="btn-ghost">إلغاء</button>
                <button class="btn-primary">حفظ وفتح الملف</button>
            </div>
        </form>
    </x-modal>
</div>
