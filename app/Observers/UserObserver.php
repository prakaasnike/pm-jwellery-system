<?php

namespace App\Observers;

use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->assignPanelUserRole($user);

        $customer = Customer::query()->where('email', $user->email)->first();

        if ($customer) {
            Customer::withoutEvents(fn () => $customer->update(['user_id' => $user->id]));
        } else {
            $customer = Customer::withoutEvents(fn () => Customer::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'email' => $user->email,
            ]));
        }

        Notification::make()
            ->success()
            ->title('Welcome')
            ->body('Your customer account has been created. You can now view your orders from your dashboard.')
            ->sendToDatabase($user);

        $admins = User::role('super_admin')->whereKeyNot($user->id)->get();

        Notification::make()
            ->success()
            ->title('New user registered')
            ->body("**{$user->name}** registered and was linked to a customer profile.")
            ->icon('heroicon-o-user-plus')
            ->actions([
                Action::make('view')
                    ->label('View Customer')
                    ->url(CustomerResource::getUrl('edit', ['record' => $customer]))
                    ->markAsRead(),
            ])
            ->sendToDatabase($admins);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if (! $user->wasChanged(['name', 'email', 'password'])) {
            return;
        }

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

    private function assignPanelUserRole(User $user): void
    {
        if ($user->hasAnyRole(['super_admin', 'panel_user'])) {
            return;
        }

        $role = Role::query()->firstOrCreate([
            'name' => 'panel_user',
            'guard_name' => 'web',
        ]);

        $this->ensurePanelUserCanViewOwnRecords($role);
        $user->assignRole($role);
    }

    private function ensurePanelUserCanViewOwnRecords(Role $role): void
    {
        $permissions = Permission::query()
            ->whereIn('name', [
                'view_any_order',
                'view_order',
                'view_any_customer',
                'view_customer',
            ])
            ->get();

        if ($permissions->isNotEmpty()) {
            $role->givePermissionTo($permissions);
        }
    }
}
