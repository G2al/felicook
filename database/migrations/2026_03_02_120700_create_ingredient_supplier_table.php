<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_supplier', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->decimal('price', 12, 4);
            $table->string('currency')->default('EUR');
            $table->string('price_type')->default('per_unit');
            $table->string('unit_code')->nullable();
            $table->decimal('pack_quantity', 12, 4)->nullable();
            $table->string('pack_unit_code')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('ingredient_id');
            $table->index('supplier_id');
            $table->index(['ingredient_id', 'supplier_id', 'valid_from']);

            $table->foreign('unit_code')->references('code')->on('units');
            $table->foreign('pack_unit_code')->references('code')->on('units');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_supplier');
    }
};
