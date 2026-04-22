<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FormController extends Controller
{
    public const QUESTION_TYPES = [
        'welcome_screen', 'short_text', 'long_text', 'email', 'phone',
        'number', 'multiple_choice', 'checkboxes', 'dropdown', 'rating',
        'yes_no', 'date', 'statement', 'end_screen',
    ];

    public const CHOICE_TYPES = ['multiple_choice', 'checkboxes', 'dropdown'];

    private const TEMPLATES = [
        'blank' => [
            'title' => 'Untitled form',
            'steps' => [],
        ],
        'customer_feedback' => [
            'title' => 'Customer Feedback',
            'steps' => [
                ['type' => 'short_text',      'question' => "What's your name?",           'logic' => ['required' => true, 'placeholder' => 'Jane Doe']],
                ['type' => 'email',           'question' => "What's your email?",           'logic' => ['required' => true, 'placeholder' => 'jane@example.com']],
                ['type' => 'rating',          'question' => 'How would you rate us?',       'logic' => ['required' => true, 'scale' => 5, 'shape' => 'star']],
                ['type' => 'long_text',       'question' => 'Any additional feedback?',     'logic' => ['placeholder' => 'Tell us what you think...']],
            ],
        ],
        'job_application' => [
            'title' => 'Job Application',
            'steps' => [
                ['type' => 'short_text',      'question' => 'Full name',                   'logic' => ['required' => true, 'placeholder' => 'Your full name']],
                ['type' => 'email',           'question' => 'Email address',               'logic' => ['required' => true, 'placeholder' => 'you@example.com']],
                ['type' => 'phone',           'question' => 'Phone number',                'logic' => ['placeholder' => '+1 (555) 000-0000']],
                ['type' => 'short_text',      'question' => 'Position you are applying for', 'logic' => ['required' => true, 'placeholder' => 'e.g. Senior Designer']],
                ['type' => 'multiple_choice', 'question' => 'Years of experience',         'logic' => ['required' => true], 'options' => ['0–1 years', '1–3 years', '3–5 years', '5+ years']],
                ['type' => 'long_text',       'question' => 'Tell us about yourself',      'logic' => ['placeholder' => 'A short bio or cover note...']],
            ],
        ],
        'contact_form' => [
            'title' => 'Contact Form',
            'steps' => [
                ['type' => 'short_text',      'question' => 'Your name',                   'logic' => ['required' => true, 'placeholder' => 'Jane Doe']],
                ['type' => 'email',           'question' => 'Your email',                  'logic' => ['required' => true, 'placeholder' => 'jane@example.com']],
                ['type' => 'long_text',       'question' => 'Your message',                'logic' => ['required' => true, 'placeholder' => 'How can we help?']],
            ],
        ],
        'nps_survey' => [
            'title' => 'NPS Survey',
            'steps' => [
                ['type' => 'rating',          'question' => 'How likely are you to recommend us to a friend?', 'logic' => ['required' => true, 'scale' => 10, 'shape' => 'number']],
                ['type' => 'long_text',       'question' => 'What is the main reason for your score?',         'logic' => ['placeholder' => 'Share your thoughts...']],
            ],
        ],
        'exit_survey' => [
            'title' => 'Exit Survey',
            'steps' => [
                ['type' => 'multiple_choice', 'question' => 'Why are you leaving?',        'logic' => ['required' => true], 'options' => ['Too expensive', 'Missing features', 'Found a better alternative', 'No longer needed', 'Other']],
                ['type' => 'rating',          'question' => 'How satisfied were you overall?', 'logic' => ['required' => true, 'scale' => 5, 'shape' => 'star']],
                ['type' => 'long_text',       'question' => 'Anything we could have done better?', 'logic' => ['placeholder' => 'Your feedback helps us improve...']],
            ],
        ],
    ];

    public function index(): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    public function create(): View
    {
        return view('forms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $templateKey = $request->input('template', 'blank');
        $template    = self::TEMPLATES[$templateKey] ?? self::TEMPLATES['blank'];
        $title       = trim($request->input('title', '')) ?: $template['title'];

        $form = Form::create([
            'title'        => $title,
            'slug'         => $this->uniqueSlug($title),
            'description'  => null,
            'is_published' => false,
            'settings'     => [
                'progress_bar'   => 'bar',
                'submit_label'   => 'Submit',
                'redirect_url'   => '',
                'notify_email'   => '',
                'close_form'     => false,
                'response_limit' => null,
            ],
        ]);

        $order = 0;

        // Welcome screen
        $form->steps()->create([
            'type'        => 'welcome_screen',
            'question'    => $title,
            'options'     => null,
            'order_index' => $order++,
            'logic'       => ['subtitle' => '', 'button_label' => 'Start'],
        ]);

        // Template-specific steps
        foreach ($template['steps'] as $step) {
            $form->steps()->create([
                'type'        => $step['type'],
                'question'    => $step['question'],
                'options'     => $step['options'] ?? null,
                'order_index' => $order++,
                'logic'       => $step['logic'] ?? null,
            ]);
        }

        // End screen
        $form->steps()->create([
            'type'        => 'end_screen',
            'question'    => 'Thank you!',
            'options'     => null,
            'order_index' => $order,
            'logic'       => ['subtitle' => 'Your response has been recorded.', 'redirect_url' => ''],
        ]);

        return redirect()->route('forms.edit', $form);
    }

    public function edit(Form $form): View
    {
        $form->load('steps');

        return view('forms.builder', ['form' => $form]);
    }

    public function update(Request $request, Form $form): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'settings' => ['nullable', 'array'],
            'steps' => ['array'],
            'steps.*.type' => ['required', 'in:'.implode(',', self::QUESTION_TYPES)],
            'steps.*.question' => ['required', 'string'],
            'steps.*.options' => ['nullable', 'array'],
            'steps.*.logic' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($form, $data) {
            $form->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'settings' => array_merge($form->settings ?? [], $data['settings'] ?? []),
            ]);

            $form->steps()->delete();

            foreach ($data['steps'] ?? [] as $i => $step) {
                $options = null;
                if (in_array($step['type'], self::CHOICE_TYPES) && ! empty($step['options'])) {
                    $options = array_values(array_filter(
                        $step['options'],
                        fn ($o) => is_string($o) && trim($o) !== ''
                    ));
                }

                $form->steps()->create([
                    'type' => $step['type'],
                    'question' => $step['question'],
                    'options' => $options,
                    'order_index' => $i,
                    'logic' => $step['logic'] ?? null,
                ]);
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Form saved.']);
        }

        return redirect()->route('forms.edit', $form)->with('status', 'Form saved.');
    }

    public function destroy(Form $form): RedirectResponse
    {
        $form->delete();

        return redirect()->route('dashboard')->with('status', 'Form deleted.');
    }

    public function responses(Form $form): View
    {
        $form->load('steps');
        $responses = $form->responses()
            ->with('answers.step')
            ->latest()
            ->paginate(25);

        return view('forms.responses', [
            'form' => $form,
            'responses' => $responses,
        ]);
    }

    public function destroyResponse(Form $form, Response $response): RedirectResponse
    {
        if ($response->form_id !== $form->id) {
            abort(404);
        }

        $response->delete();

        return back()->with('status', 'Response deleted.');
    }

    public function publish(Form $form): RedirectResponse
    {
        $form->update(['is_published' => ! $form->is_published]);

        return back()->with('status', $form->is_published ? 'Form published.' : 'Form unpublished.');
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'form';
        $slug = $base;
        $i = 1;
        while (Form::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.++$i;
        }

        return $slug;
    }
}
