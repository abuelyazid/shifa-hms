<?php

namespace App\Livewire;

use App\Models\{Department, Doctor};
use Livewire\Attributes\{Title, Url};
use Livewire\Component;

#[Title('الأطباء والأقسام')]
class Doctors extends Component
{
    #[Url] public ?int $department = null;

    public bool $showForm = false;
    public ?int $editing = null;
    public array $form = [];

    public bool $showDept = false;
    public string $deptName = '';
    public string $deptColor = '#127C6B';

    public function create(): void
    {
        $this->editing = null;
        $this->form = ['name' => '', 'title' => 'أخصائي', 'department_id' => $this->department, 'phone' => '',
                       'fee' => 300, 'work_days' => [6, 0, 1, 2, 3], 'start_time' => '10:00', 'end_time' => '16:00', 'slot_minutes' => 20];
        $this->showForm = true;
    }

    public function edit(Doctor $doctor): void
    {
        $this->editing = $doctor->id;
        $this->form = $doctor->only(['name', 'title', 'department_id', 'phone', 'fee', 'work_days', 'slot_minutes']);
        $this->form['start_time'] = substr($doctor->start_time, 0, 5);
        $this->form['end_time'] = substr($doctor->end_time, 0, 5);
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'form.name'          => 'required|min:3',
            'form.title'         => 'required',
            'form.department_id' => 'required|exists:departments,id',
            'form.phone'         => 'nullable|digits:11',
            'form.fee'           => 'required|numeric|min:0',
            'form.work_days'     => 'required|array|min:1',
            'form.start_time'    => 'required|date_format:H:i',
            'form.end_time'      => 'required|date_format:H:i|after:form.start_time',
            'form.slot_minutes'  => 'required|integer|between:5,120',
        ], [], ['form.name' => 'الاسم', 'form.department_id' => 'القسم', 'form.work_days' => 'أيام العمل',
                'form.end_time' => 'نهاية الدوام', 'form.fee' => 'سعر الكشف', 'form.phone' => 'التليفون'])['form'];

        $data['work_days'] = array_map('intval', $data['work_days']);
        Doctor::updateOrCreate(['id' => $this->editing], $data);
        $this->showForm = false;
    }

    public function saveDepartment(): void
    {
        $this->validate(['deptName' => 'required|min:2|unique:departments,name'], [], ['deptName' => 'اسم القسم']);
        Department::create(['name' => $this->deptName, 'color' => $this->deptColor]);
        $this->reset(['deptName', 'showDept']);
    }

    public function render()
    {
        return view('livewire.doctors', [
            'departments' => Department::withCount('doctors')->get(),
            'doctors'     => Doctor::with('department')
                ->withCount(['appointments as today_count' => fn ($q) => $q->whereDate('date', today())])
                ->when($this->department, fn ($q) => $q->where('department_id', $this->department))
                ->orderBy('name')->get(),
        ]);
    }
}
