@extends('layouts.app')
@section('title', 'Detail '.$title)
@section('topbar', 'Detail '.$title)
@section('content')
<div class="page-heading"><div><h1>Detail {{ $title }}</h1><p>Informasi lengkap profil {{ strtolower($title) }}.</p></div><div class="d-flex gap-2"><a href="{{ route('admin.data.edit', [$resource, $record->id]) }}" class="btn btn-outline-secondary"><i class="fa-regular fa-pen-to-square me-2"></i>Edit</a><a href="{{ route('admin.data.index', $resource) }}" class="btn btn-primary"><i class="fa-solid fa-arrow-left me-2"></i>Kembali</a></div></div>
<section class="panel"><div class="row g-3">
@foreach($fields as $field => $label)
    @continue(in_array($field, ['password', 'password_confirmation'], true))
    @php
        $value = match ($field) {
            'user_id' => $record->user?->name,
            'username' => $record->user?->username,
            'kelas_id' => $record->schoolClass?->nama_kelas,
            'jenis_kelamin' => ['L' => 'Laki-laki', 'P' => 'Perempuan'][$record->jenis_kelamin] ?? null,
            'status' => $record->status ? 'Aktif' : 'Tidak aktif',
            default => $record->{$field},
        };
    @endphp
    <div class="col-md-6"><div class="text-secondary small mb-1">{{ $label }}</div><div class="fw-semibold">{{ $value ?: '-' }}</div></div>
@endforeach
</div></section>
@endsection