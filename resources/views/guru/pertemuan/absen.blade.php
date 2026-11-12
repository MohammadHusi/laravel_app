@extends('layouts.app')
@section('title', 'Kelola Absensi')
@section('topbar', 'Kelola absensi siswa')
@section('content')
@php
    $statusMeta = [
        'hadir' => ['H', 'Hadir', 'success'],
        'sakit' => ['S', 'Sakit', 'warning'],
        'izin' => ['I', 'Izin', 'info'],
        'alpa' => ['A', 'Alpa', 'danger'],
        'terlambat' => ['T', 'Terlambat', 'secondary'],
    ];
    $schedule = $meeting->schedule;
    $year = $schedule?->academicYear;
@endphp
<div class="page-heading">
    <div><h1><a href="{{ route('guru.pertemuan.matriks', ['jadwal' => $schedule?->id]) }}" class="text-reset text-decoration-none" title="Kembali ke rekap absensi"><i class="fa-solid fa-arrow-left me-2" style="font-size:1em"></i></a>Kelola Absensi: {{ $schedule?->schoolClass?->nama_kelas ?? '-' }}</h1></div>
</div>

{{-- Kartu informasi pertemuan --}}
<section class="panel mb-3">
    <div class="row g-3" style="font-size:14px">
        <div class="col-12 col-md-6">
            <div class="d-flex mb-1"><span class="fw-bold" style="min-width:150px">Mata Pelajaran</span><span>: {{ $schedule?->subject?->nama_mapel ?? '-' }}</span></div>
            <div class="d-flex mb-1"><span class="fw-bold" style="min-width:150px">Kelas</span><span>: {{ $schedule?->schoolClass?->nama_kelas ?? '-' }}</span></div>
            <div class="d-flex mb-1"><span class="fw-bold" style="min-width:150px">Guru Pengampu</span><span>: {{ $schedule?->teacher?->nama ?? '-' }}</span></div>
            <div class="d-flex"><span class="fw-bold" style="min-width:150px">Tahun Pelajaran</span><span>: {{ $year?->tahun_ajaran ?? '-' }}</span></div>
        </div>
        <div class="col-12 col-md-6">
            <div class="d-flex mb-1"><span class="fw-bold" style="min-width:150px">Semester</span><span>: {{ $year?->semester ?? '-' }}</span></div>
            <div class="d-flex mb-1"><span class="fw-bold" style="min-width:150px">Pertemuan Ke</span><span>: {{ $meeting->pertemuan_ke }}</span></div>
            <div class="d-flex mb-1"><span class="fw-bold" style="min-width:150px">Hari, Tanggal</span><span>: {{ $meeting->tanggal?->translatedFormat('l, d F Y') }}</span></div>
            <div class="d-flex"><span class="fw-bold" style="min-width:150px">Pukul</span><span>: {{ substr($meeting->jam_mulai,0,5) }} s/d {{ substr($meeting->jam_selesai,0,5) }}</span></div>
        </div>
    </div>
</section>

<form method="POST" action="{{ route('guru.pertemuan.absen.simpan', $meeting) }}" id="absenForm">
    @csrf
    <section class="panel">
        <div class="table-responsive">
            <table class="table table-absen mb-0">
                <thead>
                    <tr>
                        <th style="width:55px">No.</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th class="text-center" style="width:70px">L/P</th>
                        <th style="width:200px">Paraf</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($students as $index => $student)
                    @php $log = $existing[$student->id] ?? null; @endphp
                    <tr>
                        <td data-label="No.">{{ $index + 1 }}</td>
                        <td data-label="NIS">{{ $student->nis ?? $student->nisn ?? '-' }}</td>
                        <td data-label="Nama Siswa">{{ $student->nama }}</td>
                        <td data-label="Kelas">{{ $student->schoolClass?->nama_kelas ?? $schedule?->schoolClass?->nama_kelas ?? '-' }}</td>
                        <td data-label="L/P" class="text-center">{{ $student->jenis_kelamin === 'P' ? 'P' : ($student->jenis_kelamin === 'L' ? 'L' : '-') }}</td>
                        <td data-label="Paraf">
                            <select class="form-select form-select-sm attendance-status status-{{ $log?->status ?? 'hadir' }}" name="absensi[{{ $student->id }}][status]" data-student="{{ $student->nama }}" required>
                                @foreach($statusMeta as $key => [$code, $label, $color])
                                    <option value="{{ $key }}" @selected(($log?->status ?? 'hadir') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-users"></i><div>Belum ada siswa pada kelas ini.</div></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="konfirmasi" name="konfirmasi" value="1" required>
                <label class="form-check-label fw-semibold" for="konfirmasi">Saya yakin akan mengubah data tersebut.</label>
            </div>
            <button type="submit" class="btn btn-primary" @disabled($students->isEmpty())><i class="fa-solid fa-floppy-disk me-2"></i>Simpan Absensi</button>
        </div>
    </section>
</form>
@endsection

@push('styles')
<style>
.table-absen{border-collapse:collapse}
.table-absen thead th{background:#0e8f9e;color:#fff;border:1px solid #9aa5ac;font-size:12px;font-weight:700;text-align:left;vertical-align:middle;white-space:nowrap;padding:10px 12px}
.table-absen thead th.text-center{text-align:center}
.table-absen td{padding:10px 12px;border:1px solid #c9d0d5;vertical-align:middle;font-size:13px}
.table-absen tbody tr:nth-child(odd){background:#f4f7f8}
.table-absen tbody tr:hover{background:#eef5f6}
.attendance-status{max-width:180px}
.attendance-status.status-hadir{border-color:#1a8f4c;color:#18734f}
.attendance-status.status-sakit{border-color:#dfaa43;color:#996a12}
.attendance-status.status-izin{border-color:#2f6fed;color:#2b5fc7}
.attendance-status.status-alpa{border-color:#d64545;color:#af4545}
.attendance-status.status-terlambat{border-color:#8a98a4;color:#5d6b76}
@media(max-width:767.98px){
    .table-absen{min-width:640px}
    .table-absen thead th,.table-absen td{padding:8px 9px;font-size:12px}
    .attendance-status{max-width:none;min-width:120px}
}
</style>
@endpush

@push('scripts')
<script>
let submitting = false;
document.getElementById('absenForm')?.addEventListener('submit', (event) => {
    if (submitting) return;
    event.preventDefault();
    if (!document.getElementById('konfirmasi').checked) {
        Swal.fire({icon:'warning', title:'Konfirmasi diperlukan', text:'Centang persetujuan sebelum menyimpan.'});
        return;
    }
    Swal.fire({
        icon: 'question',
        title: 'Simpan Absensi',
        text: 'Simpan absensi pertemuan ke-{{ $meeting->pertemuan_ke }}?',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#1a8f4c',
        cancelButtonColor: '#8a98a4',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            submitting = true;
            event.target.submit();
        }
    });
});
document.querySelectorAll('.attendance-status').forEach((select) => {
    select.addEventListener('change', () => {
        select.className = 'form-select form-select-sm attendance-status status-' + select.value;
    });
});
</script>
@endpush
