<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public function index() { return view('settings.webhooks', ['webhooks' => Webhook::where('user_id', Auth::id())->with('logs')->get()]); }
    public function store(Request $request) { $request->validate(['name' => 'required|string', 'url' => 'required|url', 'events' => 'required|array']); Webhook::create(['user_id' => Auth::id(), 'name' => $request->name, 'url' => $request->url, 'secret' => Str::random(32), 'events' => $request->events]); return back()->with('success', 'Webhook created.'); }
    public function update(Request $request, Webhook $webhook) { $webhook->update($request->validate(['name' => 'required|string', 'url' => 'required|url', 'events' => 'required|array', 'is_active' => 'boolean'])); return back()->with('success', 'Updated.'); }
    public function destroy(Webhook $webhook) { $webhook->delete(); return back()->with('success', 'Deleted.'); }
}
