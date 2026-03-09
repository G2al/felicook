<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ProductionBatch;
use App\Models\RecipeIngredient;
use App\Observers\ProductionBatchObserver;
use App\Observers\RecipeIngredientObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        RecipeIngredient::observe(RecipeIngredientObserver::class);
        ProductionBatch::observe(ProductionBatchObserver::class);
    }
}
