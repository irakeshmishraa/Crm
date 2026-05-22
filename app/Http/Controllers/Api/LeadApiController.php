<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadApiController extends Controller
{
    public function index(Request $request) { $q = Lead::with('assignedTo'); if ($request->has('status')) $q->where('status', $request->status); if ($request->has('search')) $q->where(fn($qq) => $qq->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%")); return response()->json($q->latest()->paginate(25)); }
    public function store(Request $request) { $v = $request->validate(['name' => 'required|string', 'email' => 'nullable|email', 'phone' => 'nullable|string', 'company_name' => 'nullable|string', 'source' => 'nullable|string', 'status' => 'nullable|string', 'assigned_to' => 'nullable|exists:users,id']); $v['created_by'] = $request->user()->id; $v['assigned_to'] = $v['assigned_to'] ?? $request->user()->id; return response()->json(Lead::create($v), 201); }
    public function show(Lead $lead) { return response()->json($lead->load(['assignedTo', 'followUps', 'quotations'])); }
    public function update(Request $request, Lead $lead) { $lead->update($request->all()); return response()->json($lead); }
    public function destroy(Lead $lead) { $lead->delete(); return response()->json(['message' => 'Deleted']); }
    public function assign(Request $request, Lead $lead) { $request->validate(['assigned_to' => 'required|exists:users,id']); $lead->update(['assigned_to' => $request->assigned_to]); return response()->json(['message' => 'Assigned']); }
    public function convert(Lead $lead) { $client = \App\Models\Client::create(['company_name' => $lead->company_name ?? $lead->name, 'contact_person' => $lead->name, 'email' => $lead->email, 'phone' => $lead->phone, 'assigned_to' => $lead->assigned_to, 'converted_from_lead' => $lead->id]); $lead->update(['status' => 'won', 'converted_at' => now(), 'converted_client_id' => $client->id]); return response()->json(['message' => 'Converted', 'client' => $client]); }
    public function capture(Request $request) { $lead = Lead::create(['name' => $request->get('name', 'API Lead'), 'email' => $request->get('email'), 'phone' => $request->get('phone'), 'source' => 'api']); return response()->json(['success' => true, 'lead_id' => $lead->lead_id]); }
    public function import(Request $request) { return response()->json(['message' => 'Use CSV upload']); }
    public function export(Request $request) { return response()->json(Lead::all(['lead_id', 'name', 'email', 'phone', 'status', 'source', 'created_at'])); }
}
