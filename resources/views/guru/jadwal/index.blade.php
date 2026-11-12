@extends('layouts.app')
@section('title', 'Jadwal')
@section('topbar', 'Jadwal mengajar')
@section('content')
<div class="page-heading">
    <div>
        <h1>Jadwal Mengajar</h1>
        <p>Daftar jadwal mengajar Anda beserta kelas, mata pelajaran, dan jam pembelajaran.</p>
    </div>
</div>

<section class="panel">
    <form class="data-toolbar" method="GET" action="{{ route('guru.jadwal.index') }}">
        <div class="data-toolbar-left">
            <label for="per_page">Tampilkan</label>
            <select id="per_page" name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach([10, 25, 50, 100] as $size)
                    <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                @endforeach
            </select>
            <span>data</span>
        </div>
        <div class="data-toolbar-right">
            <div class="data-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search...">
            </div>
            <button class="btn btn-sm btn-primary" type="submit">Cari</button>
            @if($search !== '')
                <a class="btn btn-sm btn-light" href="{{ route('guru.jadwal.index') }}">Reset</a>
            @endif
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-data mb-0">
            <thead>
                <tr>
                    <th style="width:55px">No.</th>
                    <th>Hari</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Mata Pelajaran</th>
                    <th>Guru</th>
                    <th>Kelas</th>
                    <th>Tahun Ajaran</th>
                    <th>Semester</th>
                </tr>
            </thead>
            <tbody>
            @forelse($schedules as $schedule)
                <tr>
                    <td>{{ $schedules->firstItem() + $loop->index }}</td>
                    <td>{{ $schedule->hari ?: '-' }}</td>
                    <td>{{ $schedule->jam_mulai ? substr($schedule->jam_mulai, 0, 5) : '-' }}</td>
                    <td>{{ $schedule->jam_selesai ? substr($schedule->jam_selesai, 0, 5) : '-' }}</td>
                    <td class="fw-semibold">{{ $schedule->subject?->nama_mapel ?? '-' }}</td>
                    <td>{{ $schedule->teacher?->nama ?? '-' }}</td>
                    <td>{{ $schedule->schoolClass?->nama_kelas ?? '-' }}</td>
                    <td>{{ $schedule->academicYear?->tahun_ajaran ?? '-' }}</td>
                    <td>{{ $schedule->academicYear?->semester ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="9"><div class="empty-state"><i class="fa-regular fa-calendar-xmark"></i><div>Belum ada jadwal mengajar untuk akun ini.</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="data-footer">
        <div class="data-info">Menampilkan <strong>{{ $schedules->firstItem() ?? 0 }}</strong>–<strong>{{ $schedules->lastItem() ?? 0 }}</strong> dari <strong>{{ $schedules->total() }}</strong> data</div>
        <div class="data-pagination">{{ $schedules->links() }}</div>
    </div>
</section>
@endsection
