<?php

namespace App\Observers;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Notification::make()
            ->success()
            ->title('Welcome to the system')
            ->body('Please update your settings')
            ->sendToDatabase($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {

        Notification::make()
            ->success()
            ->title('User updated')
            ->body('The user has been saved successfully.')
            ->sendToDatabase($user);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
