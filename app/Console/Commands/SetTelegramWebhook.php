<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhook extends Command
{
    protected $signature = 'telegram:set-webhook';

    protected $description = 'Register the Telegram webhook URL for bot commands';

    public function handle(): int
    {
        $token = trim((string) config('services.telegram.bot_token'));
        $secret = trim((string) config('services.telegram.webhook_secret'));

        if ($token === '' || $secret === '') {
            $this->error('Set TELEGRAM_BOT_TOKEN and TELEGRAM_WEBHOOK_SECRET first.');

            return self::FAILURE;
        }

        $token = str_starts_with(strtolower($token), 'bot') ? substr($token, 3) : $token;
        $webhookUrl = route('telegram.webhook', ['secret' => $secret]);

        $response = Http::asJson()
            ->withOptions([
                'verify' => (bool) config('services.telegram.verify_ssl'),
            ])
            ->post("https://api.telegram.org/bot{$token}/setWebhook", [
                'url' => $webhookUrl,
            ]);

        if ($response->failed()) {
            $this->error('Telegram webhook setup failed: '.$response->body());

            return self::FAILURE;
        }

        $this->info('Telegram webhook set to: '.$webhookUrl);

        return self::SUCCESS;
    }
}
