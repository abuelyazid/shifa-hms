<?php

namespace Database\Seeders;

use App\Models\{Appointment, Department, Doctor, Expense, Invoice, LabOrder, LabTest, Medicine, Patient, Payment, Prescription, User, Visit};
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(42);

        // ---------- الأقسام والأطباء ----------
        $departments = collect([
            ['الباطنة', '#127C6B'], ['الأطفال', '#E9A23B'], ['العظام', '#5B6CD9'],
            ['النساء والتوليد', '#D9677A'], ['القلب', '#C9473F'], ['الأنف والأذن', '#3A9BC8'],
        ])->map(fn ($d) => Department::create(['name' => $d[0], 'color' => $d[1]]));

        $doctorRows = [
            ['د. أحمد عبد الرحمن', 'استشاري', 0, 350, [6, 0, 1, 2, 3]],
            ['د. منى الشريف', 'أخصائي', 1, 300, [6, 1, 3]],
            ['د. كريم فؤاد', 'استشاري', 2, 400, [0, 2, 4]],
            ['د. سارة محمود', 'أخصائي', 3, 350, [6, 0, 2, 3]],
            ['د. هشام النجار', 'استشاري', 4, 500, [6, 1, 3, 4]],
            ['د. ياسمين عادل', 'أخصائي', 5, 280, [0, 1, 2, 3, 4]],
            ['د. عمر سليمان', 'أخصائي', 0, 250, [6, 0, 1, 2, 3, 4]],
        ];
        $doctors = collect($doctorRows)->map(fn ($d) => Doctor::create([
            'department_id' => $departments[$d[2]]->id, 'name' => $d[0], 'title' => $d[1], 'fee' => $d[3],
            'work_days' => $d[4], 'phone' => '010' . mt_rand(10000000, 99999999),
            'start_time' => '10:00', 'end_time' => '16:00', 'slot_minutes' => 20,
        ]));

        // ---------- المستخدمين ----------
        User::create(['name' => 'محمود', 'email' => 'admin@shifa.test', 'password' => 'password', 'role' => 'admin']);
        User::create(['name' => 'نورا', 'email' => 'reception@shifa.test', 'password' => 'password', 'role' => 'reception']);
        User::create(['name' => 'خالد', 'email' => 'cashier@shifa.test', 'password' => 'password', 'role' => 'cashier']);
        User::create(['name' => 'د. أحمد', 'email' => 'doctor@shifa.test', 'password' => 'password', 'role' => 'doctor', 'doctor_id' => $doctors[0]->id]);
        User::create(['name' => 'ريم', 'email' => 'pharmacy@shifa.test', 'password' => 'password', 'role' => 'pharmacist']);
        User::create(['name' => 'مصطفى', 'email' => 'lab@shifa.test', 'password' => 'password', 'role' => 'lab']);

        // ---------- المرضى ----------
        $male = ['محمد', 'أحمد', 'محمود', 'علي', 'حسن', 'يوسف', 'عمرو', 'إبراهيم', 'مصطفى', 'خالد', 'طارق', 'سامح', 'وليد', 'عادل', 'ياسر'];
        $female = ['فاطمة', 'مريم', 'نور', 'هدى', 'سلمى', 'آية', 'دينا', 'رحاب', 'شيماء', 'إيمان', 'منة', 'هبة', 'سارة', 'ليلى', 'أسماء'];
        $last = ['السيد', 'عبد الله', 'حسين', 'الشافعي', 'مرسي', 'عثمان', 'رمضان', 'سالم', 'الجمال', 'منصور', 'فرج', 'زكي', 'شاكر', 'النمر', 'بدوي'];
        $blood = ['A+', 'O+', 'B+', 'AB+', 'O-', 'A-'];
        $areas = ['المنصورة', 'طنطا', 'الزقازيق', 'مدينة نصر', 'المعادي', 'الهرم', 'شبين الكوم', 'بنها'];

        $patients = collect(range(1, 60))->map(function ($i) use ($male, $female, $last, $blood, $areas) {
            $g = $i % 2 ? 'male' : 'female';
            $first = $g === 'male' ? $male[array_rand($male)] : $female[array_rand($female)];
            return Patient::create([
                'name' => $first . ' ' . $male[array_rand($male)] . ' ' . $last[array_rand($last)],
                'gender' => $g, 'phone' => '01' . [0, 1, 2, 5][mt_rand(0, 3)] . mt_rand(10000000, 99999999),
                'birth_date' => now()->subYears(mt_rand(0, 6) ? mt_rand(18, 75) : mt_rand(2, 12))->subDays(mt_rand(0, 360)),
                'blood_type' => $blood[mt_rand(0, count($blood) - 1)], 'address' => $areas[mt_rand(0, count($areas) - 1)],
                'allergies' => mt_rand(0, 6) === 0 ? 'حساسية من البنسلين' : null,
                'national_id' => (string) mt_rand(2, 3) . mt_rand(1000000, 9999999) . mt_rand(100000, 999999),
            ]);
        });

        // ---------- الصيدلية والمعمل ----------
        $meds = collect([
            ['بانادول إكسترا', 'أقراص', '500mg', 45, 340], ['أوجمنتين', 'أقراص', '1g', 120, 18], ['كونكور', 'أقراص', '5mg', 95, 75],
            ['جلوكوفاج', 'أقراص', '850mg', 38, 210], ['فولتارين', 'حقن', '75mg', 28, 12], ['زيرتك', 'شراب', '5mg/5ml', 52, 64],
            ['نيكسيوم', 'كبسولات', '40mg', 160, 90], ['كتافلام', 'أقراص', '50mg', 42, 150], ['أموكسيل', 'شراب', '250mg', 35, 9],
            ['ليبيتور', 'أقراص', '20mg', 210, 40], ['فيتامين د', 'نقط', '2800IU', 60, 25], ['ميوفين', 'كبسولات', '200mg', 48, 120],
        ])->map(fn ($m) => Medicine::create([
            'name' => $m[0], 'form' => $m[1], 'strength' => $m[2], 'price' => $m[3], 'stock' => $m[4],
            'reorder_level' => 20, 'expiry_date' => now()->addDays(mt_rand(30, 700)),
        ]));

        $tests = collect([
            ['صورة دم كاملة', 'CBC', 150, null, null], ['سكر صائم', 'FBS', 60, 'mg/dL', '70 - 100'],
            ['وظائف كبد', 'LFT', 280, null, null], ['وظائف كلى', 'KFT', 250, null, null],
            ['دهون ثلاثية', 'TG', 90, 'mg/dL', '< 150'], ['هيموجلوبين سكري', 'HbA1c', 220, '%', '4 - 5.6'],
            ['فيتامين د', 'VITD', 450, 'ng/mL', '30 - 100'], ['غدة درقية', 'TSH', 200, 'mIU/L', '0.4 - 4'],
        ])->map(fn ($t) => LabTest::create(['name' => $t[0], 'code' => $t[1], 'price' => $t[2], 'unit' => $t[3], 'normal_range' => $t[4]]));

        // الشكوى والتشخيص حسب القسم
        $complaints = [
            'الباطنة' => [['صداع مستمر وارتفاع في الضغط', 'ارتفاع ضغط الدم'], ['ألم في المعدة بعد الأكل', 'ارتجاع المريء'], ['عطش وكثرة التبول', 'سكري من النوع الثاني']],
            'الأطفال' => [['كحة وسخونية من 3 أيام', 'التهاب الشعب الهوائية'], ['إسهال وقيء', 'نزلة معوية'], ['متابعة تطعيمات', 'نمو طبيعي']],
            'العظام' => [['ألم في الركبة عند الحركة', 'خشونة الركبة'], ['ألم أسفل الظهر', 'شد عضلي'], ['تورم في الكاحل بعد إصابة', 'التواء الكاحل']],
            'النساء والتوليد' => [['متابعة حمل', 'حمل طبيعي - الأسبوع 24'], ['ألم أسفل البطن', 'تكيس المبايض'], ['متابعة حمل', 'حمل طبيعي - الأسبوع 32']],
            'القلب' => [['خفقان وضيق تنفس', 'اضطراب نظم القلب'], ['ألم في الصدر مع المجهود', 'ذبحة صدرية مستقرة'], ['تورم في القدمين', 'قصور في عضلة القلب']],
            'الأنف والأذن' => [['التهاب في الحلق وصعوبة البلع', 'التهاب اللوزتين'], ['انسداد في الأنف وصداع', 'التهاب الجيوب الأنفية'], ['ألم في الأذن', 'التهاب الأذن الوسطى']],
        ];

        // ---------- المواعيد والزيارات (آخر 30 يوم + النهارده) ----------
        $cashier = User::where('role', 'cashier')->first();
        for ($day = 30; $day >= 0; $day--) {
            $date = today()->subDays($day);
            foreach ($doctors as $doctor) {
                if (! $doctor->worksOn($date)) {
                    continue;
                }
                $slots = collect($doctor->availableSlots($date))->pluck('time')->shuffle()->take($day === 0 ? mt_rand(5, 8) : mt_rand(3, 7))->sort()->values();
                $dept = $doctor->department->name;
                $pool = $patients->filter(fn ($p) => match ($dept) {
                    'الأطفال' => $p->age <= 12, 'النساء والتوليد' => $p->gender === 'female' && $p->age >= 18, default => $p->age >= 18,
                });
                // النهارده: كام واحد خلص، واحد جوه، وكام واحد مستني
                $doneToday = mt_rand(0, 3); $waitingToday = mt_rand(1, 3);
                foreach ($slots as $q => $time) {
                    $patient = $pool->random();
                    $status = $day > 0 ? (mt_rand(0, 12) ? 'done' : 'cancelled')
                        : ($q < $doneToday ? 'done' : ($q === $doneToday ? 'in_progress' : ($q <= $doneToday + $waitingToday ? 'arrived' : 'booked')));
                    $appt = Appointment::create([
                        'patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'date' => $date,
                        'time' => $time, 'queue_no' => $q + 1, 'status' => $status,
                    ]);
                    if ($status !== 'done') {
                        continue;
                    }

                    [$complaint, $diagnosis] = $complaints[$dept][mt_rand(0, 2)];
                    $visit = Visit::create([
                        'appointment_id' => $appt->id, 'patient_id' => $patient->id, 'doctor_id' => $doctor->id,
                        'complaint' => $complaint, 'diagnosis' => $diagnosis,
                        'vitals' => ['bp' => mt_rand(110, 150) . '/' . mt_rand(70, 95), 'pulse' => mt_rand(68, 98),
                                     'temp' => mt_rand(365, 385) / 10, 'weight' => mt_rand(20, 110)],
                    ]);
                    $visit->forceFill(['created_at' => $date->copy()->setTimeFromTimeString($time)])->save();

                    foreach ($meds->random(mt_rand(1, 3)) as $med) {
                        Prescription::create([
                            'visit_id' => $visit->id, 'medicine_id' => $med->id, 'quantity' => mt_rand(1, 2),
                            'dose' => ['قرص كل 8 ساعات', 'قرص بعد الأكل مرتين يومياً', 'قرص قبل النوم', 'معلقة 3 مرات يومياً'][mt_rand(0, 3)],
                            'dispensed_at' => $day > 0 || mt_rand(0, 1) ? $date : null,
                        ]);
                    }

                    $invoice = Invoice::create(['patient_id' => $patient->id, 'visit_id' => $visit->id, 'date' => $date]);
                    $invoice->items()->create(['description' => 'كشف ' . $doctor->department->name, 'qty' => 1, 'price' => $doctor->fee]);

                    if (mt_rand(0, 2) === 0) {
                        $test = $tests->random();
                        $at = $date->copy()->setTimeFromTimeString($time)->addMinutes(15);
                        $st = $day > 0 ? 'completed' : ['pending', 'collected', 'completed'][mt_rand(0, 2)];
                        $order = LabOrder::create([
                            'visit_id' => $visit->id, 'patient_id' => $patient->id, 'lab_test_id' => $test->id,
                            'status' => $st, 'result' => $st === 'completed' ? (string) mt_rand(60, 140) : null,
                            'result_at' => $st === 'completed' ? $at->copy()->addMinutes(50) : null,
                        ]);
                        $order->forceFill(['created_at' => $at])->save();
                        $invoice->items()->create(['description' => 'تحليل ' . $test->name, 'qty' => 1, 'price' => $test->price]);
                    }

                    $invoice->recalculate();
                    if ($day > 0 || mt_rand(0, 2)) {
                        $amount = mt_rand(0, 5) ? $invoice->net : round($invoice->net / 2);
                        Payment::create(['invoice_id' => $invoice->id, 'user_id' => $cashier->id, 'amount' => $amount,
                                         'method' => mt_rand(0, 3) ? 'cash' : 'card',
                                         'paid_at' => $date->copy()->setTimeFromTimeString($time)->addMinutes(25)]);
                        $invoice->recalculate();
                    }
                }
            }
            if ($date->dayOfWeek === 6 || $day === 0) {
                Expense::create(['title' => ['مستلزمات طبية', 'فاتورة كهرباء', 'صيانة أجهزة', 'أدوات نظافة'][mt_rand(0, 3)],
                                 'amount' => mt_rand(4, 30) * 100, 'date' => $date, 'user_id' => $cashier->id]);
            }
        }
    }
}
