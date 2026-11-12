@extends('layouts.app')
@section('title', $title)
@section('topbar', 'Data '.$title)
@section('content')
<div class="page-heading"><div><h1>{{ $title }}</h1><p>Kelola data {{ strtolower($title) }} sekolah.</p></div><a href="{{ route('admin.data.create', $resource) }}" class="btn text-white" style="background:#2f6fed;border-color:#2f6fed"><i class="fa-solid fa-plus me-2"></i>Tambah Data</a></div>
<section class="panel">
    <form class="data-toolbar" method="GET" action="{{ route('admin.data.index', $resource) }}">
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
                <a class="btn btn-sm btn-light" href="{{ route('admin.data.index', $resource) }}">Reset</a>
            @endif
        </div>
    </form>
    <div class="table-responsive"><table class="table table-data mb-0"><thead><tr><th style="width:55px">No.</th>@foreach($tableFields as $field=>$label)<th>{{ $label }}</th>@endforeach<th class="text-end">Aksi</th></tr></thead><tbody>
@forelse($rows as $row)<tr><td>{{ $rows->firstItem() + $loop->index }}</td>@foreach($tableFields as $field=>$label)<td>@if($resource === 'jadwal' && $field === 'hari'){{ $row->hari ?? '-' }}@elseif($resource === 'jadwal' && $field === 'guru_id'){{ $row->teacher?->nama ?? '-' }}@elseif($resource === 'jadwal' && $field === 'kelas_id'){{ $row->schoolClass?->nama_kelas ?? '-' }}@elseif($resource === 'jadwal' && $field === 'mata_pelajaran_id'){{ $row->subject?->nama_mapel ?? '-' }}@elseif($resource === 'jadwal' && $field === 'tahun_ajaran_id'){{ $row->academicYear?->tahun_ajaran ?? '-' }}@elseif($resource === 'jadwal' && $field === 'semester'){{ $row->academicYear?->semester ?? '-' }}@elseif($resource === 'murid' && $field === 'kelas_id'){{ $row->schoolClass?->nama_kelas ?? '-' }}@elseif($field === 'password')••••••••@elseif($field === 'status')<span class="badge text-bg-{{ $row->{$field} ? 'success' : 'secondary' }}">{{ $row->{$field} ? 'Aktif' : 'Tidak aktif' }}</span>@elseif($field === 'wali_kelas_id'){{ $row->homeroomTeacher?->nama ?? '-' }}@elseif($field === 'user_id'){{ $row->user?->name ?? '-' }}@elseif($field === 'role'){{ str_replace('_',' ',ucfirst($row->{$field})) }}@elseif($field === 'jenis_kelamin'){{ ['L' => 'Laki-laki', 'P' => 'Perempuan'][$row->{$field}] ?? '-' }}@elseif($field === 'alamat'){{ \Illuminate\Support\Str::limit($row->{$field},35) ?: '-' }}@else{{ $row->{$field} ?: '-' }}@endif</td>@endforeach<td class="text-end text-nowrap">@if(in_array($resource, ['guru', 'murid'], true))<button type="button" class="btn-action btn-view" aria-label="Lihat" title="Lihat" data-bs-toggle="modal" data-bs-target="#viewModal{{ $row->id }}"><i class="fa-regular fa-eye"></i></button> @endif<a class="btn-action btn-edit" aria-label="Edit" title="Edit" href="{{ route('admin.data.edit', [$resource, $row->id]) }}"><i class="fa-regular fa-pen-to-square"></i></a><form class="d-inline" method="POST" action="{{ route('admin.data.destroy', [$resource, $row->id]) }}" data-confirm="Hapus data ini?">@csrf @method('DELETE')<button class="btn-action btn-delete" aria-label="Hapus" title="Hapus"><i class="fa-regular fa-trash-can"></i></button></form></td></tr>
@empty<tr><td colspan="{{ count($tableFields)+2 }}"><div class="empty-state"><i class="fa-regular fa-folder-open"></i><div>Belum ada data {{ strtolower($title) }}.</div></div></td></tr>@endforelse
</tbody></table></div>
    <div class="data-footer">
        <div class="data-info">Menampilkan <strong>{{ $rows->firstItem() ?? 0 }}</strong>–<strong>{{ $rows->lastItem() ?? 0 }}</strong> dari <strong>{{ $rows->total() }}</strong> data</div>
        <div class="data-pagination">{{ $rows->links('vendor.pagination.custom') }}</div>
    </div>
</section>

@if(in_array($resource, ['guru', 'murid'], true))
    @foreach($rows as $row)
    <div class="modal fade" id="viewModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:520px">
            <div class="modal-content" style="border-radius:14px;overflow:hidden">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Detail {{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="text-center mb-3">
                        <div class="detail-avatar"><i class="fa-solid fa-user"></i></div>
                    </div>
                    <div class="detail-list">
                        @php
                            $detailOrder = array_keys($tableFields);
                            $detailFields = [];
                            foreach ($detailOrder as $f) { $detailFields[$f] = $fields[$f]; }
                            foreach ($fields as $f => $l) { if (! isset($detailFields[$f])) $detailFields[$f] = $l; }
                        @endphp
                        @foreach($detailFields as $field => $label)
                            @continue(in_array($field, ['password', 'password_confirmation'], true))
                            @php
                                $value = match ($field) {
                                    'user_id' => $row->user?->name,
                                    'username' => $row->user?->username,
                                    'kelas_id' => $row->schoolClass?->nama_kelas,
                                    'jenis_kelamin' => ['L' => 'Laki-laki', 'P' => 'Perempuan'][$row->{$field}] ?? null,
                                    default => $row->{$field},
                                };
                            @endphp
                            <div class="detail-row">
                                <span class="detail-label">{{ $label }}</span>
                                <span class="detail-sep">:</span>
                                <span class="detail-value">
                                    @if($field === 'status')
                                        <span class="badge rounded-1 text-uppercase" style="background:{{ $row->status ? '#1a8f4c' : '#8a98a4' }}">{{ $row->status ? 'Aktif' : 'Tidak Aktif' }}</span>
                                    @else
                                        {{ $value ?: '-' }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <a href="{{ route('admin.data.edit', [$resource, $row->id]) }}" class="btn text-white" style="background:#2f6fed;border-color:#2f6fed"><i class="fa-regular fa-pen-to-square me-2"></i>Edit</a>
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif
@endsection

@push('styles')
<style>
.detail-avatar{width:110px;height:110px;border-radius:50%;background:#e9ecef;border:3px solid #dee2e6;display:grid;place-items:center;margin:0 auto;color:#adb5bd;font-size:52px}
.detail-list{border-top:1px solid #e9ecef}
.detail-row{display:flex;align-items:center;gap:10px;padding:11px 4px;border-bottom:1px solid #e9ecef;font-size:14px}
.detail-label{font-weight:700;flex:0 0 40%}
.detail-sep{color:#adb5bd}
.detail-value{flex:1;word-break:break-word}
</style>
@endpush
