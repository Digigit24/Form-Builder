<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormStep;
use App\Models\Response;
use App\Models\ResponseAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FormController extends Controller
{
    public const DEFAULT_DESIGN = [
        'colors' => [
            'background'  => '#ffffff',
            'questions'   => '#000000',
            'answers'     => '#000000',
            'buttons'     => '#000000',
            'button_text' => '#ffffff',
            'star_rating' => '#000000',
        ],
        'alignment'            => 'left',
        'font'                 => 'Inter',
        'font_size'            => 'medium',
        'background_image'     => null,
        'background_blur'      => 0,
        'background_opacity'   => 100,
        'background_per_block' => false,
        'logo'                 => null,
        'round_corners'        => true,
    ];

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
            'theme_config' => self::DEFAULT_DESIGN,
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
        $form->load('steps')->loadCount('responses');

        return view('forms.builder', ['form' => $form]);
    }

    public function update(Request $request, Form $form): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                // title is nullable so auto-save doesn't fail while the user is clearing/retyping it
                'title'            => ['nullable', 'string', 'max:255'],
                'description'      => ['nullable', 'string'],
                'settings'         => ['nullable', 'array'],

                // Design — every field nullable so partial payloads are safe
                'design'                      => ['nullable', 'array'],
                'design.colors'               => ['nullable', 'array'],
                'design.alignment'            => ['nullable', 'string', 'in:left,center,right'],
                'design.font'                 => ['nullable', 'string', 'max:100'],
                'design.font_size'            => ['nullable', 'string', 'in:small,medium,large'],
                'design.background_image'     => ['nullable', 'string'],
                'design.background_blur'      => ['nullable', 'numeric', 'min:0', 'max:20'],
                'design.background_opacity'   => ['nullable', 'numeric', 'min:0', 'max:100'],
                'design.background_per_block' => ['nullable', 'boolean'],
                'design.logo'                 => ['nullable', 'string'],
                'design.round_corners'        => ['nullable', 'boolean'],

                // Steps — id is nullable (new steps have none); question nullable for in-progress blocks
                'steps'            => ['array'],
                'steps.*.id'       => ['nullable', 'uuid'],
                'steps.*.type'     => ['required', 'in:'.implode(',', self::QUESTION_TYPES)],
                'steps.*.question' => ['nullable', 'string'],
                'steps.*.options'  => ['nullable', 'array'],
                'steps.*.logic'    => ['nullable', 'array'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Form auto-save validation failed', [
                'form_id' => $form->id,
                'errors'  => $e->errors(),
                'input'   => $request->except(['_token']),
            ]);
            throw $e;
        }

        try {
            // Merge new design over existing, preserving keys not present in the payload
            $newDesign = $data['design'] ?? null;
            if ($newDesign !== null) {
                $existing  = $form->theme_config ?? self::DEFAULT_DESIGN;
                $newDesign = array_merge($existing, $newDesign, [
                    'colors' => array_merge($existing['colors'] ?? [], $newDesign['colors'] ?? []),
                ]);
            }

            // Use existing title if the incoming title is blank (user mid-edit)
            $title = trim($data['title'] ?? '') ?: $form->title;

            // Update form metadata outside the steps transaction so any failure
            // surfaces immediately rather than masking as a 25P02 on the subsequent DELETE.
            $form->update([
                'title'        => $title,
                'description'  => $data['description'] ?? null,
                'settings'     => array_merge($form->settings ?? [], $data['settings'] ?? []),
                'theme_config' => $newDesign ?? $form->theme_config,
            ]);

            // ── Sync steps ───────────────────────────────────────────────────
            // Deliberately NOT using DB::transaction() here.  PostgreSQL leaves
            // the connection in an "aborted transaction" state when any statement
            // fails, and that state persists into subsequent DB::transaction()
            // calls.  Auto-save retries every few seconds, so a partial sync from
            // an interrupted request will always be resolved by the next save.
            // Each statement below runs in PostgreSQL autocommit mode, so a
            // failure is isolated and never cascades to other statements.

            // Force-rollback any dangling aborted transaction left by a previous
            // failed statement on this connection (PostgreSQL-specific quirk).
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            $incoming    = collect($data['steps'] ?? []);
            $existingIds = $form->steps()->pluck('id')->toArray();
            $incomingIds = $incoming->pluck('id')->filter()->values()->toArray();

            // Delete steps removed from the form (and their response_answers)
            $toDelete = array_diff($existingIds, $incomingIds);
            if (! empty($toDelete)) {
                ResponseAnswer::whereIn('step_id', $toDelete)->delete();
                FormStep::whereIn('id', $toDelete)->delete();
            }

            // Update existing steps in-place (UUID preserved → response_answers stay linked)
            // and insert genuinely new steps
            foreach ($incoming as $i => $step) {
                $options = null;
                if (in_array($step['type'], self::CHOICE_TYPES) && ! empty($step['options'])) {
                    $options = array_values(array_filter(
                        $step['options'],
                        fn ($o) => is_string($o) && trim($o) !== ''
                    ));
                }

                $attrs = [
                    'type'        => $step['type'],
                    'question'    => $step['question'] ?? '',
                    'options'     => $options,
                    'order_index' => $i,
                    'logic'       => $step['logic'] ?? null,
                ];

                $stepId = $step['id'] ?? null;

                if ($stepId && in_array($stepId, $existingIds)) {
                    $form->steps()->where('id', $stepId)->update($attrs);
                } else {
                    $form->steps()->create($attrs);
                }
            }

            Log::info('Form auto-saved', ['form_id' => $form->id, 'user_id' => auth()->id()]);

        } catch (\Throwable $e) {
            Log::error('Form auto-save failed', [
                'form_id'   => $form->id,
                'user_id'   => auth()->id(),
                'error'     => $e->getMessage(),
                'exception' => get_class($e),
                'file'      => $e->getFile().':'.$e->getLine(),
                'trace'     => collect(explode("\n", $e->getTraceAsString()))->take(10)->implode("\n"),
            ]);
            throw $e;
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
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

    public function publish(Form $form): RedirectResponse|JsonResponse
    {
        $form->update(['is_published' => ! $form->is_published]);

        if (request()->wantsJson()) {
            return response()->json(['ok' => true, 'is_published' => $form->is_published]);
        }

        return back()->with('status', $form->is_published ? 'Form published.' : 'Form unpublished.');
    }

    public function uploadMedia(Request $request, Form $form): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'max:5120'],
            'type' => ['required', 'in:background,logo'],
        ]);

        $path = $request->file('file')->store("form-media/{$form->id}", 'public');

        return response()->json(['url' => asset('storage/'.$path)]);
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
