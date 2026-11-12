@extends('layouts.app')
@section('title', 'Data Mapel')
@section('topbar', 'Data Mapel')
@section('content')
<div class="page-heading"><div><h1>Data Mapel</h1><p>Kelola data mata pelajaran sekolah.</p></div><a href="{{ route('admin.data.create', 'jadwal') }}" class="btn text-white" style="background:#2f6fed;border-color:#2f6fed"><i class="fa-solid fa-plus me-2"></i>Tambah Data</a></div>
<section class="panel">
    <form class="data-toolbar" method="GET" action="{{ route('admin.datapembelajaran') }}">
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
                <a class="btn btn-sm btn-light" href="{{ route('admin.datapembelajaran') }}">Reset</a>
            @endif
        </div>
    </form>
    <div class="table-responsive"><table class="table table-data mb-0"><thead><tr><th style="width:55px">No.</th><th>Mata Pelajaran</th><th>Kelas</th><th>Guru</th><th>Tahun Pelajaran</th><th>Semester</th><th class="text-end">Aksi</th></tr></thead><tbody>
@forelse($rows as $row)<tr>
    <td>{{ $rows->firstItem() + $loop->index }}</td>
    <td class="fw-semibold">{{ $row->subject?->nama_mapel ?? '-' }}</td>
    <td>{{ $row->schoolClass?->nama_kelas ?? '-' }}</td>
    <td>{{ $row->teacher?->nama ?? '-' }}</td>
    <td>{{ $row->academicYear?->tahun_ajaran ?? '-' }}</td>
    <td>{{ $row->academicYear?->semester ?? '-' }}</td>
    <td class="text-end text-nowrap">
        <a class="btn-action btn-edit" aria-label="Edit" title="Edit" href="{{ route('admin.data.edit', ['jadwal', $row->id]) }}"><i class="fa-regular fa-pen-to-square"></i></a>
        <form class="d-inline" method="POST" action="{{ route('admin.data.destroy', ['jadwal', $row->id]) }}" data-confirm="Hapus data ini?">@csrf @method('DELETE')<button class="btn-action btn-delete" aria-label="Hapus" title="Hapus"><i class="fa-regular fa-trash-can"></i></button></form>
    </td>
</tr>
@empty<tr><td colspan="7"><div class="empty-state"><i class="fa-regular fa-folder-open"></i><div>Belum ada data pembelajaran.</div></div></td></tr>@endforelse
</tbody></table></div>
    <div class="data-footer">
        <div class="data-info">Menampilkan <strong>{{ $rows->firstItem() ?? 0 }}</strong>–<strong>{{ $rows->lastItem() ?? 0 }}</strong> dari <strong>{{ $rows->total() }}</strong> data</div>
        <div class="data-pagination">{{ $rows->links() }}</div>
    </div>
</section>
@endsection

