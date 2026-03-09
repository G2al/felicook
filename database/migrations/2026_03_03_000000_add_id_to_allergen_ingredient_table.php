<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('allergen_ingredient')) {
            return;
        }

        if (! Schema::hasColumn('allergen_ingredient', 'id')) {
            DB::statement('ALTER TABLE allergen_ingredient ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('allergen_ingredient')) {
            return;
        }

        if (Schema::hasColumn('allergen_ingredient', 'id')) {
            Schema::table('allergen_ingredient', function (Blueprint $table): void {
                $table->dropPrimary();
                $table->dropColumn('id');
            });
        }
    }
};
