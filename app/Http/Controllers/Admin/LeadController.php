<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Lead::with(['assignedTo', 'createdBy']);
        if (!$user->isAdmin()) $query->where('assigned_to', $user->id);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('source')) $query->where('source', $request->source);
        if ($request->filled('priority')) $query->where('priority', $request->priority);
        if ($request->filled('assigned_to')) $query->where('assigned_to', $request->assigned_to);
        if ($request->filled('search')) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%")->orWhere('phone', 'like', "%{$request->search}%")->orWhere('company_name', 'like', "%{$request->search}%"));
        }
        $leads = $query->orderBy($request->get('sort', 'created_at'), $request->get('direction', 'desc'))->paginate(25);
        $users = User::where('status', 'active')->get();
        return view('leads.index', compact('leads', 'users'));
    }

    public function create() { return view('leads.create', ['users' => User::where('status', 'active')->get()]); }


    public function store(Request $request)
    {
        $v = $request->validate(['name' => 'required|string|max:255', 'email' => 'nullable|email', 'phone' => 'nullable|string|max:20', 'whatsapp_number' => 'nullable|string|max:20', 'company_name' => 'nullable|string|max:255', 'designation' => 'nullable|string', 'website' => 'nullable|url', 'industry' => 'nullable|string', 'address' => 'nullable|string', 'city' => 'nullable|string', 'state' => 'nullable|string', 'country' => 'nullable|string', 'pincode' => 'nullable|string', 'budget' => 'nullable|numeric', 'requirement' => 'nullable|string', 'notes' => 'nullable|string', 'tags' => 'nullable|array', 'source' => 'nullable|string', 'status' => 'nullable|string', 'priority' => 'nullable|string', 'assigned_to' => 'nullable|exists:users,id', 'deal_value' => 'nullable|numeric', 'expected_close_date' => 'nullable|date']);
        $v['created_by'] = Auth::id();
        $v['assigned_to'] = $v['assigned_to'] ?? Auth::id();
        $lead = Lead::create($v);
        LeadActivity::create(['lead_id' => $lead->id, 'user_id' => Auth::id(), 'type' => 'created', 'title' => 'Lead Created', 'description' => "Lead {$lead->name} was created."]);
        return redirect()->route('admin.leads.show', $lead)->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead)
    {
        $lead->load(['assignedTo', 'createdBy', 'followUps' => fn($q) => $q->orderByDesc('scheduled_at')->limit(10), 'activities' => fn($q) => $q->with('user')->limit(20), 'quotations', 'notes.user', 'documents']);
        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead) { return view('leads.edit', ['lead' => $lead, 'users' => User::where('status', 'active')->get()]); }

    public function update(Request $request, Lead $lead)
    {
        $v = $request->validate(['name' => 'required|string|max:255', 'email' => 'nullable|email', 'phone' => 'nullable|string|max:20', 'company_name' => 'nullable|string', 'status' => 'nullable|string', 'priority' => 'nullable|string', 'assigned_to' => 'nullable|exists:users,id', 'deal_value' => 'nullable|numeric', 'win_probability' => 'nullable|integer|min:0|max:100', 'expected_close_date' => 'nullable|date', 'source' => 'nullable|string', 'budget' => 'nullable|numeric', 'requirement' => 'nullable|string', 'notes' => 'nullable|string']);
        $old = $lead->status;
        $lead->update($v);
        if ($old !== $lead->status) LeadActivity::create(['lead_id' => $lead->id, 'user_id' => Auth::id(), 'type' => 'status_change', 'title' => 'Status Changed', 'description' => "From {$old} to {$lead->status}"]);
        return redirect()->route('admin.leads.show', $lead)->with('success', 'Lead updated.');
    }

    public function destroy(Lead $lead) { $lead->delete(); return redirect()->route('admin.leads.index')->with('success', 'Lead deleted.'); }


    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,xlsx,xls|max:10240']);
        $data = array_map('str_getcsv', file($request->file('file')->getPathname()));
        $headers = array_shift($data);
        $imported = 0;
        foreach ($data as $row) {
            $r = array_combine($headers, $row);
            Lead::create(['name' => $r['name'] ?? $r['Name'] ?? 'Unknown', 'email' => $r['email'] ?? $r['Email'] ?? null, 'phone' => $r['phone'] ?? $r['Phone'] ?? null, 'company_name' => $r['company'] ?? null, 'source' => 'import', 'assigned_to' => Auth::id(), 'created_by' => Auth::id()]);
            $imported++;
        }
        return back()->with('success', "{$imported} leads imported.");
    }

    public function export(string $format)
    {
        $leads = Lead::with('assignedTo')->get();
        $filename = 'leads_' . date('Y-m-d') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename={$filename}"];
        $cb = function () use ($leads) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['ID', 'Name', 'Email', 'Phone', 'Company', 'Status', 'Source', 'Priority', 'Assigned To', 'Created']);
            foreach ($leads as $l) fputcsv($f, [$l->lead_id, $l->name, $l->email, $l->phone, $l->company_name, $l->status, $l->source, $l->priority, $l->assignedTo?->name, $l->created_at->format('Y-m-d')]);
            fclose($f);
        };
        return response()->stream($cb, 200, $headers);
    }

    public function bulkAssign(Request $request) { $request->validate(['lead_ids' => 'required|array', 'assigned_to' => 'required|exists:users,id']); Lead::whereIn('id', $request->lead_ids)->update(['assigned_to' => $request->assigned_to]); return back()->with('success', count($request->lead_ids) . ' leads assigned.'); }
    public function bulkDelete(Request $request) { $request->validate(['lead_ids' => 'required|array']); Lead::whereIn('id', $request->lead_ids)->delete(); return back()->with('success', 'Leads deleted.'); }

    public function convertToClient(Lead $lead)
    {
        $client = \App\Models\Client::create(['company_name' => $lead->company_name ?? $lead->name, 'contact_person' => $lead->name, 'email' => $lead->email, 'phone' => $lead->phone, 'whatsapp' => $lead->whatsapp_number, 'website' => $lead->website, 'industry' => $lead->industry, 'billing_address' => $lead->address, 'billing_city' => $lead->city, 'billing_state' => $lead->state, 'billing_country' => $lead->country, 'assigned_to' => $lead->assigned_to, 'converted_from_lead' => $lead->id]);
        $lead->update(['status' => 'won', 'converted_at' => now(), 'converted_client_id' => $client->id]);
        LeadActivity::create(['lead_id' => $lead->id, 'user_id' => Auth::id(), 'type' => 'converted', 'title' => 'Converted to Client']);
        return redirect()->route('admin.clients.show', $client)->with('success', 'Lead converted to client.');
    }

    public function updateScore(Request $request, Lead $lead) { $request->validate(['score' => 'required|integer|min:0|max:100']); $lead->update(['score' => $request->score]); return response()->json(['success' => true]); }
    public function timeline(Lead $lead) { return response()->json($lead->activities()->with('user')->orderByDesc('created_at')->paginate(20)); }
    public function duplicateCheck(Request $request) { $q = Lead::query(); if ($request->email) $q->orWhere('email', $request->email); if ($request->phone) $q->orWhere('phone', $request->phone); return response()->json($q->limit(5)->get(['id', 'lead_id', 'name', 'email', 'phone', 'status'])); }
    public function merge(Request $request) { $request->validate(['primary_id' => 'required|exists:leads,id', 'merge_ids' => 'required|array']); $primary = Lead::findOrFail($request->primary_id); foreach (Lead::whereIn('id', $request->merge_ids)->get() as $m) { $m->followUps()->update(['lead_id' => $primary->id]); $m->activities()->update(['lead_id' => $primary->id]); $m->update(['status' => 'duplicate']); $m->delete(); } return back()->with('success', 'Leads merged.'); }
    public function captureContact(Request $request) { $request->validate(['name' => 'required|string', 'email' => 'nullable|email', 'phone' => 'nullable|string']); Lead::create(['name' => $request->name, 'email' => $request->email, 'phone' => $request->phone, 'requirement' => $request->message, 'source' => 'website', 'campaign_source' => 'contact_form']); return response()->json(['success' => true, 'message' => 'Thank you!']); }
    public function captureCallback(Request $request) { $request->validate(['name' => 'required', 'phone' => 'required']); Lead::create(['name' => $request->name, 'phone' => $request->phone, 'source' => 'website', 'campaign_source' => 'callback_form']); return response()->json(['success' => true]); }
    public function captureDemo(Request $request) { $request->validate(['name' => 'required', 'email' => 'required|email']); Lead::create(['name' => $request->name, 'email' => $request->email, 'company_name' => $request->company_name, 'source' => 'website', 'campaign_source' => 'demo_request']); return response()->json(['success' => true]); }
    public function captureWebhook(Request $request) { Lead::create(['name' => $request->get('name', 'Webhook Lead'), 'email' => $request->get('email'), 'phone' => $request->get('phone'), 'source' => $request->get('source', 'webhook')]); return response()->json(['success' => true]); }
}
