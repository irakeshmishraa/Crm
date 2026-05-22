<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index() { return view('settings.index', ['settings' => Setting::all()->groupBy('group')]); }
    public function updateGeneral(Request $request) { foreach ($request->except('_token') as $key => $value) Setting::set($key, $value, 'string', 'general'); return back()->with('success', 'Settings updated.'); }
    public function updateEmail(Request $request) { foreach ($request->except('_token') as $key => $value) Setting::set($key, $value, 'string', 'email'); return back()->with('success', 'Email settings updated.'); }
    public function updateGoogle(Request $request) { foreach ($request->except('_token') as $key => $value) Setting::set($key, $value, 'string', 'google'); return back()->with('success', 'Google settings updated.'); }
    public function updateWhatsApp(Request $request) { foreach ($request->except('_token') as $key => $value) Setting::set($key, $value, 'string', 'whatsapp'); return back()->with('success', 'WhatsApp settings updated.'); }
    public function updateBranding(Request $request) { if ($request->hasFile('logo')) Setting::set('company_logo', $request->file('logo')->store('branding', 'public'), 'string', 'branding'); if ($request->hasFile('favicon')) Setting::set('company_favicon', $request->file('favicon')->store('branding', 'public'), 'string', 'branding'); return back()->with('success', 'Branding updated.'); }
    public function whiteLabel() { return view('settings.white-label'); }
    public function updateWhiteLabel(Request $request) { Setting::set('white_label_enabled', $request->boolean('white_label_enabled'), 'boolean', 'white_label'); Setting::set('white_label_name', $request->white_label_name, 'string', 'white_label'); if ($request->hasFile('white_label_logo')) Setting::set('white_label_logo', $request->file('white_label_logo')->store('branding', 'public'), 'string', 'white_label'); return back()->with('success', 'White label updated.'); }
}
