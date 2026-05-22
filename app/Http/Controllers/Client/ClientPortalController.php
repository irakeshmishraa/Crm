<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientPortalController extends Controller
{
    public function dashboard() { $client = Auth::guard('client')->user(); return view('client-portal.dashboard', ['client' => $client, 'quotations' => Quotation::where('client_id', $client->id)->latest()->limit(5)->get()]); }
    public function quotations() { $client = Auth::guard('client')->user(); return view('client-portal.quotations', ['quotations' => Quotation::where('client_id', $client->id)->latest()->paginate(10)]); }
    public function viewQuotation(Quotation $quotation) { return view('client-portal.quotation-view', ['quotation' => $quotation->load('items')]); }
    public function approveQuotation(Request $request, Quotation $quotation) { $request->validate(['accepted_by_name' => 'required|string']); $quotation->update(['status' => 'accepted', 'accepted_at' => now(), 'accepted_by_name' => $request->accepted_by_name]); return back()->with('success', 'Approved.'); }
    public function rejectQuotation(Request $request, Quotation $quotation) { $request->validate(['rejection_reason' => 'required|string']); $quotation->update(['status' => 'rejected', 'rejected_at' => now(), 'rejection_reason' => $request->rejection_reason]); return back()->with('success', 'Rejected.'); }
    public function documents() { $client = Auth::guard('client')->user(); return view('client-portal.documents', ['documents' => $client->documents()->latest()->get()]); }
    public function submitSupport(Request $request) { $request->validate(['subject' => 'required|string', 'message' => 'required|string']); return back()->with('success', 'Support request submitted.'); }
}
