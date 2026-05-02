<?php

namespace App\Observers;

use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        $admins = User::role('super_admin')->get();

        Notification::make()
            ->success()
            ->title('New customer added')
            ->body("**{$customer->full_name}** has been registered.")
            ->icon('heroicon-o-user-plus')
            ->actions([
                Action::make('view')
                    ->label('View Customer')
                    ->url(CustomerResource::getUrl('edit', ['record' => $customer]))
                    ->markAsRead(),
            ])
            ->sendToDatabase($admins);
    }

    public function updated(Customer $customer): void
    {
        if (! $customer->wasChanged(['full_name', 'email', 'phone', 'address'])) {
            return;
        }

        $admins = User::role('super_admin')->get();

        Notification::make()
            ->info()
            ->title('Customer updated')
            ->body("**{$customer->full_name}** details have been updated.")
            ->icon('heroicon-o-pencil-square')
            ->actions([
                Action::make('view')
                    ->label('View Customer')
                    ->url(CustomerResource::getUrl('edit', ['record' => $customer]))
                    ->markAsRead(),
            ])
            ->sendToDatabase($admins);
    }

    public function deleted(Customer $customer): void
    {
        $admins = User::role('super_admin')->get();

        Notification::make()
            ->danger()
            ->title('Customer deleted')
            ->body("**{$customer->full_name}** has been removed.")
            ->icon('heroicon-o-user-minus')
            ->sendToDatabase($admins);
    }
}
