<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $form->title }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family={{ urlencode($theme['font_family']) }}:300,400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $fontSizeMap = ['small' => '14px', 'medium' => '16px', 'large' => '18px'];
        $baseFontSize = $fontSizeMap[$theme['font_size'] ?? 'medium'] ?? '16px';
        $radius = ($theme['round_corners'] ?? true) ? '0.625rem' : '0.25rem';
        $isLight = $theme['is_light'] ?? false;
        $alignment = $theme['alignment'] ?? 'left';
    @endphp
    <style>
        :root {
            --primary:        {{ $theme['primary_color'] }};
            --bg:             {{ $theme['background_color'] }};
            --color-question: {{ $theme['question_color'] }};
            --color-answer:   {{ $theme['answer_color'] }};
            --color-btn-text: {{ $theme['button_text_color'] }};
            --color-star:     {{ $theme['star_color'] }};
            --font:           '{{ $theme['font_family'] }}', system-ui, sans-serif;
            --font-size:      {{ $baseFontSize }};
            --radius:         {{ $radius }};
        }
        *, *::before, *::after { box-sizing: border-box; }
        html, body {
            margin: 0; height: 100%;
            background: var(--bg);
            font-family: var(--font);
            font-size: var(--font-size);
            color: var(--color-question);
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        /* Background image rendered in its own layer for blur/opacity support */
        #bg-image-layer {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            @if($theme['background_image'] ?? null)
            background-image: url('{{ $theme['background_image'] }}');
            background-size: cover;
            background-position: center;
            filter: blur({{ $theme['background_blur'] ?? 0 }}px);
            opacity: {{ ($theme['background_opacity'] ?? 100) / 100 }};
            @php $blur = $theme['background_blur'] ?? 0; @endphp
            @if($blur > 0)
            transform: scale({{ number_format(1 + $blur * 0.012, 3) }});
            @endif
            @else
            display: none;
            @endif
        }
        .slide-content { text-align: {{ $alignment }}; }

        /* Subtle radial glow behind the form */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background: radial-gradient(ellipse 80% 55% at 50% -5%, rgba(99,102,241,.11), transparent 65%);
            pointer-events: none; z-index: 0;
        }

        /* Progress bar */
        .progress {
            position: fixed; top: 0; left: 0; height: 2px;
            background: var(--primary);
            transition: width .5s cubic-bezier(.4,0,.2,1);
            z-index: 100; border-radius: 0 1px 1px 0;
        }

        /* Slide system */
        .slide {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            padding: 1.5rem 1.5rem 5.5rem;
            transition: transform .48s cubic-bezier(.4,0,.2,1), opacity .48s cubic-bezier(.4,0,.2,1);
            will-change: transform, opacity; z-index: 10;
        }
        .slide-enter  { transform: translateY(56px); opacity: 0; pointer-events: none; }
        .slide-active { transform: translateY(0);    opacity: 1; }
        .slide-exit   { transform: translateY(-56px);opacity: 0; pointer-events: none; }

        /* Text input */
        .tf-input {
            background: transparent; border: none;
            border-bottom: 2px solid rgba(128,128,128,.22);
            color: var(--color-answer);
            font-size: clamp(1.1875rem, 3vw, 1.5rem);
            width: 100%; padding: .875rem 0; outline: none;
            font-family: var(--font); font-weight: 400; line-height: 1.5;
            transition: border-color .2s, box-shadow .2s;
        }
        .tf-input:focus {
            border-bottom-color: var(--primary);
            box-shadow: 0 1px 0 0 var(--primary);
        }
        .tf-input::placeholder { color: var(--color-answer); opacity: .25; }
        textarea.tf-input { resize: none; }
        select.tf-input { -webkit-appearance: none; cursor: pointer; }

        /* OK / submit button */
        .tf-btn {
            display: inline-flex; align-items: center; gap: .5rem;
            background: var(--primary); color: var(--color-btn-text); border: none;
            padding: .75rem 1.625rem;
            border-radius: var(--radius); font-size: .875rem; font-weight: 600;
            cursor: pointer; font-family: var(--font); letter-spacing: .015em;
            transition: transform .17s, filter .17s;
            position: relative; overflow: hidden;
        }
        .tf-btn::after {
            content: ''; position: absolute; inset: 0;
            background: rgba(255,255,255,.1); opacity: 0; transition: opacity .17s;
        }
        .tf-btn:hover::after { opacity: 1; }
        .tf-btn:hover { transform: translateY(-1px); }
        .tf-btn:active { transform: translateY(0); }
        .tf-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

        /* Hero start button (welcome screen) */
        .hero-btn {
            display: inline-flex; align-items: center; gap: .625rem;
            background: var(--primary); color: var(--color-btn-text); border: none;
            padding: 1rem 2.375rem; border-radius: var(--radius);
            font-size: 1.0625rem; font-weight: 600;
            cursor: pointer; font-family: var(--font); letter-spacing: .01em;
            transition: transform .2s, filter .2s;
            box-shadow: 0 8px 28px rgba(0,0,0,.3);
        }
        .hero-btn:hover { transform: translateY(-2px); filter: brightness(1.09); }
        .hero-btn:active { transform: translateY(0); }

        /* Choice buttons */
        .choice-btn {
            display: flex; align-items: center; gap: .875rem;
            width: 100%; text-align: left;
            padding: .875rem 1.125rem;
            border: 1.5px solid rgba(128,128,128,.18);
            border-radius: var(--radius);
            background: rgba(128,128,128,.04);
            color: var(--color-question);
            opacity: .85;
            cursor: pointer; font-size: .9375rem; font-family: var(--font);
            transition: border-color .15s, background .15s, transform .15s, opacity .15s;
            margin-bottom: .4375rem;
        }
        .choice-btn:hover {
            border-color: rgba(128,128,128,.32);
            background: rgba(128,128,128,.08);
            opacity: 1;
            transform: translateX(3px);
        }
        .choice-btn.selected {
            border-color: var(--primary);
            background: color-mix(in srgb, var(--primary) 12%, transparent);
            color: var(--color-question);
            opacity: 1;
        }
        .choice-key {
            width: 1.625rem; height: 1.625rem;
            border-radius: .3125rem; border: 1.5px solid rgba(128,128,128,.25);
            display: flex; align-items: center; justify-content: center;
            font-size: .625rem; font-weight: 700; flex-shrink: 0;
            letter-spacing: .05em;
            transition: background .15s, border-color .15s, color .15s;
        }
        .choice-btn.selected .choice-key {
            background: var(--primary); border-color: var(--primary);
            color: var(--color-btn-text);
        }

        /* Rating stars / numbers */
        .rating-btn {
            width: 3.125rem; height: 3.125rem;
            border-radius: var(--radius);
            border: 1.5px solid rgba(128,128,128,.18);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: border-color .18s, background .18s, transform .18s, color .18s;
            background: rgba(128,128,128,.04);
            color: var(--color-question);
            opacity: .4;
        }
        .rating-btn:hover {
            border-color: rgba(128,128,128,.32);
            background: rgba(128,128,128,.08);
            transform: translateY(-3px) scale(1.09);
            opacity: .85;
        }
        .rating-btn.selected {
            border-color: var(--color-star);
            background: color-mix(in srgb, var(--color-star) 15%, transparent);
            color: var(--color-star);
            opacity: 1;
            transform: translateY(-1px);
        }

        /* Yes / No */
        .yesno-btn {
            flex: 1; padding: 1.125rem 1rem;
            border-radius: var(--radius); border: 1.5px solid rgba(128,128,128,.18);
            text-align: center; font-weight: 600; font-size: 1rem;
            cursor: pointer; font-family: var(--font);
            transition: border-color .18s, background .18s, transform .18s, color .18s;
            background: rgba(128,128,128,.04);
            color: var(--color-question);
            opacity: .75;
            display: flex; flex-direction: column; align-items: center; gap: .375rem;
        }
        .yesno-btn:hover {
            border-color: rgba(128,128,128,.32);
            background: rgba(128,128,128,.08);
            transform: translateY(-2px); opacity: 1;
        }
        .yesno-btn.selected {
            border-color: var(--primary);
            background: color-mix(in srgb, var(--primary) 12%, transparent);
            color: var(--color-question);
            opacity: 1;
        }

        /* Keyboard hint row */
        .kbd-hint {
            display: inline-flex; align-items: center; gap: .3rem;
            font-size: .6875rem; color: var(--color-question);
            opacity: .28; margin-top: .875rem; letter-spacing: .025em;
        }
        kbd {
            background: rgba(128,128,128,.1); border: 1px solid rgba(128,128,128,.18);
            padding: .125rem .375rem; border-radius: .25rem;
            font-size: .625rem; font-family: inherit; font-weight: 600;
        }

        /* Question number label */
        .q-label {
            display: flex; align-items: center; gap: .3rem;
            font-size: .75rem; color: var(--color-question);
            opacity: .35; margin-bottom: .875rem; font-weight: 500; letter-spacing: .04em;
        }
        .q-arrow { color: var(--primary); font-style: normal; }

        /* Bottom navigation bar */
        .nav-bar {
            position: fixed; bottom: 0; left: 0; right: 0;
            padding: 1.125rem 1.5rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            background: linear-gradient(to top, color-mix(in srgb, var(--bg) 80%, transparent) 0%, transparent 100%);
            z-index: 50;
        }
        .nav-arrow {
            width: 2.25rem; height: 2.25rem; border-radius: var(--radius);
            border: 1px solid rgba(128,128,128,.18);
            background: rgba(128,128,128,.05);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--color-question);
            opacity: .4;
            transition: background .15s, color .15s, border-color .15s, opacity .15s;
        }
        .nav-arrow:hover:not(:disabled) {
            background: rgba(128,128,128,.1);
            border-color: rgba(128,128,128,.3);
            opacity: 1;
        }
        .nav-arrow:disabled { opacity: .15; cursor: not-allowed; }

        /* Ensure slide text inherits theme color */
        .slide h1, .slide h2 { color: var(--color-question); }
        /* Subtitle / muted text */
        .tf-muted { color: var(--color-question); opacity: .45; }
        .tf-subtle { color: var(--color-question); opacity: .28; }
        /* Alignment */
        .slide-content { text-align: {{ $alignment }}; }
        .slide-content.text-center { text-align: center; }

        /* Animated checkmark (end screen) */
        @keyframes circle-pop {
            0%   { transform: scale(0); opacity: 0; }
            60%  { transform: scale(1.08); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes check-draw {
            to { stroke-dashoffset: 0; }
        }
        .check-circle { animation: circle-pop .5s cubic-bezier(.34,1.56,.64,1) forwards; }
        .check-path {
            stroke-dasharray: 52; stroke-dashoffset: 52;
            animation: check-draw .38s .38s ease forwards;
        }
    </style>
</head>
<body>

<div id="bg-image-layer"></div>

@if($theme['logo'] ?? null)
<div class="fixed top-5 left-6 z-50">
    <img src="{{ $theme['logo'] }}" alt="Logo" class="h-8 w-auto max-w-[140px] object-contain">
</div>
@endif

<div
    x-data="formFlow({{ Js::from([
        'steps' => $form->steps->map(fn ($s) => [
            'id'      => $s->id,
            'type'    => $s->type,
            'question'=> $s->question,
            'options' => $s->options ?? [],
            'logic'   => $s->logic ?? [],
        ])->values(),
        'submitUrl' => route('public.form.submit', $form->slug),
        'thanksUrl' => route('public.form.thanks', $form->slug),
        'settings'  => $form->settings ?? [],
    ]) }})"
    @keydown.window="handleKey($event)"
    class="relative h-screen w-full z-10"
