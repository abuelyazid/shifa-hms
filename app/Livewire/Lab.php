<?php

namespace App\Livewire;

use App\Models\LabOrder;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('المعمل')]
class Lab extends Component
{
    public bool $showResult = false;
    public ?int $orderId = null;
    public string $result = '';

    public function collect(LabOrder $order): void
    {
        $order->update(['status' => 'collected']);
    }

    public function openResult(LabOrder $order): void
    {
        $this->orderId = $order->id;
        $this->result = (string) $order->result;
        $this->showResult = true;
    }

    public function saveResult(): void
    {
        $this->validate(['result' => 'required'], [], ['result' => 'النتيجة']);
        LabOrder::findOrFail($this->orderId)->update(['result' => $this->result, 'status' => 'completed', 'result_at' => now()]);
        $this->reset(['showResult', 'result']);
    }

    public function render()
    {
        $orders = LabOrder::with(['patient', 'test', 'visit.doctor'])
            ->where(fn ($q) => $q->where('status', '!=', 'completed')->orWhereDate('result_at', today()))
            ->oldest()->get();

        return view('livewire.lab', [
            'columns' => collect(LabOrder::STATUSES)->map(fn ($s, $key) => ['label' => $s[0], 'color' => $s[1], 'orders' => $orders->where('status', $key)]),
            'current' => $this->orderId ? LabOrder::with(['test', 'patient'])->find($this->orderId) : null,
        ]);
    }
}
