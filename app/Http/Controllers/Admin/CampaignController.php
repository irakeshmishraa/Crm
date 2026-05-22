<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignSequence;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    public function index() { return view('campaigns.index', ['campaigns' => Campaign::with('creator')->where('created_by', Auth::id())->latest()->paginate(25)]); }
    public function create() { return view('campaigns.create'); }
    public function store(Request $request) { $v = $request->validate(['name' => 'required|string', 'description' => 'nullable|string', 'type' => 'required|in:email,whatsapp,sms', 'stop_on_reply' => 'boolean', 'sequences' => 'required|array|min:1']); $c = Campaign::create(['created_by' => Auth::id(), 'name' => $v['name'], 'description' => $v['description'] ?? null, 'type' => $v['type'], 'stop_on_reply' => $request->boolean('stop_on_reply')]); foreach ($v['sequences'] as $i => $seq) CampaignSequence::create(['campaign_id' => $c->id, 'step_number' => $i + 1, 'delay_days' => $seq['delay_days'], 'subject' => $seq['subject'], 'body' => $seq['body'], 'type' => $v['type']]); return redirect()->route('admin.campaigns.show', $c)->with('success', 'Campaign created.'); }
    public function show(Campaign $campaign) { $campaign->load(['sequences', 'recipients.lead']); return view('campaigns.show', compact('campaign')); }
    public function edit(Campaign $campaign) { $campaign->load('sequences'); return view('campaigns.edit', compact('campaign')); }
    public function update(Request $request, Campaign $campaign) { $campaign->update($request->validate(['name' => 'required|string', 'description' => 'nullable|string', 'stop_on_reply' => 'boolean'])); return redirect()->route('admin.campaigns.show', $campaign)->with('success', 'Updated.'); }
    public function destroy(Campaign $campaign) { $campaign->delete(); return redirect()->route('admin.campaigns.index')->with('success', 'Deleted.'); }
    public function start(Campaign $campaign) { $leads = Lead::whereNotNull('email')->where('status', '!=', 'won')->limit(500)->get(); foreach ($leads as $lead) $campaign->recipients()->firstOrCreate(['lead_id' => $lead->id], ['next_send_at' => now()]); $campaign->update(['status' => 'active', 'started_at' => now(), 'total_recipients' => $campaign->recipients()->count()]); return back()->with('success', 'Campaign started.'); }
    public function pause(Campaign $campaign) { $campaign->update(['status' => 'paused']); return back()->with('success', 'Paused.'); }
    public function resume(Campaign $campaign) { $campaign->update(['status' => 'active']); return back()->with('success', 'Resumed.'); }
    public function stats(Campaign $campaign) { return response()->json(['total_recipients' => $campaign->total_recipients, 'sent' => $campaign->sent_count, 'opens' => $campaign->open_count, 'clicks' => $campaign->click_count, 'open_rate' => $campaign->open_rate, 'click_rate' => $campaign->click_rate]); }
}