>

    {{-- ── Progress indicator ─────────────────────────────────────────────── --}}
    <template x-if="(settings.progress_bar || 'bar') !== 'hidden'">
        <div>
            <template x-if="(settings.progress_bar || 'bar') === 'bar'">
                <div class="progress" :style="`width: ${progress}%`"></div>
            </template>
            <template x-if="(settings.progress_bar || 'bar') === 'percentage'">
                <div class="fixed top-4 right-6 text-xs z-50 font-semibold tracking-wide tabular-nums tf-subtle"
                     x-text="Math.round(progress) + '%'"></div>
            </template>
            <template x-if="(settings.progress_bar || 'bar') === 'dots'">
                <div class="fixed top-5 left-1/2 -translate-x-1/2 flex gap-1.5 z-50 items-center">
                    <template x-for="(_, di) in steps" :key="di">
                        <div class="h-1.5 rounded-full transition-all duration-300"
                             :style="di < currentStep ? 'width:1rem;background:var(--color-question);opacity:.55' : di === currentStep ? 'width:1.5rem;background:var(--color-question);opacity:1' : 'width:0.375rem;background:var(--color-question);opacity:.18'"></div>
                    </template>
                </div>
            </template>
        </div>
    </template>

    {{-- ── Slides ──────────────────────────────────────────────────────────── --}}
    <template x-for="(step, i) in steps" :key="step.id || i">
        <div class="slide" :class="slideClass(i)">
            <div class="slide-content w-full max-w-2xl mx-auto">

                {{-- Welcome screen --}}
                <template x-if="step.type === 'welcome_screen'">
                    <div class="text-center py-6">
                        <h1 class="text-5xl md:text-6xl font-bold mb-5 leading-tight tracking-tight"
                            x-text="step.question"></h1>
                        <p class="text-xl tf-muted mb-12 font-light max-w-lg mx-auto leading-relaxed"
                           x-text="step.logic.subtitle || step.logic.description || ''"></p>
                        <button @click="next()" class="hero-btn">
                            <span x-text="step.logic.button_label || 'Start'"></span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                            </svg>
                        </button>
                    </div>
                </template>

                {{-- End screen --}}
                <template x-if="step.type === 'end_screen'">
                    <div class="text-center py-6">
                        <div class="flex justify-center mb-8">
                            <svg class="w-20 h-20 check-circle" viewBox="0 0 80 80" fill="none">
                                <circle cx="40" cy="40" r="38"
                                        stroke="var(--primary)" stroke-width="1.5"
                                        fill="color-mix(in srgb, var(--primary) 8%, transparent)"/>
                                <path class="check-path"
                                      d="M24 41l12 11 20-24"
                                      stroke="var(--primary)" stroke-width="3"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h1 class="text-4xl md:text-5xl font-bold mb-4 tracking-tight"
                            x-text="step.question || 'Thank you!'"></h1>
                        <p class="text-lg tf-muted font-light"
                           x-text="step.logic.subtitle || step.logic.description || ''"></p>
                    </div>
                </template>

                {{-- Statement --}}
                <template x-if="step.type === 'statement'">
                    <div>
                        <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-snug tracking-tight"
                            x-text="step.question"></h1>
                        <p class="text-lg tf-muted mb-10 font-light leading-relaxed"
                           x-text="step.logic.description || ''"></p>
                        <button @click="next()" class="tf-btn" x-text="step.logic.button_label || 'Continue'"></button>
                    </div>
                </template>

                {{-- Input questions --}}
                <template x-if="!['welcome_screen','end_screen','statement'].includes(step.type)">
                    <div>
                        <div class="q-label">
                            <span x-text="questionNumber(i)"></span>
                            <i class="q-arrow">→</i>
                        </div>

                        <h1 class="text-3xl md:text-4xl font-bold leading-snug tracking-tight">
                            <span x-text="step.question || 'Untitled'"></span><span
                                x-show="step.logic.required"
                                class="ml-1 text-red-400"
                                style="font-size:.55em; vertical-align:super">*</span>
                        </h1>

                        <p x-show="step.logic.description"
                           class="tf-muted text-base mt-3 font-light leading-relaxed"
                           x-text="step.logic.description"></p>

                        <div class="mt-8">

                            {{-- Short text --}}
                            <template x-if="step.type === 'short_text'">
                                <div>
                                    <input type="text" class="tf-input"
                                        :placeholder="step.logic.placeholder || 'Type your answer here...'"
                                        :value="answers[step.id] || ''"
                                        @input="answers[step.id] = $event.target.value"
                                        @keydown.enter.prevent="next()">
                                    <div class="kbd-hint"><kbd>Enter</kbd><span>to continue ↵</span></div>
                                </div>
                            </template>

                            {{-- Long text --}}
                            <template x-if="step.type === 'long_text'">
                                <div>
                                    <textarea class="tf-input" rows="4"
                                        :placeholder="step.logic.placeholder || 'Type your answer here...'"
                                        :value="answers[step.id] || ''"
                                        @input="answers[step.id] = $event.target.value"></textarea>
                                    <div class="kbd-hint">
                                        <kbd>Shift</kbd>+<kbd>Enter</kbd><span>for new line</span>
                                    </div>
                                </div>
                            </template>

                            {{-- Email --}}
                            <template x-if="step.type === 'email'">
                                <div>
                                    <input type="email" class="tf-input"
                                        :placeholder="step.logic.placeholder || 'name@example.com'"
                                        :value="answers[step.id] || ''"
                                        @input="answers[step.id] = $event.target.value"
                                        @keydown.enter.prevent="next()">
                                    <div class="kbd-hint"><kbd>Enter</kbd><span>to continue ↵</span></div>
                                </div>
                            </template>

                            {{-- Phone --}}
                            <template x-if="step.type === 'phone'">
                                <div>
                                    <input type="tel" class="tf-input"
                                        :placeholder="step.logic.placeholder || '+1 (555) 000-0000'"
                                        :value="answers[step.id] || ''"
                                        @input="answers[step.id] = $event.target.value"
                                        @keydown.enter.prevent="next()">
                                    <div class="kbd-hint"><kbd>Enter</kbd><span>to continue ↵</span></div>
                                </div>
                            </template>

                            {{-- Number --}}
                            <template x-if="step.type === 'number'">
                                <div>
                                    <div class="flex items-end gap-3">
                                        <input type="number" class="tf-input"
                                            :placeholder="step.logic.placeholder || '0'"
                                            :min="step.logic.min" :max="step.logic.max"
                                            :value="answers[step.id] || ''"
                                            @input="answers[step.id] = $event.target.value"
                                            @keydown.enter.prevent="next()">
                                        <span x-show="step.logic.unit"
                                              class="text-white/35 text-base pb-4 shrink-0"
                                              x-text="step.logic.unit"></span>
                                    </div>
                                    <div class="kbd-hint"><kbd>Enter</kbd><span>to continue ↵</span></div>
                                </div>
                            </template>

                            {{-- Multiple choice --}}
                            <template x-if="step.type === 'multiple_choice'">
                                <div>
                                    <template x-for="(opt, oi) in step.options" :key="oi">
                                        <button class="choice-btn"
                                                :class="answers[step.id] === opt ? 'selected' : ''"
                                                @click="answers[step.id] = opt; setTimeout(() => next(), 280)">
                                            <span class="choice-key" x-text="String.fromCharCode(65 + oi)"></span>
                                            <span x-text="opt"></span>
                                        </button>
                                    </template>
                                    <div class="kbd-hint">
                                        Press
                                        <template x-for="(_, ki) in step.options" :key="ki">
                                            <kbd x-text="String.fromCharCode(65 + ki)" class="mx-0.5"></kbd>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Checkboxes --}}
                            <template x-if="step.type === 'checkboxes'">
                                <div>
                                    <template x-for="(opt, oi) in step.options" :key="oi">
                                        <button class="choice-btn"
                                                :class="(answers[step.id] || []).includes(opt) ? 'selected' : ''"
                                                @click="toggleMulti(step.id, opt)">
                                            <span class="choice-key"
                                                  x-text="(answers[step.id] || []).includes(opt) ? '✓' : String.fromCharCode(65 + oi)"></span>
                                            <span x-text="opt"></span>
                                        </button>
                                    </template>
                                    <div class="mt-5">
                                        <button @click="next()" class="tf-btn"
                                                x-text="step.logic.button_label || 'OK'"></button>
                                    </div>
                                </div>
                            </template>

                            {{-- Dropdown --}}
                            <template x-if="step.type === 'dropdown'">
                                <div>
                                    <select class="tf-input" @change="answers[step.id] = $event.target.value">
                                        <option value="" disabled :selected="!answers[step.id]"
                                                style="background:var(--bg)">Choose an option…</option>
                                        <template x-for="(opt, oi) in step.options" :key="oi">
                                            <option :value="opt" x-text="opt"
                                                    :selected="answers[step.id] === opt"
                                                    style="background:var(--bg)"></option>
                                        </template>
                                    </select>
                                    <div class="mt-5">
                                        <button @click="next()" class="tf-btn"
                                                x-text="step.logic.button_label || 'OK'"></button>
                                    </div>
                                </div>
                            </template>

                            {{-- Rating --}}
                            <template x-if="step.type === 'rating'">
                                <div class="flex gap-2.5 flex-wrap">
                                    <template x-for="n in (step.logic.scale || 5)" :key="n">
                                        <button class="rating-btn"
                                                :class="answers[step.id] >= n ? 'selected' : ''"
                                                @click="answers[step.id] = n; setTimeout(() => next(), 350)">
                                            <template x-if="(step.logic.shape || 'star') === 'star'">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            </template>
                                            <template x-if="(step.logic.shape || 'star') === 'number'">
                                                <span class="text-sm font-semibold tabular-nums" x-text="n"></span>
                                            </template>
                                        </button>
                                    </template>
                                </div>
                            </template>

                            {{-- Yes / No --}}
                            <template x-if="step.type === 'yes_no'">
                                <div class="flex gap-3 max-w-xs">
                                    <button class="yesno-btn"
                                            :class="answers[step.id] === 'Yes' ? 'selected' : ''"
                                            @click="answers[step.id] = 'Yes'; setTimeout(() => next(), 280)">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                        </svg>
                                        <span>Yes</span>
                                    </button>
                                    <button class="yesno-btn"
                                            :class="answers[step.id] === 'No' ? 'selected' : ''"
                                            @click="answers[step.id] = 'No'; setTimeout(() => next(), 280)">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                        </svg>
                                        <span>No</span>
                                    </button>
                                </div>
                            </template>

                            {{-- Date --}}
                            <template x-if="step.type === 'date'">
                                <div>
                                    <input type="date" class="tf-input"
                                        style="color-scheme:dark; max-width:16rem;"
                                        :value="answers[step.id] || ''"
                                        @input="answers[step.id] = $event.target.value"
                                        @keydown.enter.prevent="next()">
                                    <div class="kbd-hint"><kbd>Enter</kbd><span>to continue ↵</span></div>
                                </div>
                            </template>

                        </div>{{-- /mt-8 --}}
                    </div>
                </template>

            </div>
        </div>
    </template>

    {{-- ── Bottom navigation ───────────────────────────────────────────────── --}}
    <div class="nav-bar"
         x-show="steps[currentStep]?.type !== 'welcome_screen' && steps[currentStep]?.type !== 'end_screen'">
        <button class="nav-arrow" @click="prev()" :disabled="currentStep === 0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/>
            </svg>
        </button>

        <span class="text-[11px] tracking-widest uppercase font-medium select-none tf-subtle">
            Form Builder
        </span>

        <button class="tf-btn" @click="next()" :disabled="submitting">
            <span x-text="isLastInput
                ? (submitting ? 'Sending…' : (settings.submit_label || 'Submit'))
                : (steps[currentStep]?.logic?.button_label || 'OK')"></span>
            <svg x-show="!isLastInput && !submitting"
                 class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/>
            </svg>
        </button>
    </div>

