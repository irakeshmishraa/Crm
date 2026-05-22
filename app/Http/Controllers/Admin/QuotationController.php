<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationVersion;
use App\Models\Lead;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function index(Request $request) {
        $user = Auth::user();
        $query = Quotation::with(['lead', 'client', 'createdBy']);
        if (!$user->isAdmin()) $query->where('created_by', $user->id);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) $query->where(fn($q) => $q->where('quotation_number', 'like', "%{$request->search}%")->orWhere('client_name', 'like', "%{$request->search}%"));
        return view('quotations.index', ['quotations' => $query->latest()->paginate(25)]);
    }
    public function create(Request $request) { return view('quotations.create', ['leads' => Lead::whereNotIn('status', ['lost', 'duplicate'])->get(), 'clients' => Client::active()->get(), 'products' => Product::active()->get(), 'selectedLead' => $request->lead_id ? Lead::find($request->lead_id) : null, 'selectedClient' => $request->client_id ? Client::find($request->client_id) : null]); }
    public function store(Request $request) {
        $v = $request->validate(['lead_id' => 'nullable|exists:leads,id', 'client_id' => 'nullable|exists:clients,id', 'client_name' => 'required|string', 'client_email' => 'nullable|email', 'client_phone' => 'nullable|string', 'client_company' => 'nullable|string', 'client_gst' => 'nullable|string', 'billing_address' => 'nullable|string', 'shipping_address' => 'nullable|string', 'quotation_date' => 'required|date', 'valid_until' => 'required|date', 'subject' => 'nullable|string', 'introduction' => 'nullable|string', 'terms_conditions' => 'nullable|string', 'notes' => 'nullable|string', 'discount_amount' => 'nullable|numeric', 'discount_type' => 'nullable|string', 'shipping_charges' => 'nullable|numeric', 'template' => 'nullable|string', 'items' => 'required|array|min:1']);
        $v['created_by'] = Auth::id(); $v['assigned_to'] = Auth::id();
        $quotation = Quotation::create($v);
        $subtotal = 0; $totalTax = 0;
        foreach ($request->items as $i => $d) { $item = new QuotationItem($d); $item->quotation_id = $quotation->id; $item->sort_order = $i; $item->calculateLineTotal(); $item->save(); $subtotal += $item->quantity * $item->rate; $totalTax += $item->tax_amount; }
        $quotation->update(['subtotal' => $subtotal, 'tax_amount' => $totalTax, 'cgst_amount' => $totalTax / 2, 'sgst_amount' => $totalTax / 2, 'grand_total' => $subtotal - ($quotation->discount_amount ?? 0) + $totalTax + ($quotation->shipping_charges ?? 0)]);
        return redirect()->route('admin.quotations.show', $quotation)->with('success', 'Quotation created.');
    }
    public function show(Quotation $quotation) { $quotation->load(['items.product', 'lead', 'client', 'createdBy', 'versions']); return view('quotations.show', compact('quotation')); }
    public function edit(Quotation $quotation) { $quotation->load('items'); return view('quotations.edit', ['quotation' => $quotation, 'leads' => Lead::get(), 'clients' => Client::active()->get(), 'products' => Product::active()->get()]); }
    public function update(Request $request, Quotation $quotation) { QuotationVersion::create(['quotation_id' => $quotation->id, 'version_number' => $quotation->version, 'snapshot' => $quotation->toArray(), 'created_by' => Auth::id()]); $quotation->update($request->only(['client_name', 'client_email', 'valid_until', 'subject', 'notes', 'terms_conditions', 'template']) + ['version' => $quotation->version + 1]); return redirect()->route('admin.quotations.show', $quotation)->with('success', 'Updated.'); }
    public function destroy(Quotation $quotation) { $quotation->delete(); return redirect()->route('admin.quotations.index')->with('success', 'Deleted.'); }
    public function send(Quotation $quotation) { $quotation->update(['status' => 'sent', 'sent_at' => now()]); if ($quotation->lead) $quotation->lead->update(['status' => 'proposal_sent']); return back()->with('success', 'Quotation sent.'); }
    public function duplicate(Quotation $quotation) { $new = $quotation->replicate(); $new->quotation_number = null; $new->status = 'draft'; $new->sent_at = null; $new->approval_token = null; $new->version = 1; $new->save(); foreach ($quotation->items as $item) { $ni = $item->replicate(); $ni->quotation_id = $new->id; $ni->save(); } return redirect()->route('admin.quotations.edit', $new)->with('success', 'Duplicated.'); }
    public function downloadPdf(Quotation $quotation) { $quotation->load(['items.product', 'createdBy']); $pdf = Pdf::loadView('quotations.pdf.standard', compact('quotation')); return $pdf->download("Quotation-{$quotation->quotation_number}.pdf"); }
    public function convertToInvoice(Quotation $quotation) { $quotation->update(['status' => 'converted']); return back()->with('success', 'Converted to invoice.'); }
    public function versions(Quotation $quotation) { return response()->json($quotation->versions()->with('creator')->get()); }
    public function publicView(string $token) { $q = Quotation::where('approval_token', $token)->with('items')->firstOrFail(); if (!$q->viewed_at) $q->update(['viewed_at' => now(), 'status' => 'viewed']); return view('quotations.public-view', ['quotation' => $q]); }
    public function publicApprove(Request $request, string $token) { $q = Quotation::where('approval_token', $token)->firstOrFail(); $request->validate(['accepted_by_name' => 'required|string', 'action' => 'required|in:approve,reject']); if ($request->action === 'approve') $q->update(['status' => 'accepted', 'accepted_at' => now(), 'accepted_by_name' => $request->accepted_by_name]); else $q->update(['status' => 'rejected', 'rejected_at' => now(), 'rejection_reason' => $request->rejection_reason]); return back()->with('success', 'Response recorded.'); }
}
