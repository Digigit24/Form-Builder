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
            'title' => 'Customer Feedback',
            'slug' => 'customer-feedback',
            'description' => 'We value your feedback!',
            'is_published' => true,
            'settings' => [
                'progress_bar' => 'bar',
                'submit_label' => 'Submit',
                'redirect_url' => '',
                'notify_email' => '',
                'close_form' => false,
                'response_limit' => null,
            ],
        ]);

        $steps = [
            ['type' => 'welcome_screen', 'question' => 'Customer Feedback', 'order_index' => 0, 'logic' => ['subtitle' => 'Help us improve by answering a few questions.', 'button_label' => 'Start']],
            ['type' => 'short_text', 'question' => "What's your name?", 'order_index' => 1, 'logic' => ['required' => true, 'placeholder' => 'John Doe']],
            ['type' => 'email', 'question' => "What's your email?", 'order_index' => 2, 'logic' => ['required' => true, 'placeholder' => 'john@example.com']],
            ['type' => 'multiple_choice', 'question' => 'How did you hear about us?', 'order_index' => 3, 'options' => ['Google', 'Social Media', 'Friend', 'Other'], 'logic' => ['required' => true]],
            ['type' => 'rating', 'question' => 'How would you rate our service?', 'order_index' => 4, 'logic' => ['required' => true, 'scale' => 5, 'shape' => 'star']],
            ['type' => 'yes_no', 'question' => 'Would you recommend us to a friend?', 'order_index' => 5, 'logic' => ['required' => true]],
            ['type' => 'long_text', 'question' => 'Any additional feedback?', 'order_index' => 6, 'logic' => ['placeholder' => 'Tell us what you think...']],
            ['type' => 'end_screen', 'question' => 'Thank you!', 'order_index' => 7, 'logic' => ['subtitle' => 'We appreciate your feedback. Have a great day!']],
        ];

        foreach ($steps as $step) {
            FormStep::create([
                'form_id' => $form->id,
                'type' => $step['type'],
                'question' => $step['question'],
                'options' => $step['options'] ?? null,
                'order_index' => $step['order_index'],
                'logic' => $step['logic'] ?? null,
            ]);
        }
    }
}
