@extends('layouts.app')
@section('title', 'Absensi Pembelajaran')
@section('topbar', 'Absensi pembelajaran')
@section('content')
<div class="page-heading">
    <div><div class="eyebrow">Guru / Absensi Pembelajaran</div><h1>Pilih Kelompok Kelas</h1><p>Pilih kelas yang Anda ajar untuk melihat rekap matriks absensi.</p></div>
</div>

@php
    $totalSchedules = $schedules->count();
    $totalClasses = $classes->count();
@endphp

@if($classes->isEmpty())
    <section class="panel"><div class="empty-state"><i class="fa-solid fa-chalkboard-user"></i><div>Belum ada jadwal mengajar untuk akun ini.</div></div></section>
@else
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4"><div class="stat-card"><div class="d-flex justify-content-between"><span class="stat-label">Kelompok Kelas</span><span class="stat-icon"><i class="fa-solid fa-school"></i></span></div><div class="stat-value">{{ $totalClasses }}</div><div class="stat-note">Kelas yang Anda ampu</div></div></div>
        <div class="col-6 col-md-4"><div class="stat-card"><div class="d-flex justify-content-between"><span class="stat-label">Jadwal Mengajar</span><span class="stat-icon"><i class="fa-solid fa-clock"></i></span></div><div class="stat-value">{{ $totalSchedules }}</div><div class="stat-note">Total sesi terjadwal</div></div></div>
        <div class="col-12 col-md-4"><div class="stat-card"><div class="d-flex justify-content-between"><span class="stat-label">Guru</span><span class="stat-icon"><i class="fa-solid fa-chalkboard-user"></i></span></div><div class="stat-value" style="font-size:18px">{{ $teacher->nama }}</div><div class="stat-note">Akun pengajar aktif</div></div></div>
    </div>

    <div class="row g-3">
        @foreach($classes as $kelasId => $group)
            @php $first = $group->first(); @endphp
            <div class="col-md-6 col-xl-4">
                <section class="panel class-card h-100">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="class-icon"><i class="fa-solid fa-users-rectangle"></i></div>
                        <span class="badge rounded-pill text-bg-light">{{ $group->count() }} jadwal</span>
                    </div>
                    <h2 class="panel-title mb-1">{{ $first->schoolClass?->nama_kelas ?? 'Kelas' }}</h2>
                    <p class="panel-subtitle mb-3">{{ $first->schoolClass?->jurusan ?: 'Kelompok belajar' }}{{ $first->schoolClass?->tingkat ? ' · Tingkat '.$first->schoolClass->tingkat : '' }}</p>
                    <div class="class-subjects mb-3">
                        @foreach($group as $schedule)
                            <a class="subject-chip" href="{{ route('guru.pertemuan.matriks', ['jadwal' => $schedule->id]) }}">
                                <i class="fa-solid fa-book-open"></i>
                                <span>{{ $schedule->subject?->nama_mapel ?? 'Mata Pelajaran' }}</span>
                                <small>{{ $schedule->hari }} {{ substr($schedule->jam_mulai,0,5) }}</small>
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ route('guru.pertemuan.matriks', ['jadwal' => $first->id]) }}" class="btn btn-primary w-100"><i class="fa-solid fa-table-cells-large me-2"></i>Buka Rekap Matriks</a>
                </section>
            </div>
        @endforeach
    </div>
@endif
@endsection

@push('styles')
<style>
.class-card{transition:box-shadow .18s,transform .18s}
.class-card:hover{box-shadow:0 10px 26px rgba(15,61,44,.10);transform:translateY(-2px)}
.class-icon{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;background:#e2f3ec;color:#18734f;font-size:19px}
.class-subjects{display:flex;flex-direction:column;gap:8px}
.subject-chip{display:flex;align-items:center;gap:10px;padding:9px 12px;border:1px solid var(--line);border-radius:8px;text-decoration:none;color:var(--ink);font-size:13px;font-weight:600;transition:background .15s,border-color .15s}
.subject-chip:hover{background:#f2faf5;border-color:#bfe3cf;color:#18734f}
.subject-chip i{color:#1a8f4c}
.subject-chip small{margin-left:auto;color:var(--muted);font-weight:500;font-size:11px}
</style>
@endpush

