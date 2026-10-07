<?php

namespace App\Livewire\Patients;

use App\Models\Patient;
use Livewire\Attributes\{Title, Url, Validate};
use Livewire\Component;
use Livewire\WithPagination;

#[Title('المرضى')]
class Index extends Component
{
    use WithPagination;

    #[Url] public string $search = '';
    public bool $showForm = false;

    #[Validate('required|min:3')]          public string $name = '';
    #[Validate('required|in:male,female')] public string $gender = 'male';
    #[Validate('required|digits:11')]      public string $phone = '';
    #[Validate('nullable|date|before:today')] public ?string $birth_date = null;
    #[Validate('nullable|digits:14')]      public ?string $national_id = null;
    #[Validate('nullable')]                public ?string $blood_type = null;
    #[Validate('nullable|max:255')]        public ?string $allergies = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function save()
    {
        $patient = Patient::create($this->validate());
        $this->reset(['name', 'phone', 'birth_date', 'national_id', 'blood_type', 'allergies', 'showForm']);

        return $this->redirectRoute('patients.show', $patient, navigate: true);
    }

    public function render()
    {
        $patients = Patient::query()
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")
                ->orWhere('file_no', 'like', "%{$this->search}%")))
            ->withCount('visits')
            ->latest()
            ->paginate(12);

        return view('livewire.patients.index', compact('patients'));
    }
}
