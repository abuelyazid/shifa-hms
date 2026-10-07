<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <nav class="flex rounded-xl bg-white border border-line p-1">
            <button wire:click="$set('tab', 'dispense')" @class(['rounded-lg px-4 py-2 text-sm font-medium', 'bg-shifa-600 text-white' => $tab === 'dispense', 'text-ink-soft' => $tab !== 'dispense'])>روشتات للصرف <span class="tabular-nums opacity-70">{{ $pending->count() }}</span></button>
            <button wire:click="$set('tab', 'stock')" @class(['rounded-lg px-4 py-2 text-sm font-medium', 'bg-shifa-600 text-white' => $tab === 'stock', 'text-ink-soft' => $tab !== 'stock'])>المخزون</button>
        </nav>
        @if ($tab === 'stock')
            <input wire:model.live.debounce.300ms="search" type="search" class="input w-64" placeholder="ابحث عن دواء">
            <button wire:click="newMedicine" class="btn-primary mr-auto"><x-icon name="plus" class="w-4 h-4" />دواء جديد</button>
        @endif
    </div>
    @error('stock')<p class="rounded-xl bg-rose-50 text-rose-700 px-4 py-3 text-sm font-medium">{{ $message }}</p>@enderror

    @if ($tab === 'dispense')
        <div class="grid lg:grid-cols-2 2xl:grid-cols-3 gap-4">
            @forelse ($pending as $v)
                <article wire:key="v{{ $v->id }}" class="panel flex flex-col">
                    <header class="px-5 pt-5 pb-4 border-b border-dashed border-line">
                        <p class="font-semibold">{{ $v->patient->name }}</p>
                        <p class="text-xs text-ink-mute">{{ $v->doctor->name }}، {{ $v->created_at->locale('ar')->diffForHumans() }}</p>
                    </header>
                    <ul class="px-5 py-4 space-y-3 flex-1">
                        @foreach ($v->prescriptions as $rx)
                            <li class="flex items-start gap-3">
                                <x-icon name="pill" class="w-4 h-4 mt-1 text-shifa-600" />
                                <div class="flex-1"><p class="text-sm font-medium">{{ $rx->medicine->name }} <span class="text-ink-mute" dir="ltr">{{ $rx->medicine->strength }}</span></p><p class="text-xs text-ink-soft">{{ $rx->dose }}</p></div>
                                <span class="text-sm font-semibold tabular-nums">×{{ $rx->quantity }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="px-5 pb-5"><button wire:click="dispense({{ $v->id }})" class="btn-primary w-full"><x-icon name="check" class="w-4 h-4" />صرف الروشتة</button></div>
                </article>
            @empty
                <p class="text-ink-mute">مفيش روشتات مستنية صرف.</p>
            @endforelse
        </div>
    @else
        <section class="panel overflow-hidden">
            <table class="table-base">
                <thead><tr><th>الدواء</th><th>الشكل</th><th>السعر</th><th class="w-64">المخزون</th><th>الصلاحية</th><th></th></tr></thead>
                <tbody>
                @foreach ($medicines as $m)
                    <tr wire:key="m{{ $m->id }}">
                        <td class="font-medium">{{ $m->name }} <span class="text-ink-mute font-normal" dir="ltr">{{ $m->strength }}</span></td>
                        <td class="text-ink-soft">{{ $m->form }}</td>
                        <td><x-money :value="$m->price" /></td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 h-2 rounded-full bg-paper overflow-hidden"><div @class(['h-full rounded-full', 'bg-rose-500' => $m->isLow(), 'bg-shifa-500' => ! $m->isLow()]) style="width: {{ min(100, $m->stock / 3) }}%"></div></div>
                                <span @class(['text-sm font-semibold tabular-nums w-10', 'text-rose-700' => $m->isLow()])>{{ $m->stock }}</span>
                            </div>
                        </td>
                        <td @class(['tabular-nums', 'text-saffron-700 font-semibold' => $m->expiresSoon(), 'text-ink-soft' => ! $m->expiresSoon()])>{{ $m->expiry_date?->format('Y/m') }}</td>
                        <td class="text-left"><button wire:click="openStock({{ $m->id }})" class="btn-ghost py-1.5 px-3">إضافة كمية</button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    @endif

    <x-modal show="showStock" :title="'إضافة كمية: ' . ($stockMed->name ?? '')">
        <form wire:submit="addStock" class="space-y-4">
            <p class="text-sm text-ink-soft">المتاح حالياً: <b class="tabular-nums">{{ $stockMed->stock ?? 0 }}</b></p>
            <div><label class="label">الكمية الواردة</label><input wire:model="stockQty" type="number" class="input">@error('stockQty')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
            <div class="flex justify-end gap-2"><button type="button" wire:click="$set('showStock', false)" class="btn-ghost">إلغاء</button><button class="btn-primary">إضافة</button></div>
        </form>
    </x-modal>

    <x-modal show="showMed" title="دواء جديد" width="max-w-xl">
        <form wire:submit="saveMedicine" class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2"><label class="label">اسم الدواء</label><input wire:model="med.name" class="input">@error('med.name')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
            <div><label class="label">الشكل</label><select wire:model="med.form" class="input">@foreach (['أقراص','كبسولات','شراب','حقن','نقط','كريم'] as $f)<option>{{ $f }}</option>@endforeach</select></div>
            <div><label class="label">التركيز</label><input wire:model="med.strength" class="input" dir="ltr" placeholder="500mg"></div>
            <div><label class="label">السعر</label><input wire:model="med.price" type="number" class="input">@error('med.price')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
            <div><label class="label">الكمية</label><input wire:model="med.stock" type="number" class="input"></div>
            <div><label class="label">حد إعادة الطلب</label><input wire:model="med.reorder_level" type="number" class="input"></div>
            <div><label class="label">تاريخ الصلاحية</label><input wire:model="med.expiry_date" type="date" class="input">@error('med.expiry_date')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2 flex justify-end gap-2"><button type="button" wire:click="$set('showMed', false)" class="btn-ghost">إلغاء</button><button class="btn-primary">حفظ</button></div>
        </form>
    </x-modal>
</div>
