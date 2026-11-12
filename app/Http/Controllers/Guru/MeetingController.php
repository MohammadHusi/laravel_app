<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Meeting;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MeetingController extends Controller
{
    /** Langkah 1: pilih kelompok kelas yang diajar. */
    public function index(Request $request): View
    {
        $teacher = $request->user()->teacherProfile();

        $schedules = Schedule::with(['schoolClass', 'subject'])
            ->when($request->user()->role === 'guru', fn ($q) => $q->where('guru_id', $teacher->id))
            ->get();
        $classes = $schedules->groupBy('kelas_id')
            ->sortBy(fn ($group) => $group->first()->schoolClass?->nama_kelas);

        return view('guru.pertemuan.pilih', compact('teacher', 'schedules', 'classes'));
    }

    /** Langkah 2: rekap matriks absensi (siswa x pertemuan). */
    public function matriks(Request $request): View
    {
        $teacher = $request->user()->teacherProfile();

        $schedules = Schedule::with(['schoolClass', 'subject', 'teacher', 'academicYear'])
            ->when($request->user()->role === 'guru', fn ($q) => $q->where('guru_id', $teacher->id))
            ->get();
        $requested = $schedules->firstWhere('id', (int) $request->query('jadwal'));
        if ($request->user()->role === 'guru' && $request->filled('jadwal') && ! $requested) {
            abort(403, 'Anda tidak memiliki akses ke mata pelajaran ini.');
        }
        $schedule = $requested ?? $schedules->first();

        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $students = collect();
        $meetings = collect();

        if ($schedule && $schedule->schoolClass) {
            $students = $schedule->schoolClass->students()
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('nama', 'like', '%'.$search.'%')
                            ->orWhere('nis', 'like', '%'.$search.'%')
                            ->orWhere('nisn', 'like', '%'.$search.'%');
                    });
                })
                ->orderBy('nama')
                ->paginate($perPage)
                ->withQueryString();

            $meetings = $schedule->meetings()->with('logs')->get();
        }

        // Matriks: [student_id][meeting_id] => status
        $matrix = [];
        $totals = [];
        foreach ($students as $student) {
            $totals[$student->id] = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0, 'terlambat' => 0];
        }
        foreach ($meetings as $meeting) {
            foreach ($meeting->logs as $log) {
                $matrix[$log->murid_id][$meeting->id] = $log->status;
                if (isset($totals[$log->murid_id])) {
                    $totals[$log->murid_id][$log->status]++;
                }
            }
        }

        return view('guru.pertemuan.index', compact('teacher', 'schedules', 'schedule', 'students', 'meetings', 'matrix', 'totals', 'search', 'perPage'));
    }

    /** Simpan pertemuan baru. */
    public function store(Request $request): RedirectResponse
    {
        $teacher = $request->user()->teacherProfile();

        $data = $request->validate([
            'jadwal_id' => ['required', 'integer', 'exists:jadwal,id'],
            'pertemuan_ke' => ['required', 'integer', 'min:1', 'max:100'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'konfirmasi' => ['accepted'],
        ], [
            'konfirmasi.accepted' => 'Centang konfirmasi bahwa data sudah benar.',
            'jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
        ]);

        $schedule = Schedule::with(['schoolClass', 'subject'])
            ->findOrFail($data['jadwal_id']);

        if ($request->user()->role === 'guru'
            && (int) $schedule->guru_id !== (int) $teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke mata pelajaran ini.');
        }

        // Cegah duplikat nomor pertemuan pada jadwal yang sama.
        $exists = Meeting::where('jadwal_id', $schedule->id)
            ->where('pertemuan_ke', $data['pertemuan_ke'])
            ->exists();
        if ($exists) {
            return back()->withErrors(['pertemuan_ke' => 'Pertemuan ke-'.$data['pertemuan_ke'].' sudah ada pada jadwal ini.'])->withInput();
        }

        Meeting::create([
            'jadwal_id' => $schedule->id,
            'pertemuan_ke' => $data['pertemuan_ke'],
            'nama_pembelajaran' => $schedule->subject?->nama_mapel ?? 'Pembelajaran',
            'tanggal' => $data['tanggal'],
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
        ]);

        return redirect()->route('guru.pertemuan.matriks', ['jadwal' => $schedule->id])
            ->with('success', 'Pertemuan ke-'.$data['pertemuan_ke'].' berhasil ditambahkan.');
    }

    /** Detail pertemuan (JSON untuk modal). */
    public function show(Request $request, Meeting $meeting): JsonResponse
    {
        $this->authorizeMeeting($request, $meeting);

        $meeting->load('schedule.subject', 'schedule.schoolClass', 'logs');
        $counts = $meeting->statusCounts();

        return response()->json([
            'id' => $meeting->id,
            'pertemuan_ke' => $meeting->pertemuan_ke,
            'nama_pembelajaran' => $meeting->nama_pembelajaran,
            'tanggal' => $meeting->tanggal?->translatedFormat('l, d F Y'),
            'jam' => substr($meeting->jam_mulai, 0, 5).' s/d '.substr($meeting->jam_selesai, 0, 5),
            'kelas' => $meeting->schedule?->schoolClass?->nama_kelas,
            'counts' => $counts,
            'terisi' => $meeting->logs->count(),
            'urls' => [
                'edit' => route('guru.pertemuan.update', $meeting),
                'destroy' => route('guru.pertemuan.destroy', $meeting),
                'absen' => route('guru.pertemuan.absen', $meeting),
            ],
        ]);
    }

    /** Perbarui pertemuan. */
    public function update(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeMeeting($request, $meeting);

        $data = $request->validate([
            'pertemuan_ke' => ['required', 'integer', 'min:1', 'max:100'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);

        $dupe = Meeting::where('jadwal_id', $meeting->jadwal_id)
            ->where('pertemuan_ke', $data['pertemuan_ke'])
            ->whereKeyNot($meeting->id)
            ->exists();
        if ($dupe) {
            return back()->withErrors(['pertemuan_ke' => 'Pertemuan ke-'.$data['pertemuan_ke'].' sudah ada pada jadwal ini.'])->withInput();
        }

        $meeting->update($data);

        return redirect()->route('guru.pertemuan.matriks', ['jadwal' => $meeting->jadwal_id])
            ->with('success', 'Pertemuan berhasil diperbarui.');
    }

    /** Hapus pertemuan beserta log absensinya. */
    public function destroy(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeMeeting($request, $meeting);
        $scheduleId = $meeting->jadwal_id;
        $meeting->delete();

        return redirect()->route('guru.pertemuan.matriks', ['jadwal' => $scheduleId])
            ->with('success', 'Pertemuan berhasil dihapus.');
    }

    /** Halaman kelola absensi siswa untuk satu pertemuan. */
    public function absen(Request $request, Meeting $meeting): View
    {
        $this->authorizeMeeting($request, $meeting);

        $meeting->load('schedule.schoolClass', 'schedule.subject', 'schedule.teacher', 'schedule.academicYear', 'logs');
        $students = $meeting->schedule?->schoolClass
            ? $meeting->schedule->schoolClass->students()->with('schoolClass')->orderBy('nama')->get()
            : collect();

        $existing = $meeting->logs->keyBy('murid_id');

        return view('guru.pertemuan.absen', compact('meeting', 'students', 'existing'));
    }

    /** Simpan absensi seluruh siswa pada satu pertemuan. */
    public function simpanAbsen(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeMeeting($request, $meeting);

        $data = $request->validate([
            'absensi' => ['required', 'array', 'min:1'],
            'absensi.*.status' => ['required', Rule::in(['hadir', 'sakit', 'izin', 'alpa', 'terlambat'])],
            'absensi.*.keterangan' => ['nullable', 'string', 'max:1000'],
            'konfirmasi' => ['accepted'],
        ], [
            'konfirmasi.accepted' => 'Centang persetujuan sebelum menyimpan.',
        ]);

        $studentIds = $meeting->schedule->schoolClass->students->pluck('id')->map(fn ($id) => (string) $id)->all();
        abort_if(array_diff(array_keys($data['absensi']), $studentIds), 403, 'Daftar murid tidak sesuai dengan kelas pertemuan.');

        DB::transaction(function () use ($data, $meeting) {
            foreach ($data['absensi'] as $studentId => $row) {
                AttendanceLog::updateOrCreate(
                    ['pertemuan_id' => $meeting->id, 'murid_id' => $studentId],
                    ['status' => $row['status'], 'keterangan' => $row['keterangan'] ?? null],
                );
            }
        });

        return redirect()->route('guru.pertemuan.matriks', ['jadwal' => $meeting->jadwal_id])
            ->with('success', 'Absensi pertemuan ke-'.$meeting->pertemuan_ke.' berhasil disimpan.');
    }

    /** Pastikan pertemuan milik jadwal guru yang berhak. */
    private function authorizeMeeting(Request $request, Meeting $meeting): void
    {
        abort_unless($meeting->schedule, 404, 'Pertemuan tidak memiliki jadwal.');

        if ($request->user()->role === 'guru'
            && (int) $meeting->schedule->guru_id !== (int) $request->user()->teacherProfile()->id) {
            abort(403, 'Anda tidak memiliki akses ke mata pelajaran ini.');
        }
    }
}

