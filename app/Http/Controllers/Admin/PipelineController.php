<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PipelineController extends Controller
{
    public function index() {
        $user = Auth::user(); $stages = config('crm.pipeline_stages');
        $leads = Lead::with('assignedTo')->when(!$user->isAdmin(), fn($q) => $q->where('assigned_to', $user->id))->whereNotIn('status', ['lost', 'duplicate', 'not_interested'])->orderByDesc('deal_value')->get()->groupBy('pipeline_stage');
        $stageStats = [];
        foreach ($stages as $key => $label) { $sl = $leads->get($key, collect()); $stageStats[$key] = ['label' => $label, 'count' => $sl->count(), 'value' => $sl->sum('deal_value')]; }
        return view('pipeline.index', compact('leads', 'stages', 'stageStats'));
    }
    public function updateStage(Request $request) {
        $request->validate(['lead_id' => 'required|exists:leads,id', 'stage' => 'required|string']);
        $lead = Lead::findOrFail($request->lead_id); $old = $lead->pipeline_stage;
        $lead->update(['pipeline_stage' => $request->stage]);
        $map = ['new_lead' => 'new', 'qualified' => 'interested', 'contacted' => 'contacted', 'proposal' => 'proposal_sent', 'negotiation' => 'negotiation', 'won' => 'won', 'lost' => 'lost'];
        if (isset($map[$request->stage])) $lead->update(['status' => $map[$request->stage]]);
        LeadActivity::create(['lead_id' => $lead->id, 'user_id' => Auth::id(), 'type' => 'pipeline_change', 'title' => 'Pipeline Stage Changed', 'description' => "From {$old} to {$request->stage}"]);
        return response()->json(['success' => true]);
    }
    public function forecast() {
        $user = Auth::user();
        $forecast = Lead::when(!$user->isAdmin(), fn($q) => $q->where('assigned_to', $user->id))->whereIn('pipeline_stage', ['qualified', 'contacted', 'proposal', 'negotiation'])->select('pipeline_stage', DB::raw('COUNT(*) as deal_count'), DB::raw('SUM(deal_value) as total_value'), DB::raw('SUM(deal_value * win_probability / 100) as weighted_value'))->groupBy('pipeline_stage')->get();
        return view('pipeline.forecast', compact('forecast'));
    }
}
