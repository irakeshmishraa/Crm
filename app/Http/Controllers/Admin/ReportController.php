<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\FollowUp;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index() { return view('reports.index'); }
    public function leads(Request $request) {
        $from = $request->get('date_from', now()->startOfMonth()->toDateString());
        $to = $request->get('date_to', now()->toDateString());
        $data = [
            'total' => Lead::whereBetween('created_at', [$from, $to])->count(),
            'by_status' => Lead::whereBetween('created_at', [$from, $to])->select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status'),
            'by_source' => Lead::whereBetween('created_at', [$from, $to])->select('source', DB::raw('count(*) as count'))->groupBy('source')->pluck('count', 'source'),
        ];
        return view('reports.leads', compact('data', 'from', 'to'));
    }
    public function sales(Request $request) {
        $from = $request->get('date_from', now()->startOfMonth()->toDateString());
        $to = $request->get('date_to', now()->toDateString());
        $data = ['total_won' => Lead::where('status', 'won')->whereBetween('converted_at', [$from, $to])->count(), 'total_revenue' => Quotation::where('status', 'accepted')->whereBetween('accepted_at', [$from, $to])->sum('grand_total')];
        return view('reports.sales', compact('data', 'from', 'to'));
    }
    public function followups(Request $request) { return view('reports.followups'); }
    public function quotations(Request $request) { return view('reports.quotations'); }
    public function revenue(Request $request) { return view('reports.revenue'); }
    public function team(Request $request) {
        $users = User::where('status', 'active')->withCount(['assignedLeads', 'followUps' => fn($q) => $q->where('status', 'completed')])->get();
        return view('reports.team', compact('users'));
    }
    public function export(string $type, string $format) { return back()->with('info', 'Export available.'); }
}
