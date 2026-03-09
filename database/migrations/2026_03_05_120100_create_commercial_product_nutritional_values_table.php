<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('commercial_product_nutritional_values');

        Schema::create('commercial_product_nutritional_values', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('commercial_product_id');
            $table->decimal('energy_kj', 12, 4)->nullable();
            $table->decimal('energy_kcal', 12, 4)->nullable();
            $table->decimal('fat', 12, 4)->nullable();
            $table->decimal('saturated_fat', 12, 4)->nullable();
            $table->decimal('mono_fat', 12, 4)->nullable();
            $table->decimal('poly_fat', 12, 4)->nullable();
            $table->decimal('carbs', 12, 4)->nullable();
            $table->decimal('sugars', 12, 4)->nullable();
            $table->decimal('polyols', 12, 4)->nullable();
            $table->decimal('erythritol', 12, 4)->nullable();
            $table->decimal('fiber', 12, 4)->nullable();
            $table->decimal('protein', 12, 4)->nullable();
            $table->decimal('salt', 12, 4)->nullable();
            $table->decimal('alcohol', 12, 4)->nullable();
            $table->decimal('water', 12, 4)->nullable();
            $table->decimal('edible_part_percentage', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('commercial_product_id', 'cmp_prod_nutr_product_unique');
            $table->foreign('commercial_product_id', 'cmp_prod_nutr_product_fk')
                ->references('id')
                ->on('commercial_products')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_product_nutritional_values');
    }
};
