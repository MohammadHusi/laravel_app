@extends('layouts.app')
@section('title', 'Absensi Pembelajaran')
@section('topbar', 'Absensi pembelajaran')
@section('content')
@php
    $statusMeta = [
        'hadir' => ['H', 'Hadir', 'success'],
        'sakit' => ['S', 'Sakit', 'warning'],
        'izin' => ['I', 'Izin', 'info'],
        'alpa' => ['A', 'Alpa', 'danger'],
        'terlambat' => ['T', 'Terlambat', 'secondary'],
    ];
@endphp
<div class="page-heading">
    @php $backUrl = auth()->user()->role === 'admin' ? route('admin.kelola-absensi') : route('guru.dashboard'); @endphp
    <div><h1><a href="{{ $backUrl }}" class="text-reset text-decoration-none" title="{{ auth()->user()->role === 'admin' ? 'Kembali ke Kelola Absensi' : 'Kembali ke dashboard' }}"><i class="fa-solid fa-arrow-left me-2" style="font-size:1em"></i></a>Absensi: {{ $schedule?->schoolClass?->nama_kelas ?? 'Pilih Kelas' }}</h1><p>Rekap kehadiran siswa per pertemuan beserta akumulasinya.</p></div>
</div>

@if(!$schedule)
    <section class="panel"><div class="empty-state"><i class="fa-solid fa-chalkboard-user"></i><div>Belum ada jadwal mengajar untuk akun ini.</div></div></section>
@else

{{-- Kartu informasi pembelajaran --}}
<section class="panel mb-3">
    <div class="row g-2" style="font-size:13px">
        <div class="col-12 col-md-6"><div class="d-flex"><span class="fw-bold" style="min-width:140px">Mata Pelajaran</span><span>: {{ $schedule->subject?->nama_mapel ?? '-' }}</span></div></div>
        <div class="col-12 col-md-6"><div class="d-flex"><span class="fw-bold" style="min-width:140px">Kelas</span><span>: {{ $schedule->schoolClass?->nama_kelas ?? '-' }}</span></div></div>
        <div class="col-12 col-md-6"><div class="d-flex"><span class="fw-bold" style="min-width:140px">Guru Pengampu</span><span>: {{ $schedule->teacher?->nama ?? '-' }}</span></div></div>
        <div class="col-12 col-md-6"><div class="d-flex"><span class="fw-bold" style="min-width:140px">Tahun Pelajaran</span><span>: {{ $schedule->academicYear?->tahun_ajaran ?? '-' }}</span></div></div>
        <div class="col-12 col-md-6"><div class="d-flex"><span class="fw-bold" style="min-width:140px">Semester</span><span>: {{ $schedule->academicYear?->semester ?? '-' }}</span></div></div>
    </div>
</section>

