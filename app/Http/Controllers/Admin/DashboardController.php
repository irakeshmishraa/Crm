<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\FollowUp;
use App\Models\Client;
use App\Models\Quotation;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        $lq = fn() => Lead::when(!$isAdmin, fn($q) => $q->where('assigned_to', $user->id));

        $data = [
            'total_leads' => $lq()->count(),
            'new_leads_today' => $lq()->today()->count(),
            'new_leads_week' => $lq()->thisWeek()->count(),
            'new_leads_month' => $lq()->thisMonth()->count(),
            'followups_due' => FollowUp::when(!$isAdmin, fn($q) => $q->where('assigned_to', $user->id))->dueToday()->count(),
            'overdue_followups' => FollowUp::when(!$isAdmin, fn($q) => $q->where('assigned_to', $user->id))->overdue()->count(),
            'meetings_scheduled' => FollowUp::when(!$isAdmin, fn($q) => $q->where('assigned_to', $user->id))->where('type', 'meeting')->pending()->count(),
            'quotations_sent' => Quotation::when(!$isAdmin, fn($q) => $q->where('created_by', $user->id))->where('status', 'sent')->count(),
            'quotations_accepted' => Quotation::when(!$isAdmin, fn($q) => $q->where('created_by', $user->id))->where('status', 'accepted')->count(),
            'won_deals' => $lq()->won()->thisMonth()->count(),
            'lost_deals' => $lq()->lost()->thisMonth()->count(),
            'active_clients' => Client::when(!$isAdmin, fn($q) => $q->where('assigned_to', $user->id))->active()->count(),
        ];

        $total = $lq()->thisMonth()->count();
        $data['conversion_rate'] = $total > 0 ? round(($data['won_deals'] / $total) * 100, 1) : 0;
        $data['revenue_forecast'] = Lead::when(!$isAdmin, fn($q) => $q->where('assigned_to', $user->id))->whereIn('status', ['interested', 'proposal_sent', 'negotiation'])->sum(DB::raw('deal_value * win_probability / 100'));


        $data['todays_tasks'] = Task::when(!$isAdmin, fn($q) => $q->where('assigned_to', $user->id))->whereDate('due_date', today())->where('status', '!=', 'completed')->limit(5)->get();
        $data['upcoming_followups'] = FollowUp::when(!$isAdmin, fn($q) => $q->where('assigned_to', $user->id))->upcoming()->with('lead')->limit(5)->get();
        $data['recent_leads'] = $lq()->with('assignedTo')->latest()->limit(5)->get();
        $data['lead_sources'] = $lq()->select('source', DB::raw('count(*) as count'))->groupBy('source')->pluck('count', 'source')->toArray();
        $data['lead_growth'] = $lq()->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('count(*) as count'))->where('created_at', '>=', now()->subMonths(6))->groupBy('month')->orderBy('month')->pluck('count', 'month')->toArray();
        $data['pipeline_funnel'] = $lq()->select('pipeline_stage', DB::raw('count(*) as count'))->groupBy('pipeline_stage')->pluck('count', 'pipeline_stage')->toArray();

        return view('dashboard.index', $data);
    }

    public function stats(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        $period = $request->get('period', 'month');
        $startDate = match ($period) { 'today' => today(), 'week' => now()->startOfWeek(), 'quarter' => now()->startOfQuarter(), 'year' => now()->startOfYear(), default => now()->startOfMonth() };
        return response()->json([
            'leads' => Lead::when(!$isAdmin, fn($q) => $q->where('assigned_to', $user->id))->where('created_at', '>=', $startDate)->count(),
            'conversions' => Lead::when(!$isAdmin, fn($q) => $q->where('assigned_to', $user->id))->where('status', 'won')->where('converted_at', '>=', $startDate)->count(),
            'revenue' => Quotation::when(!$isAdmin, fn($q) => $q->where('created_by', $user->id))->where('status', 'accepted')->where('accepted_at', '>=', $startDate)->sum('grand_total'),
        ]);
    }
}
