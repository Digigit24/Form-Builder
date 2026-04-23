<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('response_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('response_id')->constrained('responses')->cascadeOnDelete();
            $table->foreignUuid('step_id')->constrained('form_steps')->cascadeOnDelete();
            $table->json('answer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('response_answers');
    }
};
