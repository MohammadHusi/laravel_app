<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\View\View;

class SchoolDataController extends Controller
{
    private const DATA = [
        'guru' => ['Data Guru', Teacher::class, ['nama' => 'Nama', 'nip' => 'NIP', 'email' => 'Email', 'no_hp' => 'No. HP']],
        'murid' => ['Data Murid', Student::class, ['nisn' => 'NISN', 'nama' => 'Nama', 'kelas_id' => 'ID Kelas', 'jenis_kelamin' => 'Jenis Kelamin']],
        'kelas' => ['Data Kelas', SchoolClass::class, ['nama_kelas' => 'Nama Kelas', 'jurusan' => 'Jurusan', 'tingkat' => 'Tingkat', 'wali_kelas_id' => 'ID Wali Kelas']],
    ];

    public function index(string $resource): View
    {
        abort_unless(isset(self::DATA[$resource]), 404);
        [$title, $model, $fields] = self::DATA[$resource];

        return view('kepala-sekolah.data.index', [
            'title' => $title,
            'fields' => $fields,
            'rows' => $model::latest()->paginate(20),
        ]);
    }
}