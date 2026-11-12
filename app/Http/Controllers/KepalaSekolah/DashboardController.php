<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $statuses = Attendance::whereDate('tanggal', today())
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $start = Carbon::now()->startOfMonth()->subMonths(5);

        // Agregasi bulanan portabel (MySQL & SQLite). Lihat BUG-02.
        $rows = Attendance::whereBetween('tanggal', [$start->toDateString(), today()->toDateString()])
            ->get(['tanggal', 'status']);

        $grouped = [];
        foreach ($rows as $row) {
            $monthKey = Carbon::parse($row->tanggal)->format('Y-m');
            $grouped[$monthKey][$row->status] = ($grouped[$monthKey][$row->status] ?? 0) + 1;
        }

        $monthly = collect(range(0, 5))->map(function ($offset) use ($start, $grouped) {
            $month = $start->copy()->addMonths($offset);
            $key = $month->format('Y-m');
            $row = ['label' => $month->translatedFormat('M'), 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];
            foreach (['hadir', 'izin', 'sakit', 'alpa'] as $status) {
                $row[$status] = (int) ($grouped[$key][$status] ?? 0);
            }

            return $row;
        });

        return view('dashboards.kepala-sekolah', [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'classes' => SchoolClass::count(),
            'todayByStatus' => collect(['hadir', 'izin', 'sakit', 'alpa'])
                ->mapWithKeys(fn ($status) => [$status => (int) ($statuses[$status] ?? 0)])->all(),
            'monthly' => $monthly->all(),
        ]);
    }
}