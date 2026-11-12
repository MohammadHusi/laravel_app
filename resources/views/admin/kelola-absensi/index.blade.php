@extends('layouts.app')
@section('title', 'Kelola Absensi')
@section('topbar', 'Kelola absensi')
@section('content')
<div class="page-heading"><div><h1>Kelola Absensi</h1><p>Daftar mata pelajaran beserta kelas dan tahun pelajaran.</p></div></div>

<section class="panel mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div><h2 class="panel-title mb-1">Tabel Data</h2><p class="panel-subtitle mb-0">Daftar mata pelajaran beserta kelas dan tahun pelajaran.</p></div>
    </div>
    <div class="table-responsive">
        <table class="table table-data mb-0">
            <thead>
                <tr>
                    <th style="width:55px">No.</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas</th>
                    <th>Guru</th>
                    <th>Tahun Ajaran</th>
                    <th>Semester</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($schedules as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $item->subject?->nama_mapel ?? '-' }}</td>
                    <td>{{ $item->schoolClass?->nama_kelas ?? '-' }}</td>
                    <td>{{ $item->teacher?->nama ?? '-' }}</td>
                    <td>{{ $item->academicYear?->tahun_ajaran ?? '-' }}</td>
                    <td>{{ $item->academicYear?->semester ?? '-' }}</td>
                    <td class="text-center text-nowrap">
                        <a class="btn btn-sm btn-success" href="{{ route('guru.pertemuan.matriks', ['jadwal' => $item->id]) }}"><i class="fa-solid fa-clipboard-check me-1"></i>Isi Absen</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fa-regular fa-folder-open"></i><div>Belum ada jadwal.</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

