<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolDataController extends Controller
{
    private const RESOURCES = [
        'guru' => ['Guru', Teacher::class, ['nama' => 'Nama Guru', 'jenis_kelamin' => 'Jenis Kelamin', 'nip' => 'NIP', 'no_hp' => 'No. WA/Telepon', 'alamat' => 'Alamat', 'username' => 'Username', 'status' => 'Status', 'password' => 'Password', 'password_confirmation' => 'Konfirmasi Password']],
        'murid' => ['Murid', Student::class, ['nama' => 'Nama', 'kelas_id' => 'Kelas', 'nisn' => 'NISN', 'jenis_kelamin' => 'Jenis Kelamin', 'no_hp' => 'No. WA/Telepon', 'username' => 'Username', 'status' => 'Status', 'password' => 'Password', 'password_confirmation' => 'Konfirmasi Password']],
        'kelas' => ['Kelas', SchoolClass::class, ['nama_kelas' => 'Nama Kelas', 'jurusan' => 'Jurusan', 'wali_kelas_id' => 'Wali Kelas']],
        'mata-pelajaran' => ['Mata Pelajaran', Subject::class, ['nama_mapel' => 'Nama Mata Pelajaran', 'kode_mapel' => 'Kode']],
        'tahun-ajaran' => ['Tahun Ajaran', AcademicYear::class, ['tahun_ajaran' => 'Tahun Ajaran', 'semester' => 'Semester', 'status' => 'Tahun Aktif']],
        'jadwal' => ['Jadwal Pelajaran', Schedule::class, ['hari' => 'Hari', 'mata_pelajaran_id' => 'Mata Pelajaran', 'kelas_id' => 'Kelas', 'guru_id' => 'Guru', 'jam_mulai' => 'Jam Mulai', 'jam_selesai' => 'Jam Selesai', 'tahun_ajaran_id' => 'Tahun Ajaran', 'semester' => 'Semester']],
        'users' => ['Manajemen User', User::class, ['name' => 'Nama', 'username' => 'Username', 'email' => 'Email', 'role' => 'Role', 'password' => 'Password', 'password_confirmation' => 'Konfirmasi Password']],
    ];

    public function index(Request $request, string $resource): View
    {
        [$title, $model, $fields] = $this->resource($resource);
        $query = $model::query();

        if ($resource === 'jadwal') {
            $query->with(['teacher', 'schoolClass', 'subject', 'academicYear']);
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($fields, $resource, $search) {
                foreach ($fields as $field => $label) {
                    if ($field === 'password') {
                        continue;
                    }

                    $relation = $this->searchRelation($resource, $field);
                    if ($relation) {
                        $columns = $this->relationColumns($relation);
                        $q->orWhereHas($relation, function ($rel) use ($columns, $search) {
                            $rel->where(function ($w) use ($columns, $search) {
                                foreach ($columns as $column) {
                                    $w->orWhere($column, 'like', '%'.$search.'%');
                                }
                            });
                        });
                        continue;
                    }

                    if (in_array($field, ['status', 'user_id', 'kelas_id', 'wali_kelas_id', 'guru_id', 'mata_pelajaran_id', 'tahun_ajaran_id', 'tingkat'], true)) {
                        continue;
                    }

                    $q->orWhere($field, 'like', '%'.$search.'%');
                }
            });
        }

        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $tableFields = array_diff_key($fields, ['alamat' => null]);
        if ($resource === 'guru') {
            $order = ['nama', 'nip', 'jenis_kelamin', 'no_hp', 'status'];
            $tableFields = array_combine($order, array_map(fn ($f) => $fields[$f], $order));
        }
        if ($resource === 'murid') {
            $order = ['nama', 'kelas_id', 'nisn', 'jenis_kelamin', 'no_hp', 'status'];
            $tableFields = array_combine($order, array_map(fn ($f) => $fields[$f], $order));
        }

        return view('admin.data.index', compact('resource', 'title', 'fields', 'tableFields') + [
            'rows' => $query->latest()->paginate($perPage)->withQueryString(),
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }

    private function searchRelation(string $resource, string $field): ?string
    {
        return match ($field) {
            'user_id' => 'user',
            'wali_kelas_id' => 'homeroomTeacher',
            'guru_id' => 'teacher',
            'kelas_id' => 'schoolClass',
            'mata_pelajaran_id' => 'subject',
            'tahun_ajaran_id' => 'academicYear',
            default => null,
        };
    }

    private function relationColumns(string $relation): array
    {
        return match ($relation) {
            'user' => ['name', 'username', 'email'],
            'homeroomTeacher', 'teacher' => ['nama', 'nip', 'email'],
            'schoolClass' => ['nama_kelas', 'jurusan'],
            'subject' => ['nama_mapel', 'kode_mapel'],
            'academicYear' => ['tahun_ajaran', 'semester'],
            default => [],
        };
    }

    public function create(string $resource): View
    {
        [$title, , $fields] = $this->resource($resource);

        return view('admin.data.form', compact('resource', 'title', 'fields') + ['record' => null, 'options' => $this->options()]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        [$title, $model, $fields] = $this->resource($resource);
        $data = $request->validate($this->rules($resource, $fields));
        unset($data['password_confirmation']);
        if ($resource === 'tahun-ajaran') $data['status'] = $request->boolean('status');
        if ($resource === 'guru') $data['status'] = $request->boolean('status');
        if ($resource === 'murid') $data['status'] = $request->boolean('status');

        if ($resource === 'jadwal') {
            $data['mata_pelajaran_id'] = $this->resolveSubjectId($data['mata_pelajaran_id']);
        }

        if ($resource === 'guru') {
            $user = User::create([
                'name' => $data['nama'],
                'username' => $data['username'],
                'email' => $data['username'],
                'password' => $data['password'],
                'role' => 'guru',
            ]);
            $data['user_id'] = $user->id;
            unset($data['username'], $data['password']);
        }

        if ($resource === 'murid') {
            $user = User::create([
                'name' => $data['nama'],
                'username' => $data['username'],
                'email' => $data['username'],
                'password' => $data['password'],
                'role' => 'murid',
            ]);
            $data['user_id'] = $user->id;
            unset($data['username'], $data['password']);
        }

        $model::create($data);

        return redirect()->route('admin.data.index', $resource)->with('success', "$title berhasil ditambahkan.");
    }

    public function show(string $resource, int $id): View
    {
        [$title, $model, $fields] = $this->resource($resource);

        return view('admin.data.show', compact('resource', 'title', 'fields') + ['record' => $model::findOrFail($id)]);
    }

    public function edit(string $resource, int $id): View
    {
        [$title, $model, $fields] = $this->resource($resource);
        $record = $model::findOrFail($id);
        if ($resource === 'users') $fields['password'] = 'Password baru (opsional)';
        if ($resource === 'guru') {
            $fields['password'] = 'Password baru (opsional)';
            $record->username = $record->user?->username;
        }
        if ($resource === 'murid') {
            $fields['password'] = 'Password baru (opsional)';
            $record->username = $record->user?->username;
        }

        return view('admin.data.form', compact('resource', 'title', 'fields') + ['record' => $record, 'options' => $this->options()]);
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        [$title, $model, $fields] = $this->resource($resource);
        $record = $model::findOrFail($id);
        $data = $request->validate($this->rules($resource, $fields, $record));
        unset($data['password_confirmation']);
        if ($resource === 'users' && blank($data['password'] ?? null)) unset($data['password']);
        if ($resource === 'tahun-ajaran') $data['status'] = $request->boolean('status');
        if ($resource === 'guru') $data['status'] = $request->boolean('status');
        if ($resource === 'murid') $data['status'] = $request->boolean('status');

        if ($resource === 'jadwal') {
            $data['mata_pelajaran_id'] = $this->resolveSubjectId($data['mata_pelajaran_id']);
        }

        if ($resource === 'guru') {
            $user = $record->user ?? User::create([
                'name' => $data['nama'], 'username' => $data['username'],
                'email' => $data['username'], 'password' => $data['password'] ?? 'password1234',
                'role' => 'guru',
            ]);
            $user->name = $data['nama'];
            $user->username = $data['username'];
            $user->email = $data['username'];
            if (! blank($data['password'] ?? null)) $user->password = $data['password'];
            $user->save();
            $data['user_id'] = $user->id;
            unset($data['username'], $data['password']);
        }

        if ($resource === 'murid') {
            $user = $record->user ?? User::create([
                'name' => $data['nama'], 'username' => $data['username'],
                'email' => $data['username'], 'password' => $data['password'] ?? 'password1234',
                'role' => 'murid',
            ]);
            $user->name = $data['nama'];
            $user->username = $data['username'];
            $user->email = $data['username'];
            if (! blank($data['password'] ?? null)) $user->password = $data['password'];
            $user->save();
            $data['user_id'] = $user->id;
            unset($data['username'], $data['password']);
        }

        $record->update($data);

        return redirect()->route('admin.data.index', $resource)->with('success', "$title berhasil diperbarui.");
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        [$title, $model] = $this->resource($resource);
        $model::findOrFail($id)->delete();

        return back()->with('success', "$title berhasil dihapus.");
    }

    private function resource(string $resource): array
    {
        abort_unless(isset(self::RESOURCES[$resource]), 404);

        return self::RESOURCES[$resource];
    }

    private function rules(string $resource, array $fields, ?Model $record = null): array
    {
        $rules = [];

        foreach ($fields as $field => $label) {
            $required = in_array($field, ['nama', 'nama_kelas', 'nama_mapel', 'kode_mapel', 'tahun_ajaran', 'semester', 'guru_id', 'kelas_id', 'mata_pelajaran_id', 'hari', 'jam_mulai', 'jam_selesai', 'name', 'email', 'role', 'username'], true);
            $rules[$field] = [$required ? 'required' : 'nullable'];

            if ($field === 'password') {
                $rules[$field] = $record ? ['nullable'] : ['required'];
            }

            if ($field === 'password_confirmation') {
                $rules[$field] = ['nullable', 'string'];
            }

            if (in_array($field, ['kelas_id', 'wali_kelas_id'], true)) {
                $rules[$field][] = ($field === 'kelas_id' && in_array($resource, ['jadwal', 'murid'], true))
                    ? 'exists:kelas,id'
                    : 'exists:guru,id';
                continue;
            }

            switch ($field) {
                case 'email':
                    $rules[$field] = array_merge($rules[$field], ['email', 'max:255']);
                    break;
                case 'tanggal_lahir':
                    $rules[$field][] = 'date';
                    break;
                case 'tingkat':
                    $rules[$field][] = 'integer';
                    $rules[$field][] = 'between:10,12';
                    break;
                case 'jenis_kelamin':
                    $rules[$field][] = 'in:L,P';
                    break;
                case 'semester':
                    $rules[$field][] = 'in:Ganjil,Genap';
                    break;
                case 'hari':
                    $rules[$field][] = 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Ahad';
                    break;
                case 'user_id':
                    $rules[$field][] = 'exists:users,id';
                    break;
                case 'role':
                    $rules[$field][] = 'in:admin,guru,murid';
                    break;
                case 'guru_id':
                    $rules[$field][] = 'exists:guru,id';
                    break;
                case 'mata_pelajaran_id':
                    $rules[$field][] = $resource === 'jadwal' ? 'string' : 'exists:mata_pelajaran,id';
                    break;
                case 'tahun_ajaran_id':
                    $rules[$field][] = 'exists:tahun_ajaran,id';
                    break;
                case 'jam_mulai':
                case 'jam_selesai':
                    $rules[$field][] = 'date_format:H:i';
                    break;
                case 'password':
                    $rules[$field][] = 'string';
                    $rules[$field][] = 'min:10';
                    $rules[$field][] = 'confirmed';
                    break;
                default:
                    $rules[$field][] = 'string';
                    $rules[$field][] = 'max:1000';
                    break;
            }

            if (in_array($resource, ['users', 'guru', 'murid'], true) && in_array($field, ['email', 'username'], true)) {
                $ignoreId = match (true) {
                    $resource === 'users' => $record?->id,
                    $resource === 'guru' => $record?->user_id,
                    $resource === 'murid' => $record?->user_id,
                    default => null,
                };
                $rules[$field][] = 'unique:users,'. $field . ($ignoreId ? ','.$ignoreId : '');
            }

            if ($field === 'user_id') {
                $table = match ($resource) {
                    'guru' => 'guru',
                    'murid' => 'murid',
                    default => 'kepala_sekolah',
                };
                $rules[$field][] = 'unique:'.$table.',user_id'.($record ? ','.$record->id : '');
            }
        }

        return $rules;
    }

    private function resolveSubjectId(string $name): int
    {
        $name = trim($name);

        $subject = Subject::firstOrCreate(
            ['nama_mapel' => $name],
            ['kode_mapel' => $this->generateSubjectCode($name)]
        );

        return $subject->id;
    }

    private function generateSubjectCode(string $name): string
    {
        $base = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $base = substr($base, 0, 8) ?: 'MAPEL';
        $code = $base;
        $i = 1;
        while (Subject::where('kode_mapel', $code)->exists()) {
            $code = $base.$i;
            $i++;
        }

        return $code;
    }

    private function options(): array
    {
        return [
            'kelas' => SchoolClass::orderBy('nama_kelas')->pluck('nama_kelas', 'id'),
            'guru' => Teacher::orderBy('nama')->pluck('nama', 'id'),
            'mapel' => Subject::orderBy('nama_mapel')->pluck('nama_mapel', 'id'),
            'tahun' => AcademicYear::orderByDesc('tahun_ajaran')->pluck('tahun_ajaran', 'id'),
            'users' => User::orderBy('name')->pluck('name', 'id'),
        ];
    }
}