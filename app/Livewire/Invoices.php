<?php

namespace App\Livewire;

use App\Models\{Invoice, Payment};
use Livewire\Attributes\{Title, Url};
use Livewire\Component;
use Livewire\WithPagination;

#[Title('الفواتير')]
class Invoices extends Component
{
    use WithPagination;

    #[Url] public string $status = '';
    #[Url] public string $search = '';

    public ?int $openId = null;
    public bool $showInvoice = false;
    public $amount = null;
    public string $method = 'cash';
    public $discount = 0;
    public string $itemDesc = '';
    public $itemPrice = null;

    public function updating($field): void
    {
        if (in_array($field, ['status', 'search'])) {
            $this->resetPage();
        }
    }

    public function open(Invoice $invoice): void
    {
        $this->openId = $invoice->id;
        $this->discount = (float) $invoice->discount;
        $this->amount = $invoice->due ?: null;
        $this->showInvoice = true;
    }

    public function addItem(): void
    {
        $this->validate(['itemDesc' => 'required', 'itemPrice' => 'required|numeric|min:1'], [], ['itemDesc' => 'البند', 'itemPrice' => 'السعر']);
        $invoice = Invoice::findOrFail($this->openId);
        $invoice->items()->create(['description' => $this->itemDesc, 'qty' => 1, 'price' => $this->itemPrice]);
        $invoice->recalculate();
        $this->reset(['itemDesc', 'itemPrice']);
        $this->amount = $invoice->due ?: null;
    }

    public function applyDiscount(): void
    {
        $invoice = Invoice::findOrFail($this->openId);
        $this->validate(['discount' => 'numeric|min:0|max:' . $invoice->total], [], ['discount' => 'الخصم']);
        $invoice->update(['discount' => $this->discount]);
        $invoice->recalculate();
        $this->amount = $invoice->due ?: null;
    }

    public function pay(): void
    {
        $invoice = Invoice::findOrFail($this->openId);
        $this->validate(['amount' => 'required|numeric|min:1|max:' . $invoice->due, 'method' => 'in:cash,card'], [], ['amount' => 'المبلغ']);

        Payment::create(['invoice_id' => $invoice->id, 'user_id' => auth()->id(), 'amount' => $this->amount,
                         'method' => $this->method, 'paid_at' => now()]);
        $invoice->recalculate();
        $this->amount = $invoice->due ?: null;
    }

    public function render()
    {
        $invoices = Invoice::with('patient')
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('number', 'like', "%{$this->search}%")
                ->orWhereHas('patient', fn ($p) => $p->where('name', 'like', "%{$this->search}%"))))
            ->latest('date')->latest('id')->paginate(12);

        return view('livewire.invoices', [
            'invoices' => $invoices,
            'current'  => $this->openId ? Invoice::with(['patient', 'items', 'payments'])->find($this->openId) : null,
            'totals'   => Invoice::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
            'dueTotal' => Invoice::where('status', '!=', 'paid')->get()->sum->due,
        ]);
    }
}
