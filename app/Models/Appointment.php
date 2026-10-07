<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['patient_id', 'doctor_id', 'date', 'time', 'queue_no', 'status', 'notes'];

    protected $casts = ['date' => 'date'];

    public const STATUSES = [
        'booked'      => ['محجوز',    'slate'],
        'arrived'     => ['في الانتظار', 'amber'],
        'in_progress' => ['عند الطبيب', 'teal'],
        'done'        => ['انتهى',    'green'],
        'cancelled'   => ['ملغي',     'red'],
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function visit()
    {
        return $this->hasOne(Visit::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status][0];
    }

    public function statusColor(): string
    {
        return self::STATUSES[$this->status][1];
    }
}
