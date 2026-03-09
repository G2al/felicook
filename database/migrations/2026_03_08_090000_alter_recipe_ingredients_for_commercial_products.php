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
            $table->dropUnique('recipe_ingredients_recipe_id_ingredient_id_unique');
            $table->unsignedBigInteger('ingredient_id')->nullable()->change();
            $table->foreignId('commercial_product_id')
                ->nullable()
                ->after('ingredient_id')
                ->constrained('commercial_products')
                ->nullOnDelete();
            $table->index('commercial_product_id');
            $table->index(['recipe_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->dropIndex(['recipe_id', 'sort_order']);
            $table->dropIndex(['commercial_product_id']);
            $table->dropConstrainedForeignId('commercial_product_id');
            $table->unsignedBigInteger('ingredient_id')->nullable(false)->change();
            $table->unique(['recipe_id', 'ingredient_id']);
        });
    }
};
