<?php

namespace App\Console\Commands;

use App\Services\TelegramNotifier;
use Illuminate\Console\Command;

class SendTelegramTestMessage extends Command
{
    protected $signature = 'telegram:test {message=PMJ Telegram notifications are connected.}';

    protected $description = 'Send a test message to the configured Telegram admin chat';

    public function handle(TelegramNotifier $telegram): int
    {
        if (! $telegram->enabled()) {
            $this->error('Telegram is not configured. Set TELEGRAM_NOTIFICATIONS_ENABLED=true, TELEGRAM_BOT_TOKEN, and TELEGRAM_ADMIN_CHAT_ID.');

            return self::FAILURE;
        }

        if (! $telegram->sendToAdmin($this->argument('message'))) {
            $this->error('Telegram message failed. Check storage/logs/laravel.log for the Telegram API response.');

            return self::FAILURE;
        }

        $this->info('Telegram test message sent.');

        return self::SUCCESS;
    }
}
