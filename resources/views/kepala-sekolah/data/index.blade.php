@extends('layouts.app')
@section('title', $title)
@section('topbar', 'Monitoring sekolah')
@section('content')
<div class="page-heading"><div><div class="eyebrow">Monitoring / Data Sekolah</div><h1>{{ $title }}</h1><p>Data ini hanya dapat dilihat dan tidak dapat diubah.</p></div></div>
<section class="panel"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>No.</th>@foreach($fields as $field=>$label)<th>{{ $label }}</th>@endforeach</tr></thead><tbody>@forelse($rows as $row)<tr><td>{{ $rows->firstItem()+$loop->index }}</td>@foreach($fields as $field=>$label)<td>{{ $row->{$field} ?: '-' }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($fields)+1 }}"><div class="empty-state"><i class="fa-regular fa-folder-open"></i><div>Belum ada data.</div></div></td></tr>@endforelse</tbody></table></div><div class="mt-3">{{ $rows->links() }}</div></section>
@endsection