<?php

namespace App\Observers;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class OrderObserver
{
    public function created(Order $order): void
    {
        $admins = User::role('super_admin')->get();
        $customerUser = $order->customer?->user;

        Notification::make()
            ->success()
            ->title('New order received')
            ->body("Order **{$order->order_name}** has been created.")
            ->icon('heroicon-o-shopping-bag')
            ->sendToDatabase($admins);

        if ($customerUser) {
            Notification::make()
                ->success()
                ->title('New order received')
                ->body("Your order **{$order->order_name}** has been created.")
                ->icon('heroicon-o-shopping-bag')
                ->actions([
                    Action::make('view')
                        ->label('View Order')
                        ->url(OrderResource::getUrl('index'))
                        ->markAsRead(),
                ])
                ->sendToDatabase($customerUser);
        }
    }

    public function updated(Order $order): void
    {
        $watched = ['status', 'payment_status', 'delivery_date'];

        if (! $order->wasChanged($watched)) {
            return;
        }

        $admins = User::role('super_admin')->get();
        $customerUser = $order->customer?->user;

        $changes = [];
        if ($order->wasChanged('status')) {
            $changes[] = 'Status → '.ucfirst($order->status);
        }
        if ($order->wasChanged('payment_status')) {
            $changes[] = 'Payment → '.ucfirst($order->payment_status);
        }
        if ($order->wasChanged('delivery_date')) {
            $date = \Carbon\Carbon::parse($order->delivery_date)->format('d M Y');
            $changes[] = "Delivery date → {$date}";
        }

        Notification::make()
            ->info()
            ->title("Order updated: {$order->order_name}")
            ->body(implode(' | ', $changes))
            ->icon('heroicon-o-arrow-path')
            ->sendToDatabase($admins);

        if ($customerUser) {
            Notification::make()
                ->info()
                ->title("Order updated: {$order->order_name}")
                ->body(implode(' | ', $changes))
                ->icon('heroicon-o-arrow-path')
                ->actions([
                    Action::make('view')
                        ->label('View Order')
                        ->url(OrderResource::getUrl('index'))
                        ->markAsRead(),
                ])
                ->sendToDatabase($customerUser);
        }
    }
}
