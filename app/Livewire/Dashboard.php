<?php

namespace App\Livewire;

use App\Models\{Appointment, Doctor, LabOrder, Medicine, Payment};
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('لوحة التحكم')]
class Dashboard extends Component
{
    public function render()
    {
        $today = Appointment::with(['patient', 'doctor.department'])->whereDate('date', today())->orderBy('time')->get();

        // لوحة الانتظار: لكل دكتور شغال النهارده، مين جوه ومين اللي عليه الدور
        $queues = Doctor::with('department')->get()
            ->filter(fn ($d) => $d->worksOn(today()))
            ->map(fn ($d) => [
                'doctor'  => $d,
                'current' => $today->where('doctor_id', $d->id)->firstWhere('status', 'in_progress'),
                'waiting' => $today->where('doctor_id', $d->id)->where('status', 'arrived')->values(),
            ]);

        $revenue = Payment::where('paid_at', '>=', today()->subDays(13))
            ->get()->groupBy(fn ($p) => $p->paid_at->toDateString())->map->sum('amount');

        $days = collect(range(13, 0))->map(fn ($i) => today()->subDays($i))
            ->map(fn ($d) => ['day' => $d, 'total' => (float) ($revenue[$d->toDateString()] ?? 0)]);

        return view('livewire.dashboard', [
            'today'      => $today,
            'queues'     => $queues,
            'days'       => $days,
            'income'     => Payment::whereDate('paid_at', today())->sum('amount'),
            'labPending' => LabOrder::where('status', '!=', 'completed')->count(),
            'lowStock'   => Medicine::whereColumn('stock', '<=', 'reorder_level')->orderBy('stock')->get(),
        ]);
    }
}
