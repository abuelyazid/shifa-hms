<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = ['file_no', 'name', 'gender', 'birth_date', 'phone',
                           'national_id', 'blood_type', 'allergies', 'address'];

    protected $casts = ['birth_date' => 'date'];

    protected static function booted(): void
    {
        static::creating(function (Patient $patient) {
            $patient->file_no ??= 'P-' . str_pad((static::max('id') ?? 0) + 1001, 5, '0', STR_PAD_LEFT);
        });
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class)->latest();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class)->latest('date');
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class)->latest();
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/u', trim($this->name));
        return mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1);
    }
}