{{-- Tombol aksi --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <button type="button" class="btn btn-success" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Cetak Rekapitulasi</button>
    <button class="btn text-white" style="background:#2f6fed;border-color:#2f6fed" data-bs-toggle="modal" data-bs-target="#modalTambahPertemuan"><i class="fa-solid fa-plus me-2"></i>Pertemuan</button>
</div>

<section class="panel">
    {{-- Toolbar: page size + search --}}
    <form class="data-toolbar" method="GET" action="{{ route('guru.pertemuan.matriks') }}">
        <input type="hidden" name="jadwal" value="{{ $schedule->id }}">
        <div class="data-toolbar-left">
            <select id="per_page" name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach([10, 25, 50, 100] as $size)
                    <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </div>
        <div class="data-toolbar-right">
            <div class="data-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search...">
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-matrix table-rekap mb-0">
            <thead>
                <tr>
                    <th rowspan="2" class="text-center" style="width:46px">No.</th>
                    <th rowspan="2">NIS</th>
                    <th rowspan="2">Nama Siswa</th>
                    <th rowspan="2" class="text-center">L/P</th>
                    <th class="text-center" colspan="{{ max($meetings->count(), 1) }}">Pertemuan Ke</th>
                    <th class="text-center total-head" colspan="5">Jumlah</th>
                </tr>
                <tr>
                    @forelse($meetings as $meeting)
                        <th class="text-center meeting-head">
                            <button type="button" class="meeting-link" data-meeting-id="{{ $meeting->id }}" title="Detail pertemuan {{ $meeting->pertemuan_ke }}">{{ $meeting->pertemuan_ke }}</button>
                        </th>
                    @empty
                        <th class="text-center text-secondary">–</th>
                    @endforelse
                    @foreach($statusMeta as $key => [$code, $label, $color])<th class="text-center total-head total-col head-{{ $key }}"><span class="badge-status badge-{{ $color }}">{{ $code }}</span></th>@endforeach
                </tr>
            </thead>
            <tbody>
            @forelse($students as $student)
                <tr>
                    <td class="text-center">{{ $students->firstItem() + $loop->index }}</td>
                    <td>{{ $student->nis ?? $student->nisn ?? '-' }}</td>
                    <td class="fw-semibold">{{ $student->nama }}</td>
                    <td class="text-center">{{ $student->jenis_kelamin === 'P' ? 'P' : ($student->jenis_kelamin === 'L' ? 'L' : '-') }}</td>
                    @forelse($meetings as $meeting)
                        @php $status = $matrix[$student->id][$meeting->id] ?? null; @endphp
                        <td class="text-center {{ $status ? 'cell-'.$status : '' }}">
                            @if($status)
                                <span class="badge-status badge-{{ $statusMeta[$status][2] }}" title="{{ $statusMeta[$status][1] }}">{{ $statusMeta[$status][0] }}</span>
                            @else
                                <span class="text-secondary">–</span>
                            @endif
                        </td>
                    @empty
                        <td class="text-center text-secondary">–</td>
                    @endforelse
                    @foreach($statusMeta as $key => [$code, $label, $color])
                        <td class="text-center fw-bold total-col total-col-{{ $key }}">{{ $totals[$student->id][$key] ?? 0 }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ 4 + max($meetings->count(), 1) + 5 }}"><div class="empty-state"><i class="fa-solid fa-users"></i><div>Belum ada siswa pada kelas ini.</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="data-footer">
        <div class="data-info">Menampilkan <strong>{{ $students->firstItem() ?? 0 }}</strong>–<strong>{{ $students->lastItem() ?? 0 }}</strong> dari <strong>{{ $students->total() }}</strong> siswa</div>
        <div class="data-pagination">{{ $students->links() }}</div>
    </div>
</section>
@endif

{{-- Modal Tambah Pertemuan --}}
<div class="modal fade" id="modalTambahPertemuan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('guru.pertemuan.store') }}">
                @csrf
                <input type="hidden" name="jadwal_id" value="{{ $schedule?->id }}">
                <div class="modal-header"><h5 class="modal-title"><i class="fa-solid fa-calendar-plus me-2"></i>Tambah Pertemuan</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label fw-bold">Nama Pembelajaran</label>
                        <div class="col-sm-8"><input type="text" class="form-control" value="{{ $schedule?->subject?->nama_mapel }}{{ $schedule?->schoolClass ? ' - '.$schedule->schoolClass->nama_kelas : '' }}" readonly></div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label fw-bold" for="pertemuan_ke">Pertemuan Ke <span class="fw-normal fst-italic text-secondary">(Angka)</span></label>
                        <div class="col-sm-8"><input type="number" class="form-control" id="pertemuan_ke" name="pertemuan_ke" min="1" max="100" placeholder="Masukkan pertemuan ke" value="{{ ($meetings->max('pertemuan_ke') ?? 0) + 1 }}" required></div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label fw-bold" for="tanggal">Tanggal</label>
                        <div class="col-sm-8"><input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ now()->toDateString() }}" required></div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label fw-bold" for="jam_mulai">Pukul</label>
                        <div class="col-sm-8">
                            <div class="d-flex align-items-center gap-2">
                                <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" value="{{ $schedule ? substr($schedule->jam_mulai,0,5) : '07:00' }}" required>
                                <span class="text-nowrap">s/d</span>
                                <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" value="{{ $schedule ? substr($schedule->jam_selesai,0,5) : '08:30' }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="offset-sm-4 col-sm-8"><div class="form-check"><input class="form-check-input" type="checkbox" id="konfirmasi" name="konfirmasi" value="1" required><label class="form-check-label fw-semibold" for="konfirmasi">Saya yakin sudah mengisi dengan benar</label></div></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan</button></div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Detail Pertemuan --}}
