<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\View\View;

class KelolaAbsensiController extends Controller
{
    public function index(): View
    {
        $schedules = Schedule::with(['schoolClass', 'subject', 'teacher', 'academicYear'])->get();

        return view('admin.kelola-absensi.index', compact('schedules'));
    }
}

