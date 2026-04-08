<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormStep;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::create([
            'name' => 'Acme',
            'slug' => 'acme',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Acme Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $form = Form::create([
            'tenant_id' => $tenant->id,
            'title' => 'Lead Gen',
            'slug' => 'lead-gen-form',
            'description' => 'Tell us about yourself so we can get in touch.',
            'is_published' => true,
        ]);

        FormStep::create([
            'form_id' => $form->id,
            'type' => 'text',
            'question' => "What's your name?",
            'order_index' => 0,
        ]);

        FormStep::create([
            'form_id' => $form->id,
            'type' => 'mcq',
            'question' => 'How did you hear about us?',
            'options' => ['Twitter', 'Friend', 'Search', 'Other'],
            'order_index' => 1,
        ]);

        FormStep::create([
            'form_id' => $form->id,
            'type' => 'textarea',
            'question' => 'Anything else you want us to know?',
            'order_index' => 2,
        ]);
    }
}
