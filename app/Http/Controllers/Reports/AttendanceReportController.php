<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $filters = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'guru_id' => ['nullable', 'integer', 'exists:guru,id'],
            'mata_pelajaran_id' => ['nullable', 'integer', 'exists:mata_pelajaran,id'],
            'status' => ['nullable', 'in:hadir,izin,sakit,alpa'],
        ]);

        $query = Attendance::with(['student', 'teacher', 'schoolClass', 'subject'])->when(
            $filters['tanggal'] ?? null,
            fn ($query, $date) => $query->whereDate('tanggal', $date),
        )->when($filters['bulan'] ?? null, fn ($query, $month) => $query->whereMonth('tanggal', $month))
            ->when($filters['tahun'] ?? null, fn ($query, $year) => $query->whereYear('tanggal', $year))
            ->when($filters['kelas_id'] ?? null, fn ($query, $id) => $query->where('kelas_id', $id))
            ->when($filters['guru_id'] ?? null, fn ($query, $id) => $query->where('guru_id', $id))
            ->when($filters['mata_pelajaran_id'] ?? null, fn ($query, $id) => $query->where('mata_pelajaran_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status));

        if ($request->query('export') === 'csv') {
            $rows = (clone $query)->orderBy('tanggal')->get();

            return response()->streamDownload(function () use ($rows) {
                $output = fopen('php://output', 'w');
                fwrite($output, "\xEF\xBB\xBF");
                fputcsv($output, ['Tanggal', 'NIS', 'NISN', 'Nama Murid', 'Kelas', 'Guru', 'Mata Pelajaran', 'Status', 'Keterangan']);
                foreach ($rows as $row) {
                    fputcsv($output, [
                        $row->tanggal->format('Y-m-d'), $row->student?->nis, $row->student?->nisn,
                        $row->student?->nama, $row->schoolClass?->nama_kelas, $row->teacher?->nama,
                        $row->subject?->nama_mapel, ucfirst($row->status), $row->keterangan,
                    ]);
                }
                fclose($output);
            }, 'laporan-absensi.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $rows = $query->orderByDesc('tanggal')->orderBy('kelas_id')->paginate(25)->withQueryString();
        $counts = (clone $query)
            ->reorder()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $statusCounts = collect(['hadir', 'izin', 'sakit', 'alpa'])->mapWithKeys(fn ($status) => [
            $status => (int) ($counts[$status] ?? 0),
        ]);
        $total = $statusCounts->sum();

        return view('laporan.index', [
            'rows' => $rows,
            'filters' => $filters,
            'classes' => SchoolClass::orderBy('nama_kelas')->get(),
            'teachers' => Teacher::orderBy('nama')->get(),
            'subjects' => Subject::orderBy('nama_mapel')->get(),
            'statusCounts' => $statusCounts,
            'percentage' => $total ? round(($statusCounts['hadir'] / $total) * 100, 1) : 0,
        ]);
    }
}