<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /** Daftar jadwal pelajaran untuk murid. */
    public function index(Request $request): View
    {
        $student = $request->user()->student?->load('schoolClass');

        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $query = Schedule::with(['schoolClass', 'subject', 'teacher', 'academicYear']);

        // Jika murid memiliki kelas, tampilkan jadwal kelasnya
        if ($student && $student->kelas_id) {
            $query->where('kelas_id', $student->kelas_id);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('hari', 'like', '%'.$search.'%')
                    ->orWhereHas('subject', fn ($rel) => $rel->where('nama_mapel', 'like', '%'.$search.'%'))
                    ->orWhereHas('teacher', fn ($rel) => $rel->where('nama', 'like', '%'.$search.'%'))
                    ->orWhereHas('schoolClass', fn ($rel) => $rel->where('nama_kelas', 'like', '%'.$search.'%'))
                    ->orWhereHas('academicYear', fn ($rel) => $rel->where('tahun_ajaran', 'like', '%'.$search.'%'));
            });
        }

        $schedules = $query
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad', 'Minggu')")
            ->orderBy('jam_mulai')
            ->paginate($perPage)
            ->withQueryString();

        return view('murid.jadwal.index', compact('student', 'schedules', 'search', 'perPage'));
    }
}

