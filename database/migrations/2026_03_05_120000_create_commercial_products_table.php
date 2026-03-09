<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commercial_products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->text('ingredient_list')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name']);
            $table->index(['brand']);
            $table->index(['name', 'brand']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_products');
    }
};

