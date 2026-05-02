<?php

namespace App\Http\Controllers;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Services\TelegramNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, TelegramNotifier $telegram, string $secret): JsonResponse
    {
        if (! hash_equals((string) config('services.telegram.webhook_secret'), $secret)) {
            abort(404);
        }

        $message = $request->input('message');
        $chatId = data_get($message, 'chat.id');
        $text = trim((string) data_get($message, 'text'));

        if (! $chatId || $text === '') {
            return response()->json(['ok' => true]);
        }

        if ((string) $chatId !== (string) config('services.telegram.admin_chat_id')) {
            $telegram->sendToChat((string) $chatId, 'Sorry, this bot is only for the PMJ admin.');

            return response()->json(['ok' => true]);
        }

        if (str_starts_with($text, '/start') || str_starts_with($text, '/today')) {
            $telegram->sendToChat((string) $chatId, $this->todayOrdersMessage());

            return response()->json(['ok' => true]);
        }

        $telegram->sendToChat((string) $chatId, 'Send /today to see today\'s active delivery orders.');

        return response()->json(['ok' => true]);
    }

    private function todayOrdersMessage(): string
    {
        $orders = Order::query()
            ->with('customer')
            ->whereDate('delivery_date', today())
            ->whereIn('status', Order::NOTIFIABLE_STATUSES)
            ->orderBy('delivery_date')
            ->get();

        $lines = [
            'PMJ orders due today',
            'Date: '.today()->format('d M Y'),
            '',
        ];

        if ($orders->isEmpty()) {
            $lines[] = '- No active orders due today.';

            return implode(PHP_EOL, $lines);
        }

        foreach ($orders as $order) {
            $lines[] = sprintf(
                '- %s | %s | %s',
                $order->order_name,
                $order->customer?->full_name ?? 'No customer',
                ucfirst($order->status),
            );
            $lines[] = OrderResource::getUrl('edit', ['record' => $order], true);
        }

        return implode(PHP_EOL, $lines);
    }
}
