<?php

namespace App\Livewire;

use App\Models\{Appointment, Invoice, LabTest, Medicine, Visit};
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class VisitWorkspace extends Component
{
    public Appointment $appointment;

    public array $vitals = ['bp' => '', 'pulse' => '', 'temp' => '', 'weight' => ''];
    public string $complaint = '';
    public string $diagnosis = '';
    public string $notes = '';
    public array $rx = [];        // [{medicine_id, dose, quantity}]
    public array $labs = [];      // [lab_test_id, ...]

    public function mount(Appointment $appointment): void
    {
        $this->appointment = $appointment->load(['patient', 'doctor.department']);

        if ($visit = $appointment->visit) {
            $this->vitals = array_merge($this->vitals, $visit->vitals ?? []);
            $this->fill($visit->only(['complaint', 'diagnosis']));
            $this->notes = (string) $visit->notes;
            $this->rx = $visit->prescriptions->map->only(['medicine_id', 'dose', 'quantity'])->all();
            $this->labs = $visit->labOrders->pluck('lab_test_id')->all();
        }

        if (empty($this->rx)) {
            $this->addRx();
        }
    }

    public function addRx(): void
    {
        $this->rx[] = ['medicine_id' => '', 'dose' => '', 'quantity' => 1];
    }

    public function removeRx(int $i): void
    {
        unset($this->rx[$i]);
        $this->rx = array_values($this->rx);
    }

    public function finish()
    {
        $this->rx = array_values(array_filter($this->rx, fn ($r) => $r['medicine_id']));

        $this->validate([
            'diagnosis'          => 'required|min:3',
            'vitals.pulse'       => 'nullable|integer|between:30,220',
            'vitals.temp'        => 'nullable|numeric|between:34,43',
            'rx.*.medicine_id'   => 'exists:medicines,id',
            'rx.*.dose'          => 'required',
            'rx.*.quantity'      => 'integer|min:1',
            'labs.*'             => 'exists:lab_tests,id',
        ], ['rx.*.dose.required' => 'اكتب الجرعة لكل دواء.'], ['diagnosis' => 'التشخيص']);

        DB::transaction(function () {
            $a = $this->appointment;

            $visit = Visit::updateOrCreate(['appointment_id' => $a->id], [
                'patient_id' => $a->patient_id, 'doctor_id' => $a->doctor_id,
                'vitals' => $this->vitals, 'complaint' => $this->complaint,
                'diagnosis' => $this->diagnosis, 'notes' => $this->notes,
            ]);

            // الروشتة
            $visit->prescriptions()->whereNull('dispensed_at')->delete();
            foreach ($this->rx as $r) {
                $visit->prescriptions()->create($r);
            }

            // طلبات التحاليل الجديدة فقط
            $existing = $visit->labOrders()->pluck('lab_test_id')->all();
            foreach (array_diff($this->labs, $existing) as $testId) {
                $visit->labOrders()->create(['patient_id' => $a->patient_id, 'lab_test_id' => $testId]);
            }

            // الفاتورة: الكشف + التحاليل
            $invoice = Invoice::firstOrCreate(['visit_id' => $visit->id], ['patient_id' => $a->patient_id]);
            $invoice->items()->delete();
            $invoice->items()->create(['description' => 'كشف ' . $a->doctor->department->name, 'qty' => 1, 'price' => $a->doctor->fee]);
            foreach (LabTest::whereIn('id', $this->labs)->get() as $test) {
                $invoice->items()->create(['description' => 'تحليل ' . $test->name, 'qty' => 1, 'price' => $test->price]);
            }
            $invoice->recalculate();

            $a->update(['status' => 'done']);
        });

        session()->flash('ok', 'تم حفظ الكشف وإرسال الفاتورة للخزنة.');

        return $this->redirectRoute('appointments', navigate: true);
    }

    public function render()
    {
        return view('livewire.visit-workspace', [
            'patient'   => $this->appointment->patient,
            'history'   => $this->appointment->patient->visits()->with('doctor')->where('appointment_id', '!=', $this->appointment->id)->take(4)->get(),
            'medicines' => Medicine::orderBy('name')->get(),
            'tests'     => LabTest::orderBy('name')->get(),
        ])->title('كشف: ' . $this->appointment->patient->name);
    }
}
