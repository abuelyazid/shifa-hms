<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------- الأقسام والأطباء ----------
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#127C6B');
            $table->timestamps();
        });

        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('title')->default('أخصائي');
            $table->string('phone')->nullable();
            $table->decimal('fee', 10, 2)->default(0);
            $table->json('work_days');            // [0..6] السبت = 6
            $table->time('start_time')->default('10:00');
            $table->time('end_time')->default('16:00');
            $table->unsignedSmallInteger('slot_minutes')->default(20);
            $table->timestamps();
        });

        // ---------- المرضى والملف الطبي ----------
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('file_no')->unique();
            $table->string('name');
            $table->enum('gender', ['male', 'female']);
            $table->date('birth_date')->nullable();
            $table->string('phone');
            $table->string('national_id')->nullable();
            $table->string('blood_type', 3)->nullable();
            $table->text('allergies')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('time');
            $table->unsignedSmallInteger('queue_no')->nullable();
            $table->string('status')->default('booked'); // booked, arrived, in_progress, done, cancelled
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->index(['doctor_id', 'date']);
        });

        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->json('vitals')->nullable(); // bp, pulse, temp, weight
            $table->text('complaint')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ---------- الصيدلية ----------
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('form')->default('أقراص');
            $table->string('strength')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->integer('reorder_level')->default(20);
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained();
            $table->string('dose');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->timestamp('dispensed_at')->nullable();
            $table->timestamps();
        });

        // ---------- المعمل ----------
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20);
            $table->decimal('price', 10, 2);
            $table->string('unit')->nullable();
            $table->string('normal_range')->nullable();
            $table->timestamps();
        });

        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_test_id')->constrained();
            $table->string('status')->default('pending'); // pending, collected, completed
            $table->string('result')->nullable();
            $table->timestamp('result_at')->nullable();
            $table->timestamps();
        });

        // ---------- الفواتير والخزنة ----------
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('paid', 10, 2)->default(0);
            $table->string('status')->default('unpaid'); // unpaid, partial, paid
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->unsignedSmallInteger('qty')->default(1);
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('method')->default('cash'); // cash, card
            $table->timestamp('paid_at');
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['expenses', 'payments', 'invoice_items', 'invoices', 'lab_orders', 'lab_tests',
                  'prescriptions', 'medicines', 'visits', 'appointments', 'patients', 'doctors', 'departments'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
