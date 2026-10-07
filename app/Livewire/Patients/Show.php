<?php

namespace App\Livewire\Patients;

use App\Models\Patient;
use Livewire\Attributes\Url;
use Livewire\Component;

class Show extends Component
{
    public Patient $patient;

    #[Url] public string $tab = 'visits';

    public function render()
    {
        $this->patient->load(['visits.doctor.department', 'visits.prescriptions.medicine', 'labOrders.test', 'invoices']);

        return view('livewire.patients.show')->title($this->patient->name);
    }
}
