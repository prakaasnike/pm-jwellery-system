<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramNotifier
{
    public function enabled(): bool
    {
        return (bool) config('services.telegram.enabled')
            && filled($this->botToken())
            && filled($this->adminChatId());
    }

    public function sendToAdmin(string $message): bool
    {
        return $this->sendToChat((string) $this->adminChatId(), $message);
    }

    public function sendToChat(string $chatId, string $message): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        try {
            $response = Http::asJson()
                ->timeout(10)
                ->withOptions([
                    'verify' => (bool) config('services.telegram.verify_ssl'),
                ])
                ->post($this->endpoint('sendMessage'), [
                    'chat_id' => trim($chatId),
                    'text' => $message,
                    'disable_web_page_preview' => true,
                ]);
        } catch (Throwable $exception) {
            Log::warning('Telegram notification failed.', [
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        if ($response->failed()) {
            Log::warning('Telegram notification failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
                'hint' => $response->status() === 404
                    ? 'Check TELEGRAM_BOT_TOKEN on live. It should be the BotFather token only; both "123:ABC" and "bot123:ABC" are accepted.'
                    : null,
            ]);

            return false;
        }

        return true;
    }

    private function endpoint(string $method): string
    {
        return sprintf(
            'https://api.telegram.org/bot%s/%s',
            $this->botToken(),
            $method,
        );
    }

    private function botToken(): ?string
    {
        $token = trim((string) config('services.telegram.bot_token'));

        if ($token === '') {
            return null;
        }

        return str_starts_with(strtolower($token), 'bot')
            ? substr($token, 3)
            : $token;
    }

    private function adminChatId(): ?string
    {
        $chatId = trim((string) config('services.telegram.admin_chat_id'));

        return $chatId === '' ? null : $chatId;
    }
}
