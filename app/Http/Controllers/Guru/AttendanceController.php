<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user()->teacherProfile();

        $schedules = Schedule::with(['schoolClass', 'subject', 'teacher', 'academicYear'])
            ->when($request->user()->role === 'guru', fn ($q) => $q->where('guru_id', $teacher->id))
            ->get();
        $schedule = $schedules->firstWhere('id', (int) $request->query('jadwal'));
        $date = $request->query('tanggal', today()->toDateString());
        $students = $schedule
            ? $schedule->schoolClass->students()->orderBy('nama')->with(['attendances' => fn ($query) => $query
                ->whereDate('tanggal', $date)->where('jadwal_id', $schedule->id)])->get()
            : collect();

        return view('guru.absensi', compact('teacher', 'schedules', 'schedule', 'students', 'date'));
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $request->user()->teacherProfile();

        $data = $request->validate([
            'jadwal_id' => ['required', 'integer', 'exists:jadwal,id'],
            'tanggal' => ['required', 'date'],
            'absensi' => ['required', 'array', 'min:1'],
            'absensi.*.status' => ['required', 'in:hadir,izin,sakit,alpa'],
            'absensi.*.keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $schedule = Schedule::with(['schoolClass.students'])->findOrFail($data['jadwal_id']);
        if ($request->user()->role === 'guru' && (int) $schedule->guru_id !== (int) $teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke mata pelajaran ini.');
        }
        $studentIds = $schedule->schoolClass->students->pluck('id')->map(fn ($id) => (string) $id)->all();
        abort_if(array_diff(array_keys($data['absensi']), $studentIds), 403, 'Daftar murid tidak sesuai dengan kelas jadwal.');

        DB::transaction(function () use ($data, $teacher, $schedule) {
            foreach ($data['absensi'] as $studentId => $attendance) {
                Attendance::updateOrCreate(
                    [
                        'murid_id' => $studentId,
                        'tanggal' => $data['tanggal'],
                        'kelas_id' => $schedule->kelas_id,
                        'mata_pelajaran_id' => $schedule->mata_pelajaran_id,
                        'jadwal_id' => $schedule->id,
                    ],
                    [
                        'guru_id' => $teacher->id,
                        'jam' => now()->format('H:i:s'),
                        'status' => $attendance['status'],
                        'keterangan' => $attendance['keterangan'] ?? null,
                    ],
                );
            }
        });

        return redirect()->route('guru.absensi.index', ['jadwal' => $schedule->id, 'tanggal' => $data['tanggal']])
            ->with('success', 'Absensi berhasil disimpan.');
    }
}