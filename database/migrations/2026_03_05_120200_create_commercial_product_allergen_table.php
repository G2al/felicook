<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('commercial_product_allergen');

        Schema::create('commercial_product_allergen', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('commercial_product_id');
            $table->unsignedBigInteger('allergen_id');
            $table->string('presence_type')->default('contains');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['commercial_product_id', 'allergen_id', 'presence_type'],
                'commercial_prod_allergen_presence_unique',
            );
            $table->index('commercial_product_id', 'commercial_prod_allergen_product_index');
            $table->index('allergen_id', 'commercial_prod_allergen_allergen_index');

            $table->foreign('commercial_product_id', 'cmp_prod_allergen_product_fk')
                ->references('id')
                ->on('commercial_products')
                ->cascadeOnDelete();
            $table->foreign('allergen_id', 'cmp_prod_allergen_allergen_fk')
                ->references('id')
                ->on('allergens')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_product_allergen');
    }
};
