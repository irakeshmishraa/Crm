<?php

namespace App\Services;

use App\Models\GoogleSheetConnection;
use App\Models\Lead;
use App\Models\User;
use Google\Client as GoogleClient;
use Google\Service\Sheets;

class GoogleSheetsService
{
    private Sheets $sheets;

    public function __construct(User $user)
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setAccessToken($user->google_token);
        if ($client->isAccessTokenExpired() && $user->google_refresh_token) {
            $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
            $user->update(['google_token' => $client->getAccessToken()['access_token']]);
        }
        $this->sheets = new Sheets($client);
    }

    public function syncLeads(GoogleSheetConnection $conn): array
    {
        $result = ['processed' => 0, 'created' => 0, 'updated' => 0];
        $range = ($conn->worksheet_name ?? 'Sheet1') . '!A:Z';
        $data = $this->sheets->spreadsheets_values->get($conn->spreadsheet_id, $range)->getValues() ?? [];
        if (empty($data)) return $result;
        $headers = array_shift($data);
        $mapping = $conn->column_mapping ?? ['Name' => 'name', 'Email' => 'email', 'Phone' => 'phone', 'Company' => 'company_name'];
        foreach ($data as $row) {
            $result['processed']++;
            $leadData = [];
            foreach ($mapping as $col => $field) { $idx = array_search($col, $headers); if ($idx !== false && isset($row[$idx])) $leadData[$field] = $row[$idx]; }
            if (empty($leadData['name'])) continue;
            $existing = !empty($leadData['email']) ? Lead::where('email', $leadData['email'])->first() : null;
            if ($existing) { $existing->update($leadData); $result['updated']++; }
            else { $leadData['source'] = 'google_sheets'; Lead::create($leadData); $result['created']++; }
        }
        return $result;
    }
}
