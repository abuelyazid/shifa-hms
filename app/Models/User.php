<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'doctor_id'];
    protected $hidden = ['password', 'remember_token'];

    public const ROLES = [
        'admin'      => 'مدير النظام',
        'doctor'     => 'طبيب',
        'reception'  => 'استقبال',
        'cashier'    => 'خزنة',
        'pharmacist' => 'صيدلي',
        'lab'        => 'معمل',
    ];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function roleName(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function can_access(string ...$roles): bool
    {
        return $this->role === 'admin' || in_array($this->role, $roles);
    }
}
