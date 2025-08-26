<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\PmController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//index
Route::get('/', [MainController::class, 'index']);
Route::get('/home', [MainController::class, 'index'])->name('home');

Route::fallback(function () {
    return "notfound naja";
});

Route::middleware(['logged.in', 'check.session'])->group(function () {
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

    // PM Search
    Route::get('/pm', [pmController::class, 'pm'])->name('pm.index');
    Route::get('/pm_search', [pmController::class, 'pm_search'])->name('pm_search');

    //Auth Routes
    // แสดงนัด
    Route::get('/management', [AppointmentController::class, 'showAppointments'])->name('app.show');
    // Dashboard and Report
    Route::get('/ekg-echo', [MainController::class, 'showDashboard'])->name('index');
    Route::get('/report', [MainController::class, 'showReport'])->name('report.show');
});

Route::middleware(['logged.in', 'check.session', 'is.admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'adminPage'])->name('admin');
    Route::get('/admin/findUser', [AdminController::class, 'findUser'])->name('admin.findUser');

    Route::post('/admin/users/{username}/set-role', [AdminController::class, 'setRole'])->name('admin.users.setRole');
    Route::delete('/admin/users/{userid}/destroy', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    Route::post('/admin/roles/store', [AdminController::class, 'storeRole'])->name('admin.roles.store');
    Route::delete('/admin/roles/destroy/{id}', [AdminController::class, 'destroyRole'])->name('admin.roles.destroy');
});

Auth::routes();

Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
