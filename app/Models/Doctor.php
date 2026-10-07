<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = ['department_id', 'name', 'title', 'phone', 'fee',
                           'work_days', 'start_time', 'end_time', 'slot_minutes'];

    protected $casts = ['work_days' => 'array', 'fee' => 'decimal:2'];

    public const DAYS = [6 => 'السبت', 0 => 'الأحد', 1 => 'الاثنين', 2 => 'الثلاثاء',
                         3 => 'الأربعاء', 4 => 'الخميس', 5 => 'الجمعة'];

    public const SHORT_DAYS = [6 => 'سبت', 0 => 'أحد', 1 => 'إثنين', 2 => 'ثلاثاء', 3 => 'أربعاء', 4 => 'خميس', 5 => 'جمعة'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function worksOn(Carbon $date): bool
    {
        return in_array($date->dayOfWeek, $this->work_days ?? []);
    }

    /** المواعيد المتاحة في يوم معين (بعد استبعاد المحجوز) */
    public function availableSlots(Carbon $date): array
    {
        if (! $this->worksOn($date)) {
            return [];
        }

        $booked = $this->appointments()
            ->whereDate('date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('time')
            ->map(fn ($t) => substr($t, 0, 5))
            ->all();

        $slots = [];
        $time = $date->copy()->setTimeFromTimeString($this->start_time);
        $end  = $date->copy()->setTimeFromTimeString($this->end_time);

        while ($time < $end) {
            $slots[] = [
                'time' => $time->format('H:i'),
                'free' => ! in_array($time->format('H:i'), $booked) && $time->isFuture(),
            ];
            $time->addMinutes($this->slot_minutes);
        }

        return $slots;
    }
}
