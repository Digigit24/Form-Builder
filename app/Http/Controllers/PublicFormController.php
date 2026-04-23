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
    private const NON_INPUT_TYPES = ['welcome_screen', 'end_screen', 'statement'];

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

        $inputSteps = $form->steps->reject(fn ($s) => in_array($s->type, self::NON_INPUT_TYPES));
        $stepIds = $inputSteps->pluck('id')->all();
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

        $endScreen = $form->steps->firstWhere('type', 'end_screen');
        $redirectUrl = data_get($endScreen, 'logic.redirect_url')
            ?: $form->setting('redirect_url');

        return response()->json([
            'ok' => true,
            'redirect' => $redirectUrl ?: null,
        ]);
    }

    public function thanks(string $slug): View
    {
        $form = Form::withoutGlobalScopes()
            ->with(['tenant', 'steps'])
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
        $tc = $form->theme_config ?? [];

        if (isset($tc['colors'])) {
            $bg = $tc['colors']['background'] ?? '#0f0f13';
            return [
                'primary_color'      => $tc['colors']['buttons'] ?? '#6366f1',
                'background_color'   => $bg,
                'question_color'     => $tc['colors']['questions'] ?? '#ffffff',
                'answer_color'       => $tc['colors']['answers'] ?? '#ffffff',
                'button_text_color'  => $tc['colors']['button_text'] ?? '#ffffff',
                'star_color'         => $tc['colors']['star_rating'] ?? '#6366f1',
                'font_family'        => $tc['font'] ?? 'Inter',
                'font_size'          => $tc['font_size'] ?? 'medium',
                'background_image'   => $tc['background_image']   ?? null,
                'background_blur'    => $tc['background_blur']   ?? 0,
                'background_opacity' => $tc['background_opacity'] ?? 100,
                'logo'               => $tc['logo'] ?? null,
                'round_corners'      => $tc['round_corners'] ?? true,
                'alignment'          => $tc['alignment'] ?? 'left',
                'is_light'           => $this->isLightColor($bg),
                'per_block_bg'       => $tc['background_per_block'] ?? false,
            ];
        }

        $tenant = $form->tenant;
        $bg = $tenant->background_color ?? '#0f0f13';

        return array_merge([
            'primary_color'     => $tenant->primary_color ?? '#6366f1',
            'background_color'  => $bg,
            'question_color'    => '#ffffff',
            'answer_color'      => '#ffffff',
            'button_text_color' => '#ffffff',
            'star_color'        => '#6366f1',
            'font_family'       => $tenant->font_family ?? 'Inter',
            'font_size'         => 'medium',
            'background_image'   => null,
            'background_blur'    => 0,
            'background_opacity' => 100,
            'logo'               => null,
            'round_corners'     => true,
            'alignment'         => 'left',
            'is_light'          => $this->isLightColor($bg),
            'per_block_bg'      => false,
        ], $tc);
    }

    private function isLightColor(string $hex): bool
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) return false;
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255 > 0.5;
    }
}
