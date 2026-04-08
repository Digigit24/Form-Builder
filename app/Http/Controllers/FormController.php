<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FormController extends Controller
{
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
        ]);

        return redirect()->route('forms.edit', $form);
    }

    public function edit(Form $form): View
    {
        $form->load('steps');

        return view('forms.builder', ['form' => $form]);
    }

    public function update(Request $request, Form $form): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'steps' => ['array'],
            'steps.*.type' => ['required', 'in:text,textarea,mcq,multi'],
            'steps.*.question' => ['required', 'string'],
            'steps.*.options' => ['nullable', 'array'],
            'steps.*.options.*' => ['string'],
        ]);

        DB::transaction(function () use ($form, $data) {
            $form->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
            ]);

            $form->steps()->delete();

            foreach ($data['steps'] ?? [] as $i => $step) {
                $form->steps()->create([
                    'type' => $step['type'],
                    'question' => $step['question'],
                    'options' => in_array($step['type'], ['mcq', 'multi'])
                        ? array_values(array_filter($step['options'] ?? [], fn ($o) => trim($o) !== ''))
                        : null,
                    'order_index' => $i,
                ]);
            }
        });

        return redirect()->route('forms.edit', $form)->with('status', 'Form saved.');
    }

    public function destroy(Form $form): RedirectResponse
    {
        $form->delete();

        return redirect()->route('dashboard')->with('status', 'Form deleted.');
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
