<?php

namespace App\Livewire;

use App\Models\{Medicine, Prescription, Visit};
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\{Title, Url};
use Livewire\Component;

#[Title('الصيدلية')]
class Pharmacy extends Component
{
    #[Url] public string $tab = 'dispense';
    public string $search = '';

    public bool $showStock = false;
    public ?int $stockId = null;
    public $stockQty = null;

    public bool $showMed = false;
    public array $med = [];

    public function dispense(Visit $visit): void
    {
        DB::transaction(function () use ($visit) {
            foreach ($visit->prescriptions()->whereNull('dispensed_at')->with('medicine')->get() as $rx) {
                if ($rx->medicine->stock < $rx->quantity) {
                    $this->addError('stock', "الكمية المتاحة من {$rx->medicine->name} مش كفاية.");
                    return;
                }
                $rx->medicine->decrement('stock', $rx->quantity);
                $rx->update(['dispensed_at' => now()]);
            }
        });
    }

    public function openStock(Medicine $medicine): void
    {
        $this->stockId = $medicine->id;
        $this->stockQty = null;
        $this->showStock = true;
    }

    public function addStock(): void
    {
        $this->validate(['stockQty' => 'required|integer|min:1'], [], ['stockQty' => 'الكمية']);
        Medicine::findOrFail($this->stockId)->increment('stock', $this->stockQty);
        $this->showStock = false;
    }

    public function newMedicine(): void
    {
        $this->med = ['name' => '', 'form' => 'أقراص', 'strength' => '', 'price' => null, 'stock' => 0, 'reorder_level' => 20, 'expiry_date' => null];
        $this->showMed = true;
    }

    public function saveMedicine(): void
    {
        $data = $this->validate([
            'med.name' => 'required', 'med.form' => 'required', 'med.strength' => 'nullable',
            'med.price' => 'required|numeric|min:0', 'med.stock' => 'required|integer|min:0',
            'med.reorder_level' => 'required|integer|min:0', 'med.expiry_date' => 'nullable|date|after:today',
        ], [], ['med.name' => 'اسم الدواء', 'med.price' => 'السعر', 'med.expiry_date' => 'تاريخ الصلاحية'])['med'];
        Medicine::create($data);
        $this->showMed = false;
    }

    public function render()
    {
        return view('livewire.pharmacy', [
            'pending'   => Visit::with(['patient', 'doctor', 'prescriptions' => fn ($q) => $q->whereNull('dispensed_at')->with('medicine')])
                ->whereHas('prescriptions', fn ($q) => $q->whereNull('dispensed_at'))->latest()->get(),
            'medicines' => Medicine::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderByRaw('stock <= reorder_level desc')->orderBy('name')->get(),
            'stockMed'  => $this->stockId ? Medicine::find($this->stockId) : null,
        ]);
    }
}
