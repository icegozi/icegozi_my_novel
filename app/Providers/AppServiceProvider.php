<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Novel::class, \App\Policies\NovelPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Chapter::class, \App\Policies\ChapterPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\AuthorProfile::class, \App\Policies\AuthorProfilePolicy::class);
    }
}
