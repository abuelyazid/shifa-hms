<?php

namespace App\Livewire;

use App\Models\{Expense, Payment};
use Carbon\Carbon;
use Livewire\Attributes\{Title, Url};
use Livewire\Component;

#[Title('الخزنة')]
class Cashbox extends Component
{
    #[Url] public string $date = '';

    public bool $showExpense = false;
    public string $title = '';
    public $amount = null;

    public function mount(): void
    {
        $this->date = $this->date ?: today()->toDateString();
    }

    public function addExpense(): void
    {
        $this->validate(['title' => 'required', 'amount' => 'required|numeric|min:1'], [], ['title' => 'البند', 'amount' => 'المبلغ']);
        Expense::create(['title' => $this->title, 'amount' => $this->amount, 'date' => $this->date, 'user_id' => auth()->id()]);
        $this->reset(['title', 'amount', 'showExpense']);
    }

    public function render()
    {
        $payments = Payment::with(['invoice.patient', 'user'])->whereDate('paid_at', $this->date)->latest('paid_at')->get();
        $expenses = Expense::whereDate('date', $this->date)->latest()->get();

        // كل الحركات في جدول واحد مرتب بالوقت
        $movements = $payments->map(fn ($p) => ['in' => true, 'title' => 'تحصيل فاتورة ' . $p->invoice->number,
                                               'who' => $p->invoice->patient->name, 'method' => $p->method,
                                               'amount' => $p->amount, 'at' => $p->paid_at])
            ->concat($expenses->map(fn ($e) => ['in' => false, 'title' => $e->title, 'who' => 'مصروفات',
                                                'method' => 'cash', 'amount' => $e->amount, 'at' => $e->created_at]))
            ->sortByDesc('at');

        return view('livewire.cashbox', [
            'movements' => $movements,
            'cash'      => $payments->where('method', 'cash')->sum('amount'),
            'card'      => $payments->where('method', 'card')->sum('amount'),
            'out'       => $expenses->sum('amount'),
            'day'       => Carbon::parse($this->date),
        ]);
    }
}
