<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('type');
            $table->text('question');
            $table->json('options')->nullable();
            $table->integer('order_index');
            $table->json('logic')->nullable();
            $table->timestamps();

            $table->index(['form_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_steps');
    }
};
