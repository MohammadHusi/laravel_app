<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user()->student;
        $attendances = $student?->attendances()->with(['subject', 'teacher']);
        $counts = collect(['hadir', 'izin', 'sakit', 'alpa'])->mapWithKeys(fn ($status) => [
            $status => (int) ($attendances?->clone()->where('status', $status)->count() ?? 0),
        ]);
        $total = $counts->sum();
        $period = $request->query('periode', 'semua');
        $history = $attendances?->when($period === 'hari', fn ($query) => $query->whereDate('tanggal', today()))
            ->when($period === 'minggu', fn ($query) => $query->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($period === 'bulan', fn ($query) => $query->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year))
            ->when($period === 'tahun', fn ($query) => $query->whereYear('tanggal', now()->year))
            ->latest('tanggal')->limit(10)->get() ?? collect();

        return view('dashboards.murid', [
            'student' => $student?->load('schoolClass'),
            'counts' => $counts,
            'percentage' => $total > 0 ? round(($counts['hadir'] / $total) * 100) : 0,
            'recent' => $history,
            'period' => $period,
        ]);
    }
}