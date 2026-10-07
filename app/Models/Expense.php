<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['title', 'amount', 'date', 'user_id'];

    protected $casts = ['date' => 'date'];
}
