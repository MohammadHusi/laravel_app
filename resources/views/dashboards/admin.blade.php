@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('topbar', 'Ringkasan sekolah')
@section('content')
<div class="page-heading"><div><h1>Dashboard Admin</h1><p>Pantau aktivitas dan kehadiran sekolah hari ini.</p></div></div>
<div class="row g-3 mb-4">
    @foreach([['Total Murid', $students, 'fa-user-graduate'], ['Total Guru', $teachers, 'fa-chalkboard-user'], ['Total Kelas', $classes, 'fa-school'], ['Absensi Hari Ini', $today, 'fa-clipboard-check']] as [$label, $value, $icon])
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="d-flex justify-content-between align-items-start"><span class="stat-label">{{ $label }}</span><span class="stat-icon"><i class="fa-solid {{ $icon }}"></i></span></div><div class="stat-value">{{ number_format($value) }}</div><div class="stat-note">Data tercatat di sistem</div></div></div>
    @endforeach
</div>
<div class="row g-3 mb-4">
    <div class="col-xl-7"><section class="panel h-100"><div class="d-flex justify-content-between align-items-start mb-3"><div><h2 class="panel-title">Tren absensi</h2><p class="panel-subtitle">Rekap enam bulan terakhir</p></div><span class="badge text-bg-light">Per bulan</span></div><div style="height:270px"><canvas id="monthlyChart" aria-label="Grafik absensi bulanan"></canvas></div></section></div>
    <div class="col-xl-5"><section class="panel h-100"><h2 class="panel-title">Kehadiran hari ini</h2><p class="panel-subtitle mb-2">{{ array_sum($todayByStatus) }} catatan absensi</p><div style="height:205px"><canvas id="todayChart" aria-label="Grafik status absensi hari ini"></canvas></div><div class="row g-2 mt-2">@foreach($todayByStatus as $status => $count)<div class="col-6 d-flex justify-content-between small"><span><span class="badge-status status-{{ $status }}">{{ $status }}</span></span><strong>{{ $count }}</strong></div>@endforeach</div></section></div>
</div>
<section class="panel"><div class="d-flex justify-content-between align-items-center mb-3"><div><h2 class="panel-title">Aktivitas absensi terbaru</h2><p class="panel-subtitle">Catatan terakhir yang masuk ke sistem</p></div><a class="small text-decoration-none" href="{{ route('admin.laporan') }}">Semua laporan <i class="fa-solid fa-arrow-right ms-1"></i></a></div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Murid</th><th>Kelas</th><th>Mata Pelajaran</th><th>Tanggal</th><th>Status</th></tr></thead><tbody>
        @forelse($recent as $attendance)<tr><td class="fw-semibold">{{ $attendance->student?->nama ?? 'Murid dihapus' }}</td><td>{{ $attendance->schoolClass?->nama_kelas ?? '-' }}</td><td>{{ $attendance->subject?->nama_mapel ?? '-' }}</td><td>{{ $attendance->tanggal->translatedFormat('d M Y') }}</td><td><span class="badge-status status-{{ $attendance->status }}">{{ $attendance->status }}</span></td></tr>
        @empty<tr><td colspan="5"><div class="empty-state"><i class="fa-regular fa-clipboard"></i><div>Belum ada catatan absensi.</div></div></td></tr>@endforelse
    </tbody></table></div>
</section>
@endsection
@push('scripts')
<script>
const monthly = @json($monthly), today = @json($todayByStatus), palette = ['#1a8f4c','#dfaa43','#6b91bb','#d56b6b'];
new Chart(document.getElementById('monthlyChart'), {type:'bar',data:{labels:monthly.map(row=>row.label),datasets:[{label:'Hadir',data:monthly.map(row=>row.hadir),backgroundColor:palette[0],borderRadius:4},{label:'Izin',data:monthly.map(row=>row.izin),backgroundColor:palette[1],borderRadius:4},{label:'Sakit',data:monthly.map(row=>row.sakit),backgroundColor:palette[2],borderRadius:4},{label:'Alpa',data:monthly.map(row=>row.alpa),backgroundColor:palette[3],borderRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{usePointStyle:true,boxWidth:7}}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{precision:0},grid:{color:'#edf1f3'}}}}});
new Chart(document.getElementById('todayChart'), {type:'doughnut',data:{labels:['Hadir','Izin','Sakit','Alpa'],datasets:[{data:[today.hadir,today.izin,today.sakit,today.alpa],backgroundColor:palette,borderWidth:0,hoverOffset:4}]},options:{responsive:true,maintainAspectRatio:false,cutout:'72%',plugins:{legend:{display:false}}}});
</script>
@endpush