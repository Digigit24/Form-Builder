<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->json('settings')->nullable()->after('theme_config');
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('settings');
        });
    }
};
