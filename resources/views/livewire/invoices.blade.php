<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-3">
        <nav class="flex rounded-xl bg-white border border-line p-1">
            @foreach (['' => 'الكل', 'unpaid' => 'غير مدفوعة', 'partial' => 'جزئياً', 'paid' => 'مدفوعة'] as $key => $label)
                <button wire:click="$set('status', '{{ $key }}')"
                        @class(['rounded-lg px-4 py-2 text-sm font-medium transition', 'bg-shifa-600 text-white' => $status === $key, 'text-ink-soft hover:text-ink' => $status !== $key])>
                    {{ $label }} @if($key)<span class="tabular-nums opacity-70">{{ $totals[$key] ?? 0 }}</span>@endif
                </button>
            @endforeach
        </nav>
        <div class="relative flex-1 min-w-[220px] max-w-sm">
            <x-icon name="search" class="w-5 h-5 absolute right-3.5 top-1/2 -translate-y-1/2 text-ink-mute" />
            <input wire:model.live.debounce.300ms="search" type="search" class="input pr-11" placeholder="رقم الفاتورة أو اسم المريض">
        </div>
        <p class="mr-auto text-sm text-ink-soft">مستحق للتحصيل: <x-money :value="$dueTotal" class="font-bold text-rose-700" /></p>
    </div>

    <section class="panel overflow-hidden">
        <table class="table-base">
            <thead><tr><th>رقم الفاتورة</th><th>المريض</th><th>التاريخ</th><th>الصافي</th><th>المتبقي</th><th>الحالة</th><th></th></tr></thead>
            <tbody>
            @foreach ($invoices as $i)
                @php [$label, $color] = \App\Models\Invoice::STATUSES[$i->status]; @endphp
                <tr wire:key="i{{ $i->id }}">
                    <td class="font-semibold tabular-nums">{{ $i->number }}</td>
                    <td>{{ $i->patient->name }}</td>
                    <td class="tabular-nums text-ink-soft">{{ $i->date->format('Y/m/d') }}</td>
                    <td><x-money :value="$i->net" /></td>
                    <td><x-money :value="$i->due" :class="$i->due > 0 ? 'font-semibold text-rose-700' : ''" /></td>
                    <td><x-badge :color="$color">{{ $label }}</x-badge></td>
                    <td class="text-left"><button wire:click="open({{ $i->id }})" class="btn-ghost py-1.5 px-3">{{ $i->due > 0 ? 'تحصيل' : 'عرض' }}</button></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $invoices->links() }}</div>
    </section>

    <x-modal show="showInvoice" :title="$current ? 'فاتورة ' . $current->number : 'الفاتورة'" width="max-w-2xl">
        @if ($current)
            <div id="print-area" class="space-y-5">
                <div class="flex items-center justify-between">
                    <div><p class="font-semibold">{{ $current->patient->name }}</p><p class="text-sm text-ink-mute tabular-nums">{{ $current->patient->file_no }}، {{ $current->date->format('Y/m/d') }}</p></div>
                    <x-badge :color="\App\Models\Invoice::STATUSES[$current->status][1]">{{ \App\Models\Invoice::STATUSES[$current->status][0] }}</x-badge>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-line">
                        @foreach ($current->items as $item)
                            <tr><td class="py-2.5">{{ $item->description }}</td><td class="py-2.5 text-left"><x-money :value="$item->qty * $item->price" /></td></tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-ink/10">
                        <tr><td class="pt-3 text-ink-soft">الإجمالي</td><td class="pt-3 text-left"><x-money :value="$current->total" /></td></tr>
                        @if ($current->discount > 0)<tr><td class="pt-1 text-ink-soft">الخصم</td><td class="pt-1 text-left text-emerald-700">- <x-money :value="$current->discount" /></td></tr>@endif
                        <tr><td class="pt-1 text-ink-soft">المدفوع</td><td class="pt-1 text-left"><x-money :value="$current->paid" /></td></tr>
                        <tr class="text-base font-bold"><td class="pt-2">المتبقي</td><td class="pt-2 text-left"><x-money :value="$current->due" /></td></tr>
                    </tfoot>
                </table>
            </div>

            @if ($current->due > 0)
                <div class="mt-6 grid sm:grid-cols-2 gap-4 rounded-2xl bg-paper p-4">
                    <form wire:submit="addItem" class="space-y-2">
                        <p class="text-sm font-medium text-ink-soft">إضافة بند</p>
                        <input wire:model="itemDesc" class="input" placeholder="مثلاً: أشعة سينية">
                        <div class="flex gap-2"><input wire:model="itemPrice" type="number" class="input" placeholder="السعر"><button class="btn-ghost">إضافة</button></div>
                        @error('itemDesc')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror
                    </form>
                    <form wire:submit="applyDiscount" class="space-y-2">
                        <p class="text-sm font-medium text-ink-soft">خصم</p>
                        <div class="flex gap-2"><input wire:model="discount" type="number" class="input"><button class="btn-ghost">تطبيق</button></div>
                        @error('discount')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror
                    </form>
                </div>
                <form wire:submit="pay" class="mt-4 flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[160px]"><label class="label">المبلغ المحصّل</label><input wire:model="amount" type="number" class="input text-lg font-bold">@error('amount')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror</div>
                    <div class="flex rounded-xl border border-line p-1 bg-white">
                        @foreach (['cash' => 'كاش', 'card' => 'فيزا'] as $k => $l)
                            <button type="button" wire:click="$set('method', '{{ $k }}')" @class(['rounded-lg px-4 py-2 text-sm font-medium', 'bg-ink text-white' => $method === $k, 'text-ink-soft' => $method !== $k])>{{ $l }}</button>
                        @endforeach
                    </div>
                    <button class="btn-primary py-3 px-6"><x-icon name="banknote" class="w-4 h-4" />تحصيل</button>
                </form>
            @endif

            <div class="mt-5 flex justify-end">
                <button type="button" onclick="window.print()" class="btn-ghost"><x-icon name="printer" class="w-4 h-4" />طباعة</button>
            </div>
        @endif
    </x-modal>
</div>
