<?php

namespace App\Console\Commands;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Services\TelegramNotifier;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;

class NotifyOrderDeliveries extends Command
{
    protected $signature = 'orders:notify-deliveries';

    protected $description = 'Send delivery date notifications for orders due today and tomorrow';

    public function handle(TelegramNotifier $telegram): void
    {
        $admins = User::role('super_admin')->get();

        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        $dueToday = Order::query()
            ->with('customer.user')
            ->whereDate('delivery_date', $today)
            ->whereIn('status', Order::NOTIFIABLE_STATUSES)
            ->get();

        $dueTomorrow = Order::query()
            ->with('customer.user')
            ->whereDate('delivery_date', $tomorrow)
            ->whereIn('status', Order::NOTIFIABLE_STATUSES)
            ->get();

        $adminNotifications = $this->sendReminders(
            orders: $dueToday,
            recipients: $admins,
            reminder: 'today',
            title: 'Delivery due today',
            bodyPrefix: 'Order',
            bodySuffix: 'is due for delivery today.',
            icon: 'heroicon-o-clock',
            status: 'warning',
        ) + $this->sendReminders(
            orders: $dueTomorrow,
            recipients: $admins,
            reminder: 'tomorrow',
            title: 'Delivery due tomorrow',
            bodyPrefix: 'Order',
            bodySuffix: 'is due for delivery tomorrow.',
            icon: 'heroicon-o-calendar-days',
            status: 'info',
        );

        $customerNotifications = $this->sendCustomerReminders($dueToday, 'today')
            + $this->sendCustomerReminders($dueTomorrow, 'tomorrow');

        $this->info("Orders: {$dueToday->count()} due today, {$dueTomorrow->count()} due tomorrow.");
        $this->info("Notifications sent: {$adminNotifications} admin, {$customerNotifications} customer.");

        if ($this->sendTelegramSummary($telegram, $dueToday, $dueTomorrow)) {
            $this->info('Telegram summary sent.');
        }
    }

    private function sendCustomerReminders(EloquentCollection $orders, string $reminder): int
    {
        $sent = 0;

        foreach ($orders as $order) {
            $customerUser = $order->customer?->user;

            if (! $customerUser) {
                continue;
            }

            $title = $reminder === 'today' ? 'Your delivery is due today' : 'Your delivery is due tomorrow';
            $bodySuffix = $reminder === 'today'
                ? 'is due for delivery today.'
                : 'is due for delivery tomorrow.';

            $sent += $this->sendReminders(
                orders: new EloquentCollection([$order]),
                recipients: new EloquentCollection([$customerUser]),
                reminder: "customer-{$reminder}",
                title: $title,
                bodyPrefix: 'Your order',
                bodySuffix: $bodySuffix,
                icon: $reminder === 'today' ? 'heroicon-o-clock' : 'heroicon-o-calendar-days',
                status: $reminder === 'today' ? 'warning' : 'info',
                url: OrderResource::getUrl('index'),
            );
        }

        return $sent;
    }

    private function sendReminders(
        EloquentCollection $orders,
        EloquentCollection $recipients,
        string $reminder,
        string $title,
        string $bodyPrefix,
        string $bodySuffix,
        string $icon,
        string $status,
        ?string $url = null,
    ): int {
        $sent = 0;

        foreach ($orders as $order) {
            foreach ($recipients as $recipient) {
                if ($this->reminderAlreadySent($recipient, $order, $reminder)) {
                    continue;
                }

                Notification::make()
                    ->{$status}()
                    ->title($title)
                    ->body("{$bodyPrefix} **{$order->order_name}** {$bodySuffix}")
                    ->icon($icon)
                    ->viewData([
                        'order_id' => $order->id,
                        'reminder' => $reminder,
                        'reminder_date' => today()->toDateString(),
                    ])
                    ->actions([
                        Action::make('view')
                            ->label('View Order')
                            ->url($url ?? OrderResource::getUrl('edit', ['record' => $order]))
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($recipient);

                $sent++;
            }
        }

        return $sent;
    }

    private function reminderAlreadySent(User $user, Order $order, string $reminder): bool
    {
        return $user->notifications()
            ->where('data->viewData->order_id', $order->id)
            ->where('data->viewData->reminder', $reminder)
            ->where('data->viewData->reminder_date', today()->toDateString())
            ->exists();
    }

    private function sendTelegramSummary(
        TelegramNotifier $telegram,
        EloquentCollection $dueToday,
        EloquentCollection $dueTomorrow,
    ): bool {
        if (! $telegram->enabled()) {
            return false;
        }

        if ($dueToday->isEmpty() && $dueTomorrow->isEmpty()) {
            return false;
        }

        $cacheKey = 'telegram_delivery_summary_sent_'.today()->toDateString();

        if (! Cache::add($cacheKey, true, now()->addDay())) {
            return false;
        }

        return $telegram->sendToAdmin($this->buildTelegramSummary($dueToday, $dueTomorrow));
    }

    private function buildTelegramSummary(EloquentCollection $dueToday, EloquentCollection $dueTomorrow): string
    {
        $lines = [
            'PMJ delivery reminders',
            'Date: '.today()->format('d M Y'),
            '',
            "Due today: {$dueToday->count()}",
            ...$this->formatTelegramOrders($dueToday),
            '',
            "Due tomorrow: {$dueTomorrow->count()}",
            ...$this->formatTelegramOrders($dueTomorrow),
        ];

        return implode(PHP_EOL, $lines);
    }

    private function formatTelegramOrders(EloquentCollection $orders): array
    {
        if ($orders->isEmpty()) {
            return ['- None'];
        }

        return $orders
            ->map(fn (Order $order): string => sprintf(
                '- %s | %s | %s%s%s',
                $order->order_name,
                $order->customer?->full_name ?? 'No customer',
                ucfirst($order->status),
                PHP_EOL,
                OrderResource::getUrl('edit', ['record' => $order], true),
            ))
            ->all();
    }
}
