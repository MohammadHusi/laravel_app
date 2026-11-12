<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('DEMO_PASSWORD', 'Mawaddah#2026Secure');
        $accounts = collect([
            'admin' => ['Administrator', 'admin@smkmawaddah.sch.id', 'admin'],
            'guru' => ['Siti Rahmawati', 'guru@smkmawaddah.sch.id', 'guru'],
            'murid' => ['Ahmad Fauzi', 'murid@smkmawaddah.sch.id', 'murid'],
        ])->mapWithKeys(function (array $details, string $role) use ($password) {
            [$name, $email, $username] = $details;
            $user = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'username' => $username,
                'password' => $password,
                'role' => $role,
            ]);

            return [$role => $user];
        });

        $teacher = Teacher::updateOrCreate(['user_id' => $accounts['guru']->id], [
            'nama' => 'Siti Rahmawati', 'nip' => '199001012020012001', 'email' => $accounts['guru']->email,
        ]);

        $class = SchoolClass::firstOrCreate(['nama_kelas' => 'X RPL 1'], [
            'jurusan' => 'Rekayasa Perangkat Lunak', 'tingkat' => 10, 'wali_kelas_id' => $teacher->id,
        ]);
        $subject = Subject::firstOrCreate(['kode_mapel' => 'RPL101'], ['nama_mapel' => 'Pemrograman Web']);
        $year = AcademicYear::firstOrCreate(['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil'], ['status' => true]);
        $schedule = Schedule::firstOrCreate([
            'guru_id' => $teacher->id,
            'kelas_id' => $class->id,
            'mata_pelajaran_id' => $subject->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '09:30:00',
        ], ['tahun_ajaran_id' => $year->id]);

        $student = Student::updateOrCreate(['user_id' => $accounts['murid']->id], [
            'nis' => '26001', 'nisn' => '0123456789', 'nama' => 'Ahmad Fauzi', 'kelas_id' => $class->id,
            'jenis_kelamin' => 'L', 'alamat' => 'SMK Mawaddah',
        ]);

        Attendance::updateOrCreate([
            'murid_id' => $student->id,
            'tanggal' => today()->toDateString(),
            'kelas_id' => $class->id,
            'mata_pelajaran_id' => $subject->id,
            'jadwal_id' => $schedule->id,
        ], [
            'guru_id' => $teacher->id,
            'jam' => now()->format('H:i:s'),
            'status' => 'hadir',
            'keterangan' => null,
        ]);
    }
}
