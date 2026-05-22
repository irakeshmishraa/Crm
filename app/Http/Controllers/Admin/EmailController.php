<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailController extends Controller
{
    public function inbox() { return view('emails.inbox', ['emails' => Email::whereHas('emailAccount', fn($q) => $q->where('user_id', Auth::id()))->where('direction', 'inbound')->orderByDesc('created_at')->paginate(25)]); }
    public function sent() { return view('emails.sent', ['emails' => Email::whereHas('emailAccount', fn($q) => $q->where('user_id', Auth::id()))->where('direction', 'outbound')->orderByDesc('sent_at')->paginate(25)]); }
    public function compose() { return view('emails.compose', ['templates' => EmailTemplate::where(fn($q) => $q->where('user_id', Auth::id())->orWhere('is_shared', true))->get()]); }
    public function send(Request $request) { $request->validate(['to' => 'required|email', 'subject' => 'required|string', 'body' => 'required|string']); $account = EmailAccount::where('user_id', Auth::id())->where('is_primary', true)->first(); if (!$account) return back()->withErrors(['email' => 'No email account connected.']); Email::create(['email_account_id' => $account->id, 'lead_id' => $request->lead_id, 'from_email' => $account->email_address, 'from_name' => Auth::user()->name, 'to_emails' => [$request->to], 'subject' => $request->subject, 'body_html' => $request->body, 'direction' => 'outbound', 'status' => 'sent', 'sent_at' => now()]); return redirect()->route('admin.email.sent')->with('success', 'Email sent.'); }
    public function templates() { return view('emails.templates', ['templates' => EmailTemplate::where('user_id', Auth::id())->orWhere('is_shared', true)->get()]); }
    public function storeTemplate(Request $request) { $request->validate(['name' => 'required|string', 'subject' => 'required|string', 'body' => 'required|string']); EmailTemplate::create(['user_id' => Auth::id(), 'name' => $request->name, 'subject' => $request->subject, 'body' => $request->body, 'category' => $request->category, 'is_shared' => $request->boolean('is_shared')]); return back()->with('success', 'Template saved.'); }
    public function thread(string $threadId) { return view('emails.thread', ['emails' => Email::where('thread_id', $threadId)->orderBy('created_at')->get()]); }
    public function connectGmail() { return redirect()->route('google.redirect'); }
}
