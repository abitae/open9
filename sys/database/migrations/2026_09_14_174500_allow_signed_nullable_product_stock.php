<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE products MODIFY stock INT NULL');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::table('products')->where('stock', '<', 0)->update(['stock' => 0]);
            DB::table('products')->whereNull('stock')->update(['stock' => 0]);
            DB::statement('ALTER TABLE products MODIFY stock INT UNSIGNED NOT NULL DEFAULT 0');
        }
    }
};
