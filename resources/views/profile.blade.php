@extends('layouts.app')
@section('title', 'Profil')
@section('content')
<div class="page-heading"><div><h1>Profil Saya</h1><p>Informasi akun yang sedang digunakan.</p></div></div>

@if(session('success'))
    <div class="alert alert-success" style="max-width:700px">{{ session('success') }}</div>
@endif

<section class="panel" style="max-width:700px"><div class="d-flex align-items-center gap-3 mb-4"><div class="avatar" style="width:54px;height:54px;font-size:20px">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div><div><h2 class="h5 fw-bold mb-1">{{ auth()->user()->name }}</h2><span class="text-secondary">{{ auth()->user()->email }}</span></div></div>
    <div class="row g-3"><div class="col-md-6"><div class="form-label">Username</div><div>{{ auth()->user()->username ?: 'Belum diatur' }}</div></div><div class="col-md-6"><div class="form-label">Hak akses</div><div class="text-capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</div></div><div class="col-md-6"><div class="form-label">Bergabung</div><div>{{ auth()->user()->created_at?->translatedFormat('d F Y') }}</div></div></div>
</section>

{{-- Form edit username --}}
<section class="panel mt-3" style="max-width:700px">
    <h2 class="h6 fw-bold mb-3"><i class="fa-solid fa-user-pen me-2"></i>Ubah Username</h2>
    <form method="POST" action="{{ route('profile.username') }}" autocomplete="off">
        @csrf
        @method('PUT')
        <div class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label" for="username">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username" value="{{ old('username', auth()->user()->username) }}" required>
                @error('username')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary w-100"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan</button>
            </div>
        </div>
    </form>
</section>

{{-- Form ganti password --}}
<section class="panel mt-3" style="max-width:700px">
    <h2 class="h6 fw-bold mb-3"><i class="fa-solid fa-lock me-2"></i>Ganti Password</h2>
    <form method="POST" action="{{ route('profile.password') }}" autocomplete="off">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label" for="current_password">Password Saat Ini <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password" required>
                @error('current_password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password">Password Baru <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" minlength="10" required>
                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password" minlength="10" required>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-primary"><i class="fa-solid fa-key me-2"></i>Ganti Password</button>
        </div>
    </form>
</section>
@endsection