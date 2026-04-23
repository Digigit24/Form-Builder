<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_id')->constrained('forms')->cascadeOnDelete();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
