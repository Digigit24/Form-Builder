<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicFormController extends Controller
{
    public function show(string $slug): View
    {
        $form = Form::withoutGlobalScopes()
            ->with(['steps', 'tenant'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $theme = $this->buildTheme($form);

        return view('public.render', [
            'form' => $form,
            'theme' => $theme,
        ]);
    }

    public function submit(Request $request, string $slug): JsonResponse
    {
        $form = Form::withoutGlobalScopes()
            ->with('steps')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $data = $request->validate([
            'answers' => ['required', 'array'],
        ]);

        $stepIds = $form->steps->pluck('id')->all();
        $answers = collect($data['answers'])->only($stepIds);

        DB::transaction(function () use ($form, $answers) {
            $response = Response::create([
                'form_id' => $form->id,
                'tenant_id' => $form->tenant_id,
            ]);

            foreach ($answers as $stepId => $value) {
                $response->answers()->create([
                    'step_id' => $stepId,
                    'answer' => is_array($value) ? $value : ['value' => $value],
                ]);
            }
        });

        return response()->json([
            'ok' => true,
            'redirect' => route('public.form.thanks', $form->slug),
        ]);
    }

    public function thanks(string $slug): View
    {
        $form = Form::withoutGlobalScopes()
            ->with('tenant')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('public.thanks', [
            'form' => $form,
            'theme' => $this->buildTheme($form),
        ]);
    }

    private function buildTheme(Form $form): array
    {
        return array_merge([
            'primary_color' => $form->tenant->primary_color,
            'secondary_color' => $form->tenant->secondary_color,
            'background_color' => $form->tenant->background_color,
            'font_family' => $form->tenant->font_family,
        ], $form->theme_config ?? []);
    }
}
