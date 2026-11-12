<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\KelolaAbsensiController;
use App\Http\Controllers\Admin\LearningDataController;
use App\Http\Controllers\Admin\SchoolDataController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Guru\AttendanceController as GuruAttendance;
use App\Http\Controllers\Guru\DashboardController as GuruDashboard;
use App\Http\Controllers\Guru\MeetingController as GuruMeeting;
use App\Http\Controllers\Guru\ScheduleController as GuruSchedule;
use App\Http\Controllers\Murid\DashboardController as StudentDashboard;
use App\Http\Controllers\Murid\ScheduleController as StudentSchedule;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reports\AttendanceReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            'murid' => redirect()->route('murid.dashboard'),
            default => abort(403),
        };
    })->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profil/username', [ProfileController::class, 'updateUsername'])->name('profile.username');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/', AdminDashboard::class)->name('dashboard');
        Route::get('/datapembelajaran', [LearningDataController::class, 'index'])->name('datapembelajaran');
        Route::get('/kelola-absensi', [KelolaAbsensiController::class, 'index'])->name('kelola-absensi');
        Route::get('/laporan', [AttendanceReportController::class, 'index'])->name('laporan');
        Route::get('/data/{resource}', [SchoolDataController::class, 'index'])
            ->whereIn('resource', ['guru', 'murid', 'kelas', 'mata-pelajaran', 'tahun-ajaran', 'jadwal', 'users'])
            ->name('data.index');
        Route::get('/data/{resource}/create', [SchoolDataController::class, 'create'])
            ->whereIn('resource', ['guru', 'murid', 'kelas', 'mata-pelajaran', 'tahun-ajaran', 'jadwal', 'users'])
            ->name('data.create');
        Route::post('/data/{resource}', [SchoolDataController::class, 'store'])
            ->whereIn('resource', ['guru', 'murid', 'kelas', 'mata-pelajaran', 'tahun-ajaran', 'jadwal', 'users'])
            ->name('data.store');
        Route::get('/data/{resource}/{id}', [SchoolDataController::class, 'show'])
            ->whereIn('resource', ['guru', 'murid'])->whereNumber('id')->name('data.show');
        Route::get('/data/{resource}/{id}/edit', [SchoolDataController::class, 'edit'])
            ->whereIn('resource', ['guru', 'murid', 'kelas', 'mata-pelajaran', 'tahun-ajaran', 'jadwal', 'users'])
            ->whereNumber('id')->name('data.edit');
        Route::put('/data/{resource}/{id}', [SchoolDataController::class, 'update'])
            ->whereIn('resource', ['guru', 'murid', 'kelas', 'mata-pelajaran', 'tahun-ajaran', 'jadwal', 'users'])
            ->whereNumber('id')->name('data.update');
        Route::delete('/data/{resource}/{id}', [SchoolDataController::class, 'destroy'])
            ->whereIn('resource', ['guru', 'murid', 'kelas', 'mata-pelajaran', 'tahun-ajaran', 'jadwal', 'users'])
            ->whereNumber('id')->name('data.destroy');
    });

    Route::prefix('guru')->name('guru.')->middleware('role:guru,admin')->group(function () {
        Route::get('/', GuruDashboard::class)->name('dashboard');
        Route::get('/absensi', [GuruAttendance::class, 'index'])->name('absensi.index');
        Route::get('/jadwal', [GuruSchedule::class, 'index'])->name('jadwal.index');
        Route::post('/absensi', [GuruAttendance::class, 'store'])->name('absensi.store');

        // Modul pertemuan & matriks absensi pembelajaran.
        Route::get('/pertemuan', [GuruMeeting::class, 'index'])->name('pertemuan.index');
        Route::get('/pertemuan/matriks', [GuruMeeting::class, 'matriks'])->name('pertemuan.matriks');
        Route::post('/pertemuan', [GuruMeeting::class, 'store'])->name('pertemuan.store');
        Route::get('/pertemuan/{meeting}/absen', [GuruMeeting::class, 'absen'])->name('pertemuan.absen');
        Route::post('/pertemuan/{meeting}/absen', [GuruMeeting::class, 'simpanAbsen'])->name('pertemuan.absen.simpan');
        Route::get('/pertemuan/{meeting}', [GuruMeeting::class, 'show'])->name('pertemuan.show');
        Route::put('/pertemuan/{meeting}', [GuruMeeting::class, 'update'])->name('pertemuan.update');
        Route::delete('/pertemuan/{meeting}', [GuruMeeting::class, 'destroy'])->name('pertemuan.destroy');
    });

    Route::prefix('murid')->name('murid.')->middleware('role:murid')->group(function () {
        Route::get('/', StudentDashboard::class)->name('dashboard');
        Route::get('/jadwal', [StudentSchedule::class, 'index'])->name('jadwal.index');
    });
});
