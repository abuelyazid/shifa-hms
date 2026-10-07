<?php

namespace App\Livewire;

use App\Models\{Appointment, Doctor, Patient};
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\{Computed, Title, Url};
use Livewire\Component;

#[Title('المواعيد')]
class Appointments extends Component
{
    #[Url] public string $date = '';
    #[Url] public ?int $doctorFilter = null;

    // نموذج الحجز
    public bool $showForm = false;
    public string $patientSearch = '';
    public ?int $patient_id = null;
    public ?int $doctor_id = null;
    public string $bookDate = '';
    public ?string $time = null;
    public string $notes = '';

    public function mount(): void
    {
        $this->date = $this->date ?: today()->toDateString();
        $this->bookDate = $this->date;

        if ($id = request()->integer('patient')) {
            $this->openForm($id);
        }
    }

    public function shiftDay(int $days): void
    {
        $this->date = Carbon::parse($this->date)->addDays($days)->toDateString();
    }

    public function openForm(?int $patientId = null): void
    {
        $this->reset(['patientSearch', 'doctor_id', 'time', 'notes']);
        $this->patient_id = $patientId;
        $this->bookDate = $this->date;
        $this->showForm = true;
    }

    public function updatedDoctorId(): void { $this->time = null; }
    public function updatedBookDate(): void { $this->time = null; }

    #[Computed]
    public function patientResults()
    {
        if (mb_strlen($this->patientSearch) < 2) {
            return collect();
        }

        return Patient::where('name', 'like', "%{$this->patientSearch}%")
            ->orWhere('phone', 'like', "%{$this->patientSearch}%")
            ->orWhere('file_no', 'like', "%{$this->patientSearch}%")
            ->limit(5)->get();
    }

    #[Computed]
    public function slots(): array
    {
        $doctor = Doctor::find($this->doctor_id);

        return $doctor ? $doctor->availableSlots(Carbon::parse($this->bookDate)) : [];
    }

    public function book(): void
    {
        $this->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id'  => 'required|exists:doctors,id',
            'bookDate'   => 'required|date|after_or_equal:today',
            'time'       => ['required', Rule::unique('appointments')->where(fn ($q) => $q
                                ->where('doctor_id', $this->doctor_id)->whereDate('date', $this->bookDate)
                                ->where('status', '!=', 'cancelled'))],
        ], ['time.unique' => 'الميعاد ده اتحجز حالاً، اختار ميعاد تاني.', 'time.required' => 'اختار ميعاد من المتاح.'],
           ['patient_id' => 'المريض', 'doctor_id' => 'الطبيب', 'bookDate' => 'التاريخ']);

        $queue = Appointment::where('doctor_id', $this->doctor_id)->whereDate('date', $this->bookDate)->max('queue_no') + 1;

        Appointment::create([
            'patient_id' => $this->patient_id, 'doctor_id' => $this->doctor_id, 'date' => $this->bookDate,
            'time' => $this->time, 'queue_no' => $queue, 'notes' => $this->notes ?: null,
        ]);

        $this->showForm = false;
        $this->date = $this->bookDate;
        session()->flash('ok', 'تم حجز الميعاد بنجاح.');
    }

    public function setStatus(Appointment $appointment, string $status)
    {
        abort_unless(array_key_exists($status, Appointment::STATUSES), 422);
        $appointment->update(['status' => $status]);

        if ($status === 'in_progress') {
            return $this->redirectRoute('visits.show', $appointment, navigate: true);
        }
    }

    public function whatsappLink(Appointment $a): string
    {
        $msg = "أهلاً {$a->patient->name}\nتم تأكيد ميعادك في مستشفى شفاء\n"
             . "الطبيب: {$a->doctor->name}\nالتاريخ: {$a->date->format('Y/m/d')}\nالساعة: " . substr($a->time, 0, 5)
             . "\nرقم الدور: {$a->queue_no}";

        return 'https://wa.me/2' . $a->patient->phone . '?text=' . rawurlencode($msg);
    }

    public function render()
    {
        $appointments = Appointment::with(['patient', 'doctor.department'])
            ->whereDate('date', $this->date)
            ->when($this->doctorFilter, fn ($q) => $q->where('doctor_id', $this->doctorFilter))
            ->orderBy('time')->get();

        return view('livewire.appointments', [
            'appointments' => $appointments,
            'doctors'      => Doctor::with('department')->orderBy('name')->get(),
            'day'          => Carbon::parse($this->date),
            'counts'       => $appointments->countBy('status'),
        ]);
    }
}
