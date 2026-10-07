<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', Livewire\Dashboard::class)->name('dashboard');

    Route::middleware('role:reception,doctor')->group(function () {
        Route::get('/appointments', Livewire\Appointments::class)->name('appointments');
        Route::get('/patients', Livewire\Patients\Index::class)->name('patients');
        Route::get('/patients/{patient}', Livewire\Patients\Show::class)->name('patients.show');
    });
    Route::get('/doctors', Livewire\Doctors::class)->middleware('role:reception')->name('doctors');
    Route::get('/visits/{appointment}', Livewire\VisitWorkspace::class)->middleware('role:doctor')->name('visits.show');
    Route::get('/invoices', Livewire\Invoices::class)->middleware('role:cashier,reception')->name('invoices');
    Route::get('/cashbox', Livewire\Cashbox::class)->middleware('role:cashier')->name('cashbox');
    Route::get('/pharmacy', Livewire\Pharmacy::class)->middleware('role:pharmacist')->name('pharmacy');
    Route::get('/lab', Livewire\Lab::class)->middleware('role:lab,doctor')->name('lab');
});
