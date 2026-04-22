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

    public function index(): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    public function store(Request $request): RedirectResponse
    {
        $title = trim($request->input('title', '')) ?: 'Untitled form';

        $form = Form::create([
            'title' => $title,
            'slug' => $this->uniqueSlug($title),
            'description' => null,
            'is_published' => false,
            'settings' => [
                'progress_bar' => 'bar',
                'submit_label' => 'Submit',
                'redirect_url' => '',
                'notify_email' => '',
                'close_form' => false,
                'response_limit' => null,
            ],
        ]);

        // Auto-add welcome + end screen
        $form->steps()->create([
            'type' => 'welcome_screen',
            'question' => $title,
            'options' => null,
            'order_index' => 0,
            'logic' => ['subtitle' => '', 'button_label' => 'Start'],
        ]);
        $form->steps()->create([
            'type' => 'end_screen',
            'question' => 'Thank you!',
            'options' => null,
            'order_index' => 1,
            'logic' => ['subtitle' => 'Your response has been recorded.', 'redirect_url' => ''],
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
