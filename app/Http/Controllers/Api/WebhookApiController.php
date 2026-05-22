<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessage;
use App\Models\Lead;
use Illuminate\Http\Request;

class WebhookApiController extends Controller
{
    public function whatsapp(Request $request) {
        $data = $request->all();
        if (isset($data['entry'][0]['changes'][0]['value']['messages'])) {
            foreach ($data['entry'][0]['changes'][0]['value']['messages'] as $msg) {
                $from = $msg['from']; $content = $msg['text']['body'] ?? '';
                $lead = Lead::where('whatsapp_number', $from)->orWhere('phone', $from)->first();
                WhatsAppMessage::create(['lead_id' => $lead?->id, 'from_number' => $from, 'to_number' => config('services.whatsapp.phone_number_id'), 'direction' => 'inbound', 'type' => $msg['type'] ?? 'text', 'content' => $content, 'whatsapp_message_id' => $msg['id'] ?? null, 'status' => 'delivered']);
            }
        }
        if (isset($data['entry'][0]['changes'][0]['value']['statuses'])) {
            foreach ($data['entry'][0]['changes'][0]['value']['statuses'] as $s) WhatsAppMessage::where('whatsapp_message_id', $s['id'])->update(['status' => $s['status']]);
        }
        return response()->json(['status' => 'ok']);
    }
    public function verifyWhatsapp(Request $request) { return $request->get('hub_verify_token') === config('services.whatsapp.verify_token') ? response($request->get('hub_challenge'), 200) : response('Invalid', 403); }
    public function incoming(Request $request, string $token) { $webhook = \App\Models\Webhook::where('secret', $token)->where('is_active', true)->first(); if (!$webhook) return response()->json(['error' => 'Invalid'], 404); \App\Models\WebhookLog::create(['webhook_id' => $webhook->id, 'event' => 'incoming', 'payload' => $request->all(), 'status' => 'success']); return response()->json(['received' => true]); }
}
