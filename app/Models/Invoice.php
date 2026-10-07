<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['number', 'patient_id', 'visit_id', 'total', 'discount', 'paid', 'status', 'date'];

    protected $casts = ['date' => 'date', 'total' => 'decimal:2', 'discount' => 'decimal:2', 'paid' => 'decimal:2'];

    public const STATUSES = [
        'unpaid'  => ['غير مدفوعة', 'red'],
        'partial' => ['مدفوعة جزئياً', 'amber'],
        'paid'    => ['مدفوعة', 'green'],
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            $invoice->number ??= 'INV-' . now()->format('y') . str_pad((static::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
            $invoice->date ??= today();
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getNetAttribute(): float
    {
        return (float) $this->total - (float) $this->discount;
    }

    public function getDueAttribute(): float
    {
        return max(0, $this->net - (float) $this->paid);
    }

    /** يعيد حساب الإجمالي والمدفوع والحالة */
    public function recalculate(): void
    {
        $this->total = $this->items()->sum(\DB::raw('qty * price'));
        $this->paid  = $this->payments()->sum('amount');
        $this->status = match (true) {
            $this->paid <= 0          => 'unpaid',
            $this->paid >= $this->net => 'paid',
            default                   => 'partial',
        };
        $this->save();
    }
}
