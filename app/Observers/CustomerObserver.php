<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\User;
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
            ->icon('heroicon-o-user-group')
            ->sendToDatabase($admins);
    }
}
