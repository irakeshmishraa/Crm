@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Dashboard</h4>
    <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-primary active" data-period="month">Month</button>
        <button class="btn btn-outline-primary" data-period="week">Week</button>
        <button class="btn btn-outline-primary" data-period="today">Today</button>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="d-flex justify-content-between"><div><p class="text-muted small mb-1">Total Leads</p><h3 class="fw-bold mb-0">{{ number_format($total_leads) }}</h3></div><div class="bg-primary bg-opacity-10 rounded-3 p-3"><i class="bi bi-people fs-4 text-primary"></i></div></div><small class="text-success"><i class="bi bi-arrow-up"></i> {{ $new_leads_today }} today</small></div></div></div>
    <div class="col-xl-3 col-sm-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="d-flex justify-content-between"><div><p class="text-muted small mb-1">Follow-Ups Due</p><h3 class="fw-bold mb-0">{{ $followups_due }}</h3></div><div class="bg-warning bg-opacity-10 rounded-3 p-3"><i class="bi bi-telephone fs-4 text-warning"></i></div></div><small class="text-danger"><i class="bi bi-exclamation-triangle"></i> {{ $overdue_followups }} overdue</small></div></div></div>
    <div class="col-xl-3 col-sm-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="d-flex justify-content-between"><div><p class="text-muted small mb-1">Won Deals</p><h3 class="fw-bold mb-0">{{ $won_deals }}</h3></div><div class="bg-success bg-opacity-10 rounded-3 p-3"><i class="bi bi-trophy fs-4 text-success"></i></div></div><small class="text-muted">{{ $conversion_rate }}% conversion</small></div></div></div>
    <div class="col-xl-3 col-sm-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="d-flex justify-content-between"><div><p class="text-muted small mb-1">Revenue Forecast</p><h3 class="fw-bold mb-0">@currency($revenue_forecast)</h3></div><div class="bg-info bg-opacity-10 rounded-3 p-3"><i class="bi bi-currency-rupee fs-4 text-info"></i></div></div><small class="text-muted">{{ $quotations_sent }} quotes sent</small></div></div></div>
</div>
<div class="row g-3 mb-4">
    <div class="col-xl-8"><div class="card border-0 shadow-sm"><div class="card-header bg-transparent border-0"><h6 class="mb-0">Lead Growth</h6></div><div class="card-body"><canvas id="leadGrowthChart" height="100"></canvas></div></div></div>
    <div class="col-xl-4"><div class="card border-0 shadow-sm"><div class="card-header bg-transparent border-0"><h6 class="mb-0">Lead Sources</h6></div><div class="card-body"><canvas id="leadSourceChart" height="200"></canvas></div></div></div>
</div>
<div class="row g-3">
    <div class="col-xl-6"><div class="card border-0 shadow-sm"><div class="card-header bg-transparent border-0 d-flex justify-content-between"><h6 class="mb-0">Recent Leads</h6><a href="{{ route('admin.leads.index') }}" class="small">View All</a></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><tbody>@foreach($recent_leads as $lead)<tr><td><div class="fw-semibold">{{ $lead->name }}</div><small class="text-muted">{{ $lead->company_name }}</small></td><td><span class="badge bg-{{ $lead->status_badge }}">{{ ucfirst(str_replace('_',' ',$lead->status)) }}</span></td><td class="text-muted small">{{ $lead->created_at->diffForHumans() }}</td></tr>@endforeach</tbody></table></div></div></div></div>
    <div class="col-xl-6"><div class="card border-0 shadow-sm"><div class="card-header bg-transparent border-0 d-flex justify-content-between"><h6 class="mb-0">Upcoming Follow-Ups</h6><a href="{{ route('admin.followups.index') }}" class="small">View All</a></div><div class="card-body p-0"><div class="list-group list-group-flush">@forelse($upcoming_followups as $f)<a href="{{ route('admin.followups.show', $f) }}" class="list-group-item list-group-item-action"><div class="d-flex justify-content-between"><strong>{{ $f->title }}</strong><small class="text-muted">{{ $f->scheduled_at->format('d M, h:i A') }}</small></div></a>@empty<div class="list-group-item text-center text-muted">No upcoming follow-ups</div>@endforelse</div></div></div></div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    new Chart(document.getElementById('leadGrowthChart'), {type:'line',data:{labels:{!! json_encode(array_keys($lead_growth)) !!},datasets:[{label:'Leads',data:{!! json_encode(array_values($lead_growth)) !!},borderColor:'#4f46e5',backgroundColor:'rgba(79,70,229,0.1)',fill:true,tension:0.4}]},options:{responsive:true,plugins:{legend:{display:false}}}});
    new Chart(document.getElementById('leadSourceChart'), {type:'doughnut',data:{labels:{!! json_encode(array_keys($lead_sources)) !!},datasets:[{data:{!! json_encode(array_values($lead_sources)) !!},backgroundColor:['#4f46e5','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#84cc16']}]},options:{responsive:true,plugins:{legend:{position:'bottom'}}}});
});
</script>
@endpush
