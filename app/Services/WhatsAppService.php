<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    private string $apiUrl;
    private string $phoneNumberId;
    private string $accessToken;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->accessToken = config('services.whatsapp.access_token');
    }

    public function sendTextMessage(string $to, string $message): array
    {
        $response = Http::withToken($this->accessToken)->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
            'messaging_product' => 'whatsapp', 'recipient_type' => 'individual',
            'to' => $this->formatNumber($to), 'type' => 'text', 'text' => ['body' => $message],
        ]);
        if ($response->failed()) throw new \Exception('WhatsApp API error: ' . $response->body());
        return $response->json();
    }

    public function sendTemplateMessage(string $to, string $templateName, array $params = []): array
    {
        $components = [];
        if (!empty($params)) $components[] = ['type' => 'body', 'parameters' => array_map(fn($p) => ['type' => 'text', 'text' => $p], $params)];
        $response = Http::withToken($this->accessToken)->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
            'messaging_product' => 'whatsapp', 'to' => $this->formatNumber($to), 'type' => 'template',
            'template' => ['name' => $templateName, 'language' => ['code' => 'en'], 'components' => $components],
        ]);
        if ($response->failed()) throw new \Exception('WhatsApp API error: ' . $response->body());
        return $response->json();
    }

    public function sendDocumentMessage(string $to, string $url, string $filename): array
    {
        return Http::withToken($this->accessToken)->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
            'messaging_product' => 'whatsapp', 'to' => $this->formatNumber($to), 'type' => 'document',
            'document' => ['link' => $url, 'filename' => $filename],
        ])->json();
    }

    private function formatNumber(string $number): string { $number = preg_replace('/[^0-9]/', '', $number); return strlen($number) === 10 ? '91' . $number : $number; }
}
