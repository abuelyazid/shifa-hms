<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $fillable = ['visit_id', 'medicine_id', 'dose', 'quantity', 'dispensed_at'];

    protected $casts = ['dispensed_at' => 'datetime'];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
