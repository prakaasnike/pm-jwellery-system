<?php

namespace App\Console\Commands;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class NotifyOrderDeliveries extends Command
{
    protected $signature   = 'orders:notify-deliveries';
    protected $description = 'Send delivery date notifications for orders due today and tomorrow';

    public function handle(): void
    {
        $admins = User::role('super_admin')->get();

        if ($admins->isEmpty()) {
            return;
        }

        $today    = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        $dueToday = Order::whereDate('delivery_date', $today)
            ->whereNotIn('status', ['delivered'])
            ->get();

        $dueTomorrow = Order::whereDate('delivery_date', $tomorrow)
            ->whereNotIn('status', ['delivered'])
            ->get();

        foreach ($dueToday as $order) {
            Notification::make()
                ->warning()
                ->title('Delivery due today')
                ->body("Order **{$order->order_name}** is due for delivery today.")
                ->icon('heroicon-o-clock')
                ->actions([
                    Action::make('view')
                        ->label('View Order')
                        ->url(OrderResource::getUrl('edit', ['record' => $order]))
                        ->markAsRead(),
                ])
                ->sendToDatabase($admins);
        }

        foreach ($dueTomorrow as $order) {
            Notification::make()
                ->info()
                ->title('Delivery due tomorrow')
                ->body("Order **{$order->order_name}** is due for delivery tomorrow.")
                ->icon('heroicon-o-calendar-days')
                ->actions([
                    Action::make('view')
                        ->label('View Order')
                        ->url(OrderResource::getUrl('edit', ['record' => $order]))
                        ->markAsRead(),
                ])
                ->sendToDatabase($admins);
        }

        $this->info("Notified: {$dueToday->count()} due today, {$dueTomorrow->count()} due tomorrow.");
    }
}
