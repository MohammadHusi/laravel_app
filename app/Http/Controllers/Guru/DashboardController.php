<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $teacher = auth()->user()->teacherProfile();
        $schedules = $teacher->schedules()->with(['schoolClass', 'subject'])->get();
        $recent = Attendance::with(['student', 'schoolClass', 'subject'])->where('guru_id', $teacher->id)->latest('tanggal')->limit(8)->get();

        return view('dashboards.guru', compact('teacher', 'schedules', 'recent'));
    }
}