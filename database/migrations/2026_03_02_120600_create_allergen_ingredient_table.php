<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allergen_ingredient', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('allergen_id')->constrained('allergens')->cascadeOnDelete();
            $table->string('presence_type')->default('contains');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['ingredient_id', 'allergen_id', 'presence_type'], 'allergen_ing_presence_unique');
            $table->index('ingredient_id', 'allergen_ing_ingredient_index');
            $table->index('allergen_id', 'allergen_ing_allergen_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allergen_ingredient');
    }
};
