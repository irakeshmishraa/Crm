<?php

namespace App\Services;

use App\Models\EmailAccount;
use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

class GmailService
{
    private GoogleClient $client;
    private Gmail $gmail;
    private EmailAccount $account;

    public function __construct(EmailAccount $account)
    {
        $this->account = $account;
        $this->client = new GoogleClient();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setAccessToken($account->access_token);
        if ($this->client->isAccessTokenExpired()) $this->refreshToken();
        $this->gmail = new Gmail($this->client);
    }

    public function sendEmail(string $to, string $subject, string $body): string
    {
        $raw = "To: {$to}\r\nFrom: {$this->account->email_address}\r\nSubject: {$subject}\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$body}";
        $message = new Message();
        $message->setRaw(base64_encode($raw));
        return $this->gmail->users_messages->send('me', $message)->getId();
    }

    public function getInbox(int $max = 20): array
    {
        $messages = $this->gmail->users_messages->listUsersMessages('me', ['maxResults' => $max, 'labelIds' => ['INBOX']]);
        $emails = [];
        foreach ($messages->getMessages() as $m) { $email = $this->gmail->users_messages->get('me', $m->getId()); $emails[] = $this->parseMessage($email); }
        return $emails;
    }

    private function refreshToken(): void { $this->client->fetchAccessTokenWithRefreshToken($this->account->refresh_token); $t = $this->client->getAccessToken(); $this->account->update(['access_token' => $t['access_token'], 'token_expires_at' => now()->addSeconds($t['expires_in'])]); }
    private function parseMessage($m): array { $parsed = ['id' => $m->getId(), 'threadId' => $m->getThreadId(), 'snippet' => $m->getSnippet()]; foreach ($m->getPayload()->getHeaders() as $h) { if (in_array(strtolower($h->getName()), ['from', 'to', 'subject', 'date'])) $parsed[strtolower($h->getName())] = $h->getValue(); } return $parsed; }
}