</div>

<script>
function formFlow(cfg) {
    return {
        steps: cfg.steps,
        settings: cfg.settings || {},
        submitUrl: cfg.submitUrl,
        thanksUrl: cfg.thanksUrl,
        currentStep: 0,
        prevStep: -1,
        direction: 1,
        answers: {},
        submitting: false,
        submitted: false,

        get nonScreenSteps() {
            return this.steps.filter(s => !['welcome_screen','end_screen','statement'].includes(s.type));
        },
        get isLastInput() {
            const remaining = this.steps.slice(this.currentStep + 1).filter(s => s.type !== 'end_screen');
            return remaining.length === 0;
        },
        get progress() {
            return Math.round((this.currentStep / Math.max(this.steps.length - 1, 1)) * 100);
        },

        questionNumber(i) {
            let n = 0;
            for (let j = 0; j <= i; j++) {
                if (!['welcome_screen','end_screen','statement'].includes(this.steps[j].type)) n++;
            }
            return n;
        },

        slideClass(i) {
            if (i === this.currentStep) return 'slide-active';
            if (i < this.currentStep)  return 'slide-exit';
            return 'slide-enter';
        },

        next() {
            if (this.submitting || this.submitted) return;
            const nextIdx = this.currentStep + 1;
            const isEndNext = nextIdx < this.steps.length && this.steps[nextIdx].type === 'end_screen';
            if (isEndNext || nextIdx >= this.steps.length) {
                this.submitForm();
                return;
            }
            if (nextIdx < this.steps.length) {
                this.direction = 1;
                this.prevStep = this.currentStep;
                this.currentStep = nextIdx;
            }
        },

        prev() {
            if (this.currentStep > 0) {
                this.direction = -1;
                this.prevStep = this.currentStep;
                this.currentStep--;
            }
        },

        toggleMulti(stepId, opt) {
            if (!Array.isArray(this.answers[stepId])) this.answers[stepId] = [];
            const idx = this.answers[stepId].indexOf(opt);
            if (idx === -1) this.answers[stepId].push(opt);
            else this.answers[stepId].splice(idx, 1);
        },

        handleKey(e) {
            if (this.submitted) return;
            const step = this.steps[this.currentStep];
            if (!step) return;
            if (e.key === 'Enter' && step.type !== 'long_text') {
                e.preventDefault();
                this.next();
                return;
            }
            if (e.key === 'Enter' && step.type === 'long_text' && !e.shiftKey) {
                e.preventDefault();
                this.next();
                return;
            }
            if (step.type === 'multiple_choice' && step.options) {
                const ki = e.key.toUpperCase().charCodeAt(0) - 65;
                if (ki >= 0 && ki < step.options.length) {
                    this.answers[step.id] = step.options[ki];
                    setTimeout(() => this.next(), 280);
                }
            }
        },

        async submitForm() {
            this.submitting = true;
            try {
                const res = await fetch(this.submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ answers: this.answers }),
                });
                const body = await res.json();
                if (body.ok) {
                    this.submitted = true;
                    if (body.redirect) {
                        window.location.href = body.redirect;
                    } else {
                        const endIdx = this.steps.findIndex(s => s.type === 'end_screen');
                        if (endIdx >= 0) {
                            this.prevStep = this.currentStep;
                            this.currentStep = endIdx;
                        } else {
                            window.location.href = this.thanksUrl;
                        }
                    }
                } else {
                    alert('Submission failed.');
                }
            } catch {
                alert('Network error. Please try again.');
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
</body>
</html>
