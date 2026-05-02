<?php

namespace App\Observers;

use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        $linkedUser = $this->findOrCreateUser($customer);
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

        if ($linkedUser) {
            Notification::make()
                ->success()
                ->title('Customer account ready')
                ->body('Your user account has been linked to your customer profile. Use password reset if you need to set your password.')
                ->icon('heroicon-o-user-circle')
                ->sendToDatabase($linkedUser);
        }
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

    private function findOrCreateUser(Customer $customer): ?User
    {
        if (! $customer->email) {
            return null;
        }

        $user = User::query()->where('email', $customer->email)->first();

        if (! $user) {
            $user = User::withoutEvents(fn () => User::create([
                'name' => $customer->full_name,
                'email' => $customer->email,
                'password' => Hash::make(Str::random(32)),
            ]));
        }

        if (! $customer->user_id) {
            Customer::withoutEvents(fn () => $customer->update(['user_id' => $user->id]));
        }

        $role = Role::query()->firstOrCreate([
            'name' => 'panel_user',
            'guard_name' => 'web',
        ]);

        if (! $user->hasAnyRole(['super_admin', 'panel_user'])) {
            $this->ensurePanelUserCanViewOwnRecords($role);
            $user->assignRole($role);
        }

        return $user;
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
