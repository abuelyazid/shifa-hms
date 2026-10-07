<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected $fillable = ['name', 'form', 'strength', 'price', 'stock', 'reorder_level', 'expiry_date'];

    protected $casts = ['expiry_date' => 'date', 'price' => 'decimal:2'];

    public function isLow(): bool
    {
        return $this->stock <= $this->reorder_level;
    }

    public function expiresSoon(): bool
    {
        return $this->expiry_date && $this->expiry_date->lte(now()->addDays(60));
    }
}
