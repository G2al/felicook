<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->nullable()->constrained('recipes')->nullOnDelete();
            $table->string('lot_code')->unique();
            $table->date('production_date');
            $table->date('expires_at');
            $table->decimal('produced_weight', 12, 4);
            $table->string('currency', 10)->default('EUR');
            $table->decimal('recipe_total_cost', 14, 4)->nullable();
            $table->decimal('recipe_total_weight', 12, 4)->nullable();
            $table->decimal('total_cost', 14, 4)->default(0);
            $table->decimal('cost_per_kg', 14, 4)->default(0);
            $table->decimal('public_price_per_kg', 14, 4)->nullable();
            $table->json('allergens_snapshot')->nullable();
            $table->json('nutrition_snapshot')->nullable();
            $table->json('recipe_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('recipe_id');
            $table->index('production_date');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_batches');
    }
};
