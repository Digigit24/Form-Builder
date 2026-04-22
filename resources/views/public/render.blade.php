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
    <style>
        :root {
            --primary: {{ $theme['primary_color'] }};
            --secondary: {{ $theme['secondary_color'] }};
            --bg: {{ $theme['background_color'] }};
            --font: '{{ $theme['font_family'] }}', system-ui, sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html, body {
            margin: 0; height: 100%;
            background: var(--bg); font-family: var(--font);
            color: #fff; overflow: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

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
            border-bottom: 2px solid rgba(255,255,255,.15);
            color: #fff;
            font-size: clamp(1.1875rem, 3vw, 1.5rem);
            width: 100%; padding: .875rem 0; outline: none;
            font-family: var(--font); font-weight: 400; line-height: 1.5;
            transition: border-color .2s, box-shadow .2s;
        }
        .tf-input:focus {
            border-bottom-color: var(--primary);
            box-shadow: 0 1px 0 0 var(--primary);
        }
        .tf-input::placeholder { color: rgba(255,255,255,.22); }
        textarea.tf-input { resize: none; }
        select.tf-input { -webkit-appearance: none; cursor: pointer; }

        /* OK / submit button */
        .tf-btn {
            display: inline-flex; align-items: center; gap: .5rem;
            background: var(--primary); color: #fff; border: none;
            padding: .75rem 1.625rem;
            border-radius: .5rem; font-size: .875rem; font-weight: 600;
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
            background: var(--primary); color: #fff; border: none;
            padding: 1rem 2.375rem; border-radius: .625rem;
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
            border: 1.5px solid rgba(255,255,255,.1);
            border-radius: .625rem;
            background: rgba(255,255,255,.025);
            color: rgba(255,255,255,.82);
            cursor: pointer; font-size: .9375rem; font-family: var(--font);
            transition: border-color .15s, background .15s, transform .15s;
            margin-bottom: .4375rem;
        }
        .choice-btn:hover {
            border-color: rgba(255,255,255,.22);
            background: rgba(255,255,255,.055);
            transform: translateX(3px);
        }
        .choice-btn.selected {
            border-color: var(--primary);
            background: rgba(99,102,241,.14);
            color: #fff;
        }
        .choice-key {
            width: 1.625rem; height: 1.625rem;
            border-radius: .3125rem; border: 1.5px solid rgba(255,255,255,.18);
            display: flex; align-items: center; justify-content: center;
            font-size: .625rem; font-weight: 700; flex-shrink: 0;
            letter-spacing: .05em;
            transition: background .15s, border-color .15s, color .15s;
        }
        .choice-btn.selected .choice-key {
            background: var(--primary); border-color: var(--primary); color: #fff;
        }

        /* Rating stars / numbers */
        .rating-btn {
            width: 3.125rem; height: 3.125rem;
            border-radius: .625rem;
            border: 1.5px solid rgba(255,255,255,.1);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: border-color .18s, background .18s, transform .18s, color .18s;
            background: rgba(255,255,255,.025);
            color: rgba(255,255,255,.3);
        }
        .rating-btn:hover {
            border-color: rgba(255,255,255,.22);
            background: rgba(255,255,255,.065);
            transform: translateY(-3px) scale(1.09);
            color: rgba(255,255,255,.8);
        }
        .rating-btn.selected {
            border-color: var(--primary);
            background: rgba(99,102,241,.2);
            color: var(--primary);
            transform: translateY(-1px);
        }

        /* Yes / No */
        .yesno-btn {
            flex: 1; padding: 1.125rem 1rem;
            border-radius: .75rem; border: 1.5px solid rgba(255,255,255,.1);
            text-align: center; font-weight: 600; font-size: 1rem;
            cursor: pointer; font-family: var(--font);
            transition: border-color .18s, background .18s, transform .18s, color .18s;
            background: rgba(255,255,255,.025);
            color: rgba(255,255,255,.75);
            display: flex; flex-direction: column; align-items: center; gap: .375rem;
        }
        .yesno-btn:hover {
            border-color: rgba(255,255,255,.22);
            background: rgba(255,255,255,.065);
            transform: translateY(-2px); color: #fff;
        }
        .yesno-btn.selected {
            border-color: var(--primary);
            background: rgba(99,102,241,.15); color: #fff;
        }

        /* Keyboard hint row */
        .kbd-hint {
            display: inline-flex; align-items: center; gap: .3rem;
            font-size: .6875rem; color: rgba(255,255,255,.27);
            margin-top: .875rem; letter-spacing: .025em;
        }
        kbd {
            background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
            padding: .125rem .375rem; border-radius: .25rem;
            font-size: .625rem; font-family: inherit; font-weight: 600;
        }

        /* Question number label */
        .q-label {
            display: flex; align-items: center; gap: .3rem;
            font-size: .75rem; color: rgba(255,255,255,.35);
            margin-bottom: .875rem; font-weight: 500; letter-spacing: .04em;
        }
        .q-arrow { color: var(--primary); font-style: normal; }

        /* Bottom navigation bar */
        .nav-bar {
            position: fixed; bottom: 0; left: 0; right: 0;
            padding: 1.125rem 1.5rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            background: linear-gradient(to top, rgba(0,0,0,.5) 0%, transparent 100%);
            z-index: 50;
        }
        .nav-arrow {
            width: 2.25rem; height: 2.25rem; border-radius: .5rem;
            border: 1px solid rgba(255,255,255,.1);
            background: rgba(255,255,255,.04);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: rgba(255,255,255,.4);
            transition: background .15s, color .15s, border-color .15s;
        }
        .nav-arrow:hover:not(:disabled) {
            background: rgba(255,255,255,.09); color: #fff;
            border-color: rgba(255,255,255,.2);
        }
        .nav-arrow:disabled { opacity: .22; cursor: not-allowed; }

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
    class="relative h-screen w-full"
>

    {{-- ── Progress indicator ─────────────────────────────────────────────── --}}
    <template x-if="(settings.progress_bar || 'bar') !== 'hidden'">
        <div>
            <template x-if="(settings.progress_bar || 'bar') === 'bar'">
                <div class="progress" :style="`width: ${progress}%`"></div>
            </template>
            <template x-if="(settings.progress_bar || 'bar') === 'percentage'">
                <div class="fixed top-4 right-6 text-xs text-white/30 z-50 font-semibold tracking-wide tabular-nums"
                     x-text="Math.round(progress) + '%'"></div>
            </template>
            <template x-if="(settings.progress_bar || 'bar') === 'dots'">
                <div class="fixed top-5 left-1/2 -translate-x-1/2 flex gap-1.5 z-50 items-center">
                    <template x-for="(_, di) in steps" :key="di">
                        <div class="h-1.5 rounded-full transition-all duration-300"
                             :class="di < currentStep ? 'w-4 bg-white/55' : di === currentStep ? 'w-6 bg-white' : 'w-1.5 bg-white/14'"></div>
                    </template>
                </div>
            </template>
        </div>
    </template>

    {{-- ── Slides ──────────────────────────────────────────────────────────── --}}
    <template x-for="(step, i) in steps" :key="step.id || i">
        <div class="slide" :class="slideClass(i)">
            <div class="w-full max-w-2xl mx-auto">

                {{-- Welcome screen --}}
                <template x-if="step.type === 'welcome_screen'">
                    <div class="text-center py-6">
                        <h1 class="text-5xl md:text-6xl font-bold mb-5 leading-tight tracking-tight"
                            x-text="step.question"></h1>
                        <p class="text-xl text-white/45 mb-12 font-light max-w-lg mx-auto leading-relaxed"
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
                                        fill="rgba(99,102,241,.08)"/>
                                <path class="check-path"
                                      d="M24 41l12 11 20-24"
                                      stroke="var(--primary)" stroke-width="3"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h1 class="text-4xl md:text-5xl font-bold mb-4 tracking-tight"
                            x-text="step.question || 'Thank you!'"></h1>
                        <p class="text-lg text-white/45 font-light"
                           x-text="step.logic.subtitle || step.logic.description || ''"></p>
                    </div>
                </template>

                {{-- Statement --}}
                <template x-if="step.type === 'statement'">
                    <div>
                        <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-snug tracking-tight"
                            x-text="step.question"></h1>
                        <p class="text-lg text-white/45 mb-10 font-light leading-relaxed"
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
                           class="text-white/40 text-base mt-3 font-light leading-relaxed"
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

        <span class="text-[11px] text-white/18 tracking-widest uppercase font-medium select-none">
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
