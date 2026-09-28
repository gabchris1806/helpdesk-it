<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Location;
use App\Models\MasterLapor;
use App\Models\Sla;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\LocationPolicy;
use App\Policies\MasterLaporPolicy;
use App\Policies\SlaPolicy;
use App\Policies\TicketPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Category::class => CategoryPolicy::class,
        Location::class => LocationPolicy::class,
        MasterLapor::class => MasterLaporPolicy::class,
        Sla::class => SlaPolicy::class,
        Ticket::class => TicketPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