<div class="modal fade" id="modalDetailPertemuan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fa-solid fa-circle-info me-2"></i>Detail Pertemuan</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body" id="detailBody">
                <div class="text-center py-4"><div class="spinner-border text-success" role="status"></div></div>
            </div>
            <div class="modal-footer detail-actions">
                <form method="POST" id="formHapusPertemuan" data-confirm="Hapus pertemuan ini beserta absensinya?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="fa-regular fa-trash-can me-2"></i>Hapus</button>
                </form>
                <button type="button" class="btn btn-warning text-white" id="btnEditPertemuan"><i class="fa-regular fa-pen-to-square me-2"></i>Edit</button>
                <a href="#" class="btn btn-primary" id="btnKelolaAbsen"><i class="fa-solid fa-clipboard-check me-2"></i>Kelola Absen</a>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Pertemuan --}}
<div class="modal fade" id="modalEditPertemuan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="formEditPertemuan">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title"><i class="fa-regular fa-pen-to-square me-2"></i>Edit Pertemuan</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Nama Pembelajaran</label><input type="text" class="form-control" id="edit_nama" readonly></div>
                    <div class="mb-3"><label class="form-label" for="edit_pertemuan_ke">Pertemuan Ke-</label><input type="number" class="form-control" id="edit_pertemuan_ke" name="pertemuan_ke" min="1" max="100" required></div>
                    <div class="mb-3"><label class="form-label" for="edit_tanggal">Tanggal</label><input type="date" class="form-control" id="edit_tanggal" name="tanggal" required></div>
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label" for="edit_jam_mulai">Pukul Mulai</label><input type="time" class="form-control" id="edit_jam_mulai" name="jam_mulai" required></div>
                        <div class="col-6"><label class="form-label" for="edit_jam_selesai">Pukul Selesai</label><input type="time" class="form-control" id="edit_jam_selesai" name="jam_selesai" required></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan Perubahan</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table-rekap{border-collapse:collapse}
