<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Observers\CustomerObserver;
use App\Observers\OrderObserver;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::unguard();

        User::observe(UserObserver::class);
        Order::observe(OrderObserver::class);
        Customer::observe(CustomerObserver::class);
    }
}
