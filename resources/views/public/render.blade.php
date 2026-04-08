<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $form->title }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family={{ str_replace(' ', '+', $theme['font_family']) }}:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-primary: {{ $theme['primary_color'] }};
            --color-secondary: {{ $theme['secondary_color'] }};
            --color-bg: {{ $theme['background_color'] }};
            --font-family: '{{ $theme['font_family'] }}', system-ui, sans-serif;
        }
        html, body { background: var(--color-bg); font-family: var(--font-family); color: #fff; }
        .btn-primary { background: var(--color-primary); color: #fff; }
        .btn-primary:hover { filter: brightness(1.1); }
        .accent { color: var(--color-secondary); }
        input.tf-input, textarea.tf-input {
            background: transparent;
            color: #fff;
            border: none;
            border-bottom: 2px solid rgba(255,255,255,0.3);
            outline: none;
            width: 100%;
            font-size: 1.875rem;
            padding: 0.5rem 0;
        }
        input.tf-input:focus, textarea.tf-input:focus {
            border-bottom-color: var(--color-primary);
        }
        .tf-choice {
            display: block;
            width: 100%;
            text-align: left;
            padding: 1rem 1.25rem;
            border: 2px solid rgba(255,255,255,0.25);
            border-radius: 0.5rem;
            background: rgba(255,255,255,0.05);
            color: #fff;
            margin-bottom: 0.75rem;
            transition: all 0.15s ease;
            font-size: 1.125rem;
        }
        .tf-choice:hover { border-color: var(--color-primary); background: rgba(255,255,255,0.1); }
        .tf-choice.selected { border-color: var(--color-primary); background: var(--color-primary); }
    </style>
</head>
<body class="h-screen overflow-hidden">

<div
    x-data="formFlow({{ Js::from([
        'steps' => $form->steps->map(fn ($s) => [
            'id' => $s->id,
            'type' => $s->type,
            'question' => $s->question,
            'options' => $s->options ?? [],
        ])->values(),
        'submitUrl' => route('public.form.submit', $form->slug),
    ]) }})"
    class="h-screen flex flex-col"
>
    <!-- Progress bar -->
    <div class="h-1 bg-white/10">
        <div class="h-1 transition-all duration-300" :style="`width: ${progress}%; background: var(--color-primary)`"></div>
    </div>

    <main class="flex-1 flex items-center justify-center px-6">
        <div class="w-full max-w-2xl">
            <template x-for="(step, index) in steps" :key="step.id">
                <div x-show="currentStep === index" x-transition.opacity.duration.300ms>
                    <div class="text-sm uppercase tracking-wider text-white/50 mb-3">
                        Question <span x-text="index + 1"></span> of <span x-text="steps.length"></span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-8" x-text="step.question"></h1>

                    <!-- Short answer -->
                    <template x-if="step.type === 'text'">
                        <input
                            type="text"
                            class="tf-input"
                            :value="answers[step.id] || ''"
                            @input="answers[step.id] = $event.target.value"
                            @keydown.enter.prevent="next()"
                            placeholder="Type your answer..."
                        >
                    </template>

                    <!-- Long answer -->
                    <template x-if="step.type === 'textarea'">
                        <textarea
                            class="tf-input"
                            rows="3"
                            :value="answers[step.id] || ''"
                            @input="answers[step.id] = $event.target.value"
                            placeholder="Type your answer..."
                        ></textarea>
                    </template>

                    <!-- Single choice -->
                    <template x-if="step.type === 'mcq'">
                        <div>
                            <template x-for="opt in step.options" :key="opt">
                                <button
                                    type="button"
                                    class="tf-choice"
                                    :class="answers[step.id] === opt ? 'selected' : ''"
                                    @click="answers[step.id] = opt; setTimeout(() => next(), 250)"
                                    x-text="opt"
                                ></button>
                            </template>
                        </div>
                    </template>

                    <!-- Multiple choice -->
                    <template x-if="step.type === 'multi'">
                        <div>
                            <template x-for="opt in step.options" :key="opt">
                                <button
                                    type="button"
                                    class="tf-choice"
                                    :class="(answers[step.id] || []).includes(opt) ? 'selected' : ''"
                                    @click="toggleMulti(step.id, opt)"
                                    x-text="opt"
                                ></button>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </main>

    <footer class="px-6 py-4 flex items-center justify-between border-t border-white/10">
        <button
            type="button"
            @click="prev()"
            :disabled="currentStep === 0"
            class="px-4 py-2 text-sm text-white/70 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed"
        >
            &larr; Previous
        </button>
        <div class="text-xs text-white/40">Powered by Form Builder</div>
        <button
            type="button"
            @click="next()"
            :disabled="submitting"
            class="btn-primary px-6 py-2 rounded-md text-sm font-medium disabled:opacity-50"
            x-text="isLast ? (submitting ? 'Submitting...' : 'Submit') : 'Next'"
        ></button>
    </footer>
</div>

<script>
    function formFlow(config) {
        return {
            steps: config.steps,
            submitUrl: config.submitUrl,
            currentStep: 0,
            answers: {},
            submitting: false,
            get isLast() { return this.currentStep === this.steps.length - 1; },
            get progress() { return Math.round(((this.currentStep + 1) / this.steps.length) * 100); },
            next() {
                if (this.isLast) {
                    this.submit();
                    return;
                }
                this.currentStep++;
            },
            prev() {
                if (this.currentStep > 0) this.currentStep--;
            },
            toggleMulti(stepId, opt) {
                if (!Array.isArray(this.answers[stepId])) this.answers[stepId] = [];
                const i = this.answers[stepId].indexOf(opt);
                if (i === -1) this.answers[stepId].push(opt);
                else this.answers[stepId].splice(i, 1);
            },
            async submit() {
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
                    if (body.ok && body.redirect) {
                        window.location.href = body.redirect;
                    } else {
                        alert('Submission failed.');
                    }
                } catch (e) {
                    alert('Network error.');
                } finally {
                    this.submitting = false;
                }
            },
        };
    }
</script>

</body>
</html>
