<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_batch_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('production_batch_id')->constrained('production_batches')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->foreignId('commercial_product_id')->nullable()->constrained('commercial_products')->nullOnDelete();
            $table->string('source_type', 40)->default('ingredient');
            $table->string('name_snapshot');
            $table->decimal('quantity', 12, 4);
            $table->string('unit_code');
            $table->decimal('quantity_in_grams', 12, 4)->nullable();
            $table->string('lot_code');
            $table->date('expires_at');
            $table->unsignedInteger('sort_order')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('unit_code')->references('code')->on('units');
            $table->index('production_batch_id');
            $table->index('ingredient_id');
            $table->index('commercial_product_id');
            $table->index('source_type');
            $table->index(['production_batch_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_batch_items');
    }
};
