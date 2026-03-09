<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->unique(
                ['recipe_id', 'ingredient_id'],
                'recipe_ing_recipe_ingredient_unique',
            );
            $table->unique(
                ['recipe_id', 'commercial_product_id'],
                'recipe_ing_recipe_commercial_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->dropUnique('recipe_ing_recipe_ingredient_unique');
            $table->dropUnique('recipe_ing_recipe_commercial_unique');
        });
    }
};
