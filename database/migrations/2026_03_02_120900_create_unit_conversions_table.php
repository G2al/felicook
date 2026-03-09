<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_conversions', function (Blueprint $table): void {
            $table->id();
            $table->string('from_unit_code');
            $table->string('to_unit_code');
            $table->decimal('multiplier', 18, 10);
            $table->timestamps();

            $table->unique(['from_unit_code', 'to_unit_code']);
            $table->index('from_unit_code');
            $table->index('to_unit_code');

            $table->foreign('from_unit_code')->references('code')->on('units')->cascadeOnDelete();
            $table->foreign('to_unit_code')->references('code')->on('units')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};
