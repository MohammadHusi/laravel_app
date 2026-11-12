<?php

namespace App\Http\Controllers\Admin;

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
        return view('dashboards.admin', [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'classes' => SchoolClass::count(),
            'today' => Attendance::whereDate('tanggal', today())->count(),
            'todayByStatus' => $this->statusCounts(Attendance::whereDate('tanggal', today())),
            'monthly' => $this->monthlyCounts(),
            'recent' => Attendance::with(['student', 'schoolClass', 'subject'])
                ->latest('tanggal')->latest('jam')->limit(8)->get(),
        ]);
    }

    private function statusCounts($query): array
    {
        $counts = $query->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return collect(['hadir', 'izin', 'sakit', 'alpa'])
            ->mapWithKeys(fn ($status) => [$status => (int) ($counts[$status] ?? 0)])->all();
    }

    /**
     * Hitungan bulanan yang PORTABEL lintas database (MySQL & SQLite).
     *
     * Sebelumnya memakai DATE_FORMAT() yang hanya tersedia di MySQL sehingga
     * gagal pada SQLite (test). Agregasi dilakukan di PHP agar hasil identik.
     * Lihat BUG-02.
     */
    private function monthlyCounts(): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths(5);

        $rows = Attendance::query()
            ->whereBetween('tanggal', [$start->toDateString(), today()->toDateString()])
            ->get(['tanggal', 'status']);

        // [Y-m][status] => total
        $grouped = [];
        foreach ($rows as $row) {
            $month = Carbon::parse($row->tanggal)->format('Y-m');
            $grouped[$month][$row->status] = ($grouped[$month][$row->status] ?? 0) + 1;
        }

        return collect(range(0, 5))->map(function ($offset) use ($start, $grouped) {
            $month = $start->copy()->addMonths($offset);
            $key = $month->format('Y-m');
            $row = ['label' => $month->translatedFormat('M'), 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];
            foreach (['hadir', 'izin', 'sakit', 'alpa'] as $status) {
                $row[$status] = (int) ($grouped[$key][$status] ?? 0);
            }

            return $row;
        })->all();
    }
}