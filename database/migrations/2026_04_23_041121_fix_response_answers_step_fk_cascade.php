<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Run outside a transaction — ALTER TABLE on a live FK can deadlock inside one
    public $withinTransaction = false;

    public function up(): void
    {
        // Re-create the step_id FK with ON DELETE CASCADE so that deleting
        // a form_step automatically removes its response_answers rows.
        DB::statement('ALTER TABLE response_answers
            DROP CONSTRAINT IF EXISTS response_answers_step_id_foreign');

        DB::statement('ALTER TABLE response_answers
            ADD CONSTRAINT response_answers_step_id_foreign
                FOREIGN KEY (step_id)
                REFERENCES form_steps(id)
                ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE response_answers
            DROP CONSTRAINT IF EXISTS response_answers_step_id_foreign');

        DB::statement('ALTER TABLE response_answers
            ADD CONSTRAINT response_answers_step_id_foreign
                FOREIGN KEY (step_id)
                REFERENCES form_steps(id)');
    }
};
