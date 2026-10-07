<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('sells')->default(false)->after('password');
            $table->boolean('buys')->default(true)->after('sells');
            $table->boolean('is_admin')->default(false)->after('buys');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sells', 'buys', 'is_admin']);
        });
    }
};
