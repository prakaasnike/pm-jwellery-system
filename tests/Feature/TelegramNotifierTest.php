<?php

namespace Tests\Feature;

use App\Services\TelegramNotifier;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramNotifierTest extends TestCase
{
    public function test_it_sends_to_telegram_with_a_normalized_bot_token(): void
    {
        config([
            'services.telegram.enabled' => true,
            'services.telegram.bot_token' => ' bot123456:ABC ',
            'services.telegram.admin_chat_id' => ' -100123456 ',
            'services.telegram.verify_ssl' => true,
        ]);

        Http::fake([
            'https://api.telegram.org/bot123456:ABC/sendMessage' => Http::response(['ok' => true]),
        ]);

        $sent = app(TelegramNotifier::class)->sendToAdmin('Test message');

        $this->assertTrue($sent);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.telegram.org/bot123456:ABC/sendMessage'
            && $request['chat_id'] === '-100123456'
            && $request['text'] === 'Test message'
        );
    }
}
