<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabOrder extends Model
{
    protected $fillable = ['visit_id', 'patient_id', 'lab_test_id', 'status', 'result', 'result_at'];

    protected $casts = ['result_at' => 'datetime'];

    public const STATUSES = [
        'pending'   => ['في انتظار العينة', 'amber'],
        'collected' => ['جاري التحليل', 'teal'],
        'completed' => ['النتيجة جاهزة', 'green'],
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function test()
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }
}
