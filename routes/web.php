<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TreatmentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

//index
Route::get('/', [MainController::class, 'index']);

Route::fallback(function () {
    return "notfound naja";
});

// แสดงนัด
Route::get('/index', [AppointmentController::class, 'showAppointments'])->name('app.show');

// เพิ่มนัด
Route::post('/appointment/add', [AppointmentController::class, 'addAppointment'])->name('app.add');

// ลบนัด
Route::delete('/appointment/delete/{a_id}', [AppointmentController::class, 'deleteAppointment'])->name('app.delete');

// แก้ไขนัด
Route::post('/appointments/update/{id}', [AppointmentController::class, 'updateAppointment'])->name('appointments.update');

// โชว์รายการนัดและรักษา
Route::get('/fragments/{page}', [MainController::class, 'loadFragment'])->name('loadPage');

// เพิ่มการรักษา
Route::post('/treatment/add', [TreatmentController::class, 'addTreatment'])->name('treatment.add');

//ลบการรักษา
Route::delete('/treatment/delete/{t_id}', [TreatmentController::class, 'deleteTreatment'])->name('treatment.delete');

// แสดงชื่อคนไข้ (appointments)
Route::get('/api/patient-name', [MainController::class, 'getPatientName']);

// routes/api.php
Route::get('/api/search-doctors', [AppointmentController::class, 'searchDoctors']);

// Route หลัก
Route::post('/check-appointment-history', [AppointmentController::class, 'checkAppointmentHistory'])->name('appointment.checkHistory');

// Route สำหรับแสดงรายงาน
Route::get('/dashboard', [MainController::class, 'showDashboard'])->name('dashboard.show');
Route::get('/report', [MainController::class, 'showReport'])->name('report.show');

Route::get('/login', function () {
    return view('login');
})->name('login');