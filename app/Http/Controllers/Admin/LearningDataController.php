<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LearningDataController extends Controller
{
    public function index(Request $request): View
    {
        $query = Schedule::with(['teacher', 'schoolClass', 'subject', 'academicYear']);

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('subject', fn ($w) => $w->where('nama_mapel', 'like', '%'.$search.'%'))
                    ->orWhereHas('schoolClass', fn ($w) => $w->where('nama_kelas', 'like', '%'.$search.'%'))
                    ->orWhereHas('teacher', fn ($w) => $w->where('nama', 'like', '%'.$search.'%'))
                    ->orWhereHas('academicYear', fn ($w) => $w->where('tahun_ajaran', 'like', '%'.$search.'%'));
            });
        }

        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        return view('admin.pembelajaran.index', [
            'rows' => $query->latest()->paginate($perPage)->withQueryString(),
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }
}

