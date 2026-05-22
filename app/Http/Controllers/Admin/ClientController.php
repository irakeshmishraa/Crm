<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Client::with('assignedTo');
        if (!$user->isAdmin()) $query->where('assigned_to', $user->id);
        if ($request->filled('search')) $query->where(fn($q) => $q->where('company_name', 'like', "%{$request->search}%")->orWhere('contact_person', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%"));
        if ($request->filled('status')) $query->where('status', $request->status);
        return view('clients.index', ['clients' => $query->latest()->paginate(25)]);
    }
    public function create() { return view('clients.create'); }
    public function store(Request $request) {
        $v = $request->validate(['company_name' => 'required|string|max:255', 'contact_person' => 'required|string|max:255', 'email' => 'required|email|unique:clients', 'phone' => 'nullable|string', 'whatsapp' => 'nullable|string', 'website' => 'nullable|string', 'industry' => 'nullable|string', 'gst_number' => 'nullable|string', 'billing_address' => 'nullable|string', 'billing_city' => 'nullable|string', 'billing_state' => 'nullable|string', 'notes' => 'nullable|string']);
        $v['assigned_to'] = Auth::id();
        $client = Client::create($v);
        return redirect()->route('admin.clients.show', $client)->with('success', 'Client created.');
    }
    public function show(Client $client) { $client->load(['contacts', 'documents', 'payments', 'quotations', 'assignedTo']); return view('clients.show', compact('client')); }
    public function edit(Client $client) { return view('clients.edit', compact('client')); }
    public function update(Request $request, Client $client) { $client->update($request->validate(['company_name' => 'required|string', 'contact_person' => 'required|string', 'email' => 'required|email|unique:clients,email,' . $client->id, 'phone' => 'nullable|string', 'status' => 'nullable|string', 'notes' => 'nullable|string'])); return redirect()->route('admin.clients.show', $client)->with('success', 'Updated.'); }
    public function destroy(Client $client) { $client->delete(); return redirect()->route('admin.clients.index')->with('success', 'Deleted.'); }
    public function documents(Client $client) { return view('clients.documents', ['client' => $client, 'documents' => $client->documents()->with('uploader')->latest()->get()]); }
    public function uploadDocument(Request $request, Client $client) { $request->validate(['document' => 'required|file|max:10240', 'name' => 'required|string']); $file = $request->file('document'); ClientDocument::create(['client_id' => $client->id, 'uploaded_by' => Auth::id(), 'name' => $request->name, 'file_path' => $file->store('client-documents/' . $client->id, 'public'), 'file_type' => $file->getClientOriginalExtension(), 'file_size' => $file->getSize(), 'category' => $request->category]); return back()->with('success', 'Document uploaded.'); }
    public function payments(Client $client) { return view('clients.payments', ['client' => $client, 'payments' => $client->payments()->latest()->get()]); }
    public function communications(Client $client) { return view('clients.communications', ['client' => $client, 'emails' => $client->emails()->latest()->limit(20)->get(), 'whatsapp' => $client->whatsappMessages()->latest()->limit(20)->get()]); }
}
