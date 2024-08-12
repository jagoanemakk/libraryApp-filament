<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Books;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Policies\BooksPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Books::class => BooksPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