.table-rekap thead th{background:#0e6b5a;color:#fff;border:1px solid #9aa5ac;font-size:12px;font-weight:700;text-align:center;vertical-align:middle;white-space:nowrap;padding:9px 8px}
.table-rekap thead th.total-head{background:#0b584a;border-left:1px solid #9aa5ac}
.table-rekap thead th.head-hadir{background:rgba(26,143,76,.85)}
.table-rekap thead th.head-sakit{background:rgba(223,170,67,.85)}
.table-rekap thead th.head-izin{background:rgba(47,111,237,.85)}
.table-rekap thead th.head-alpa{background:rgba(214,69,69,.85)}
.table-rekap thead th.head-terlambat{background:rgba(138,152,164,.85)}
.table-rekap thead .badge-status{background:transparent!important;width:auto;height:auto;min-width:0;padding:0}
.table-rekap thead tr:first-child th{border-top:1px solid #9aa5ac}
.table-rekap td{padding:9px 8px;border:1px solid #c9d0d5;vertical-align:middle;white-space:nowrap;font-size:13px}
.table-rekap .total-col{width:38px;min-width:38px;max-width:38px;padding-left:2px;padding-right:2px}
.table-rekap tbody tr:hover{background:#f5faf8}
.table-rekap thead th.meeting-head{padding:0}
.meeting-link{display:flex;align-items:center;justify-content:center;width:100%;min-width:44px;height:100%;min-height:34px;border:0;background:transparent;color:#fff;font-weight:800;cursor:pointer;line-height:1;padding:6px 10px;transition:background .15s ease}
.meeting-link:hover{background:#2f9e77;color:#fff}
.meeting-link:active{background:#26805f;color:#fff}
.badge-status{display:inline-grid;place-items:center;width:24px;height:24px;border-radius:6px;font-size:11px;font-weight:800;color:#fff}
.badge-success{background:#1a8f4c}.badge-warning{background:#dfaa43}.badge-info{background:#2f6fed}.badge-danger{background:#d64545}.badge-secondary{background:#8a98a4}
.table-rekap td.total-col-hadir{background:rgba(26,143,76,.85)}
.table-rekap td.total-col-sakit{background:rgba(223,170,67,.85)}
.table-rekap td.total-col-izin{background:rgba(47,111,237,.85)}
.table-rekap td.total-col-alpa{background:rgba(214,69,69,.85)}
.table-rekap td.total-col-terlambat{background:rgba(138,152,164,.85)}
.table-rekap td.cell-hadir{background:rgba(26,143,76,.85)}
.table-rekap td.cell-sakit{background:rgba(223,170,67,.85)}
.table-rekap td.cell-izin{background:rgba(47,111,237,.85)}
.table-rekap td.cell-alpa{background:rgba(214,69,69,.85)}
.table-rekap td.cell-terlambat{background:rgba(138,152,164,.85)}
.table-rekap td[class*="cell-"] .badge-status{background:transparent!important;width:auto;height:auto;min-width:0;padding:0}
.detail-card{border:1px solid #e9eef0;border-left:5px solid #f0c419;border-radius:8px;padding:16px 18px;background:#fff}
#modalDetailPertemuan .modal-content{background:#f3f6f8}
#modalDetailPertemuan .modal-header,#modalDetailPertemuan .modal-footer{background:#f3f6f8;border-color:#e4eaee}
.detail-row{display:flex;gap:8px;padding:4px 0;font-size:14px}
.detail-label{min-width:130px;font-weight:700;color:#20303c}
.detail-sep{color:#20303c}
.detail-value{color:#20303c}
.detail-actions{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
.detail-actions>form{flex:1 1 0;min-width:130px;margin:0}
.detail-actions>form .btn{width:100%}
.detail-actions .btn{flex:1 1 0;min-width:130px;white-space:nowrap}
@media print{.sidebar,.topbar,.page-heading .btn,.data-toolbar,.data-footer,.btn,.eyebrow{display:none!important}.panel{border:0!important;box-shadow:none!important}}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const detailModal = new bootstrap.Modal(document.getElementById('modalDetailPertemuan'));
    const editModal = new bootstrap.Modal(document.getElementById('modalEditPertemuan'));
    const detailBody = document.getElementById('detailBody');
    let current = null;

    const statusLabel = {hadir:'Hadir', sakit:'Sakit', izin:'Izin', alpa:'Alpa', terlambat:'Terlambat'};
    const statusColor = {hadir:'success', sakit:'warning', izin:'info', alpa:'danger', terlambat:'secondary'};
    const statusCode = {hadir:'H', sakit:'S', izin:'I', alpa:'A', terlambat:'T'};
    const statusOrder = ['hadir', 'sakit', 'izin', 'alpa', 'terlambat'];

    const openMeeting = async (id) => {
        detailBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div></div>';
        detailModal.show();
        try {
            const res = await fetch(`{{ url('guru/pertemuan') }}/${id}`, {headers: {'Accept': 'application/json'}});
            if (!res.ok) throw new Error('Gagal memuat detail');
            const d = await res.json();
            current = d;

            const badge = (k) => '<span class="badge-status badge-' + statusColor[k] + '">' + statusCode[k] + '</span>';
            detailBody.innerHTML = `
                <div class="detail-card">
                    <div class="detail-row"><span class="detail-label">Pembelajaran</span><span class="detail-sep">:</span><span class="detail-value">${d.nama_pembelajaran}${d.kelas ? ' - ' + d.kelas : ''}</span></div>
                    <div class="detail-row"><span class="detail-label">Pertemuan Ke</span><span class="detail-sep">:</span><span class="detail-value">${d.pertemuan_ke}</span></div>
                    <div class="detail-row"><span class="detail-label">Hari, Tanggal</span><span class="detail-sep">:</span><span class="detail-value">${d.tanggal}</span></div>
                    <div class="detail-row"><span class="detail-label">Pukul</span><span class="detail-sep">:</span><span class="detail-value">${d.jam}</span></div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    ${statusOrder.map(k => '<span class="d-inline-flex align-items-center gap-1">' + badge(k) + ' <span class="small">' + statusLabel[k] + ': <strong>' + (d.counts[k] ?? 0) + '</strong></span></span>').join('')}
                </div>`;

            document.getElementById('formHapusPertemuan').action = d.urls.destroy;
            document.getElementById('btnKelolaAbsen').href = d.urls.absen;
        } catch (e) {
            detailBody.innerHTML = '<div class="alert alert-danger mb-0">Gagal memuat detail pertemuan.</div>';
        }
    };

    document.querySelectorAll('.meeting-link').forEach(el => {
        el.addEventListener('click', () => openMeeting(el.dataset.meetingId));
    });

    document.getElementById('btnEditPertemuan')?.addEventListener('click', () => {
        if (!current) return;
        detailModal.hide();
        document.getElementById('formEditPertemuan').action = current.urls.edit;
        document.getElementById('edit_nama').value = current.nama_pembelajaran;
        document.getElementById('edit_pertemuan_ke').value = current.pertemuan_ke;
        const parts = current.jam.split(' s/d ');
        document.getElementById('edit_jam_mulai').value = parts[0] ?? '';
        document.getElementById('edit_jam_selesai').value = parts[1] ?? '';
        editModal.show();
    });
});
</script>
@endpush
