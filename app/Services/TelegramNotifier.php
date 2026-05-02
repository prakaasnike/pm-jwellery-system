<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    public function enabled(): bool
    {
        return (bool) config('services.telegram.enabled')
            && filled(config('services.telegram.bot_token'))
            && filled(config('services.telegram.admin_chat_id'));
    }

    public function sendToAdmin(string $message): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        $response = Http::asJson()
            ->timeout(10)
            ->post($this->endpoint('sendMessage'), [
                'chat_id' => config('services.telegram.admin_chat_id'),
                'text' => $message,
                'disable_web_page_preview' => true,
            ]);

        if ($response->failed()) {
            Log::warning('Telegram notification failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    private function endpoint(string $method): string
    {
        return sprintf(
            'https://api.telegram.org/bot%s/%s',
            config('services.telegram.bot_token'),
            $method,
        );
    }
}
