<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use App\Models\Lead;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsAppController extends Controller
{
    public function index() { $conversations = WhatsAppMessage::select('to_number', 'from_number')->selectRaw('MAX(created_at) as last_message_at')->selectRaw('COUNT(*) as message_count')->groupBy('to_number', 'from_number')->orderByDesc('last_message_at')->paginate(25); return view('whatsapp.index', compact('conversations')); }
    public function chat(string $contact) { $messages = WhatsAppMessage::where('to_number', $contact)->orWhere('from_number', $contact)->orderBy('created_at')->get(); $lead = Lead::where('whatsapp_number', $contact)->orWhere('phone', $contact)->first(); return view('whatsapp.chat', compact('messages', 'contact', 'lead')); }
    public function send(Request $request) { $request->validate(['to_number' => 'required|string', 'message' => 'required|string']); try { $svc = new WhatsAppService(); $resp = $svc->sendTextMessage($request->to_number, $request->message); WhatsAppMessage::create(['lead_id' => $request->lead_id, 'sent_by' => Auth::id(), 'whatsapp_message_id' => $resp['messages'][0]['id'] ?? null, 'from_number' => config('services.whatsapp.phone_number_id'), 'to_number' => $request->to_number, 'direction' => 'outbound', 'type' => 'text', 'content' => $request->message, 'status' => 'sent']); return back()->with('success', 'Message sent.'); } catch (\Exception $e) { return back()->withErrors(['whatsapp' => 'Failed: ' . $e->getMessage()]); } }
    public function bulkSend(Request $request) { $request->validate(['numbers' => 'required|array', 'template_id' => 'required|exists:whatsapp_templates,id']); $template = WhatsAppTemplate::findOrFail($request->template_id); $svc = new WhatsAppService(); $sent = 0; foreach ($request->numbers as $num) { try { $svc->sendTemplateMessage($num, $template->name, $request->params ?? []); $sent++; } catch (\Exception $e) { continue; } } return back()->with('success', "{$sent} messages sent."); }
    public function templates() { return view('whatsapp.templates', ['templates' => WhatsAppTemplate::orderBy('name')->get()]); }
    public function storeTemplate(Request $request) { $request->validate(['name' => 'required|string', 'content' => 'required|string']); WhatsAppTemplate::create($request->only('name', 'content', 'category', 'variables')); return back()->with('success', 'Template created.'); }
    public function settings() { return view('whatsapp.settings'); }
    public function updateSettings(Request $request) { \App\Models\Setting::set('whatsapp_phone_number_id', $request->phone_number_id, 'string', 'whatsapp'); \App\Models\Setting::set('whatsapp_access_token', $request->access_token, 'string', 'whatsapp'); return back()->with('success', 'Settings updated.'); }
}
