<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('label_name')->nullable();
            $table->string('internal_code')->nullable();
            $table->string('category')->nullable();
            $table->string('base_unit_code');
            $table->boolean('is_frozen')->default(false);
            $table->boolean('is_blast_chilled')->default(false);
            $table->boolean('is_organic')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('recipe_id')->nullable()->constrained('recipes')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('base_unit_code')->references('code')->on('units');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
