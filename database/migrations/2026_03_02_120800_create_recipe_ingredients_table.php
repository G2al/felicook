<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_ingredients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('quantity', 12, 4);
            $table->string('unit_code');
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('recipe_id');
            $table->index('ingredient_id');
            $table->unique(['recipe_id', 'ingredient_id']);
            $table->foreign('unit_code')->references('code')->on('units');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
