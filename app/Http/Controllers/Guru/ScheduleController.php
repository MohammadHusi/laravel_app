<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /** Daftar jadwal mengajar milik guru yang sedang login. */
    public function index(Request $request): View
    {
        $teacher = $request->user()->teacherProfile();

        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $schedules = Schedule::with(['schoolClass', 'subject', 'teacher', 'academicYear'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('hari', 'like', '%'.$search.'%')
                        ->orWhereHas('subject', fn ($rel) => $rel->where('nama_mapel', 'like', '%'.$search.'%'))
                        ->orWhereHas('schoolClass', fn ($rel) => $rel->where('nama_kelas', 'like', '%'.$search.'%'))
                        ->orWhereHas('academicYear', fn ($rel) => $rel->where('tahun_ajaran', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->paginate($perPage)
            ->withQueryString();

        return view('guru.jadwal.index', compact('teacher', 'schedules', 'search', 'perPage'));
    }
}

