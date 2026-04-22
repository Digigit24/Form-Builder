<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $form->title }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family={{ urlencode($theme['font_family']) }}:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: {{ $theme['primary_color'] }};
            --secondary: {{ $theme['secondary_color'] }};
            --bg: {{ $theme['background_color'] }};
            --font: '{{ $theme['font_family'] }}', system-ui, sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin:0; height:100%; background:var(--bg); font-family:var(--font); color:#fff; overflow:hidden; }
        .slide { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:2rem; transition: transform 0.5s cubic-bezier(.4,0,.2,1), opacity 0.5s cubic-bezier(.4,0,.2,1); }
        .slide-enter { transform:translateY(60px); opacity:0; }
        .slide-active { transform:translateY(0); opacity:1; }
        .slide-exit { transform:translateY(-60px); opacity:0; }
        .tf-input { background:transparent; border:none; border-bottom:2px solid rgba(255,255,255,.25); color:#fff; font-size:1.5rem; width:100%; padding:.75rem 0; outline:none; font-family:var(--font); }
        .tf-input:focus { border-bottom-color:var(--primary); }
        .tf-input::placeholder { color:rgba(255,255,255,.3); }
        .tf-btn { background:var(--primary); color:#fff; border:none; padding:.75rem 2rem; border-radius:.5rem; font-size:.875rem; font-weight:600; cursor:pointer; font-family:var(--font); transition:filter .15s; }
        .tf-btn:hover { filter:brightness(1.15); }
        .tf-btn:disabled { opacity:.5; cursor:not-allowed; }
        .choice-btn { display:flex; align-items:center; gap:.75rem; width:100%; text-align:left; padding:.875rem 1.25rem; border:2px solid rgba(255,255,255,.15); border-radius:.5rem; background:rgba(255,255,255,.03); color:#fff; cursor:pointer; font-size:1rem; font-family:var(--font); transition:all .15s; margin-bottom:.5rem; }
        .choice-btn:hover { border-color:var(--primary); background:rgba(255,255,255,.06); }
        .choice-btn.selected { border-color:var(--primary); background:var(--primary); }
        .choice-key { width:1.5rem; height:1.5rem; border-radius:.25rem; border:1px solid rgba(255,255,255,.25); display:flex; align-items:center; justify-content:center; font-size:.65rem; font-weight:700; flex-shrink:0; }
        .choice-btn.selected .choice-key { background:#fff; color:var(--primary); border-color:transparent; }
        .rating-btn { width:3rem; height:3rem; border-radius:.5rem; border:2px solid rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center; font-size:1.25rem; cursor:pointer; transition:all .15s; background:rgba(255,255,255,.03); }
        .rating-btn:hover, .rating-btn.selected { border-color:var(--primary); background:var(--primary); }
        .yesno-btn { flex:1; padding:1rem; border-radius:.5rem; border:2px solid rgba(255,255,255,.15); text-align:center; font-weight:600; font-size:1.125rem; cursor:pointer; transition:all .15s; background:rgba(255,255,255,.03); }
        .yesno-btn:hover, .yesno-btn.selected { border-color:var(--primary); background:var(--primary); }
        .enter-hint { display:inline-flex; align-items:center; gap:.25rem; font-size:.75rem; color:rgba(255,255,255,.35); margin-top:1rem; }
        .enter-hint kbd { background:rgba(255,255,255,.1); padding:.15rem .5rem; border-radius:.25rem; font-size:.65rem; }
        .progress { position:fixed; top:0; left:0; height:3px; background:var(--primary); transition:width .4s ease; z-index:50; }
    </style>
</head>
<body>

<div x-data="formFlow({{ Js::from([
    'steps' => $form->steps->map(fn ($s) => [
        'id' => $s->id,
        'type' => $s->type,
        'question' => $s->question,
        'options' => $s->options ?? [],
        'logic' => $s->logic ?? [],
    ])->values(),
    'submitUrl' => route('public.form.submit', $form->slug),
    'thanksUrl' => route('public.form.thanks', $form->slug),
    'settings' => $form->settings ?? [],
]) }})" @keydown.window="handleKey($event)">

    {{-- Progress --}}
    <template x-if="(settings.progress_bar || 'bar') !== 'hidden'">
        <div>
            <template x-if="(settings.progress_bar || 'bar') === 'bar'">
                <div class="progress" :style="`width: ${progress}%`"></div>
            </template>
            <template x-if="(settings.progress_bar || 'bar') === 'percentage'">
                <div class="fixed top-3 right-6 text-xs text-white/40 z-50" x-text="Math.round(progress) + '%'"></div>
            </template>
            <template x-if="(settings.progress_bar || 'bar') === 'dots'">
                <div class="fixed top-3 left-1/2 -translate-x-1/2 flex gap-1.5 z-50">
                    <template x-for="(_, di) in steps" :key="di">
                        <div class="w-2 h-2 rounded-full transition-colors" :class="di <= currentStep ? 'bg-white/80' : 'bg-white/15'"></div>
                    </template>
                </div>
            </template>
        </div>
    </template>

    {{-- Slides --}}
    <div class="relative h-screen w-full">
        <template x-for="(step, i) in steps" :key="step.id || i">
            <div class="slide" :class="slideClass(i)">
                <div class="w-full max-w-xl">
                    {{-- Welcome screen --}}
                    <template x-if="step.type === 'welcome_screen'">
                        <div class="text-center">
                            <h1 class="text-4xl md:text-5xl font-bold mb-4" x-text="step.question"></h1>
                            <p class="text-lg text-white/60 mb-10" x-text="step.logic.subtitle || step.logic.description || ''"></p>
                            <button @click="next()" class="tf-btn text-lg px-10 py-4" x-text="step.logic.button_label || 'Start'"></button>
                        </div>
                    </template>

                    {{-- End screen --}}
                    <template x-if="step.type === 'end_screen'">
                        <div class="text-center">
                            <div class="text-6xl mb-6" style="color:var(--primary)">&#10003;</div>
                            <h1 class="text-4xl font-bold mb-3" x-text="step.question || 'Thank you!'"></h1>
                            <p class="text-lg text-white/60" x-text="step.logic.subtitle || step.logic.description || ''"></p>
                        </div>
                    </template>

                    {{-- Statement --}}
                    <template x-if="step.type === 'statement'">
                        <div>
                            <h1 class="text-3xl font-bold mb-3" x-text="step.question"></h1>
                            <p class="text-white/60 mb-8" x-text="step.logic.description || ''"></p>
                            <button @click="next()" class="tf-btn" x-text="step.logic.button_label || 'Continue'"></button>
                        </div>
                    </template>

                    {{-- Input questions --}}
                    <template x-if="!['welcome_screen','end_screen','statement'].includes(step.type)">
                        <div>
                            <div class="flex items-baseline gap-3 mb-2">
                                <span class="text-sm text-white/40" x-text="questionNumber(i) + '→'"></span>
                                <h1 class="text-2xl md:text-3xl font-bold" x-text="step.question"></h1>
                                <span x-show="step.logic.required" class="text-red-400 text-sm">*</span>
                            </div>
                            <p x-show="step.logic.description" class="text-white/50 mb-6 pl-10" x-text="step.logic.description"></p>
                            <div class="pl-10 mt-6">
                                {{-- Short text --}}
                                <template x-if="step.type === 'short_text'">
                                    <div>
                                        <input type="text" class="tf-input" :placeholder="step.logic.placeholder || 'Type your answer here...'" :value="answers[step.id] || ''" @input="answers[step.id] = $event.target.value" @keydown.enter.prevent="next()">
                                        <div class="enter-hint"><kbd>Enter</kbd> <span>↵</span></div>
                                    </div>
                                </template>

                                {{-- Long text --}}
                                <template x-if="step.type === 'long_text'">
                                    <div>
                                        <textarea class="tf-input" rows="3" :placeholder="step.logic.placeholder || 'Type your answer here...'" :value="answers[step.id] || ''" @input="answers[step.id] = $event.target.value" style="resize:none;"></textarea>
                                        <div class="enter-hint"><kbd>Shift</kbd>+<kbd>Enter</kbd> for new line</div>
                                    </div>
                                </template>

                                {{-- Email --}}
                                <template x-if="step.type === 'email'">
                                    <div>
                                        <input type="email" class="tf-input" :placeholder="step.logic.placeholder || 'name@example.com'" :value="answers[step.id] || ''" @input="answers[step.id] = $event.target.value" @keydown.enter.prevent="next()">
                                        <div class="enter-hint"><kbd>Enter</kbd> <span>↵</span></div>
                                    </div>
                                </template>

                                {{-- Phone --}}
                                <template x-if="step.type === 'phone'">
                                    <div>
                                        <input type="tel" class="tf-input" :placeholder="step.logic.placeholder || '+1 (555) 000-0000'" :value="answers[step.id] || ''" @input="answers[step.id] = $event.target.value" @keydown.enter.prevent="next()">
                                        <div class="enter-hint"><kbd>Enter</kbd> <span>↵</span></div>
                                    </div>
                                </template>

                                {{-- Number --}}
                                <template x-if="step.type === 'number'">
                                    <div>
                                        <div class="flex items-end gap-2">
                                            <input type="number" class="tf-input" :placeholder="step.logic.placeholder || '0'" :min="step.logic.min" :max="step.logic.max" :value="answers[step.id] || ''" @input="answers[step.id] = $event.target.value" @keydown.enter.prevent="next()">
                                            <span x-show="step.logic.unit" class="text-white/40 text-sm pb-3 pl-1" x-text="step.logic.unit"></span>
                                        </div>
                                        <div class="enter-hint"><kbd>Enter</kbd> <span>↵</span></div>
                                    </div>
                                </template>

                                {{-- Multiple choice --}}
                                <template x-if="step.type === 'multiple_choice'">
                                    <div>
                                        <template x-for="(opt, oi) in step.options" :key="oi">
                                            <button class="choice-btn" :class="answers[step.id] === opt ? 'selected' : ''" @click="answers[step.id] = opt; setTimeout(() => next(), 300)">
                                                <span class="choice-key" x-text="String.fromCharCode(65 + oi)"></span>
                                                <span x-text="opt"></span>
                                            </button>
                                        </template>
                                        <div class="enter-hint">or press <template x-for="(_, ki) in step.options" :key="ki"><kbd x-text="String.fromCharCode(65 + ki)" class="mx-0.5"></kbd></template></div>
                                    </div>
                                </template>

                                {{-- Checkboxes --}}
                                <template x-if="step.type === 'checkboxes'">
                                    <div>
                                        <template x-for="(opt, oi) in step.options" :key="oi">
                                            <button class="choice-btn" :class="(answers[step.id] || []).includes(opt) ? 'selected' : ''" @click="toggleMulti(step.id, opt)">
                                                <span class="choice-key" x-text="String.fromCharCode(65 + oi)"></span>
                                                <span x-text="opt"></span>
                                            </button>
                                        </template>
                                        <div class="mt-4"><button @click="next()" class="tf-btn" x-text="step.logic.button_label || 'OK'"></button></div>
                                    </div>
                                </template>

                                {{-- Dropdown --}}
                                <template x-if="step.type === 'dropdown'">
                                    <div>
                                        <select class="tf-input" style="font-size:1.25rem;border-bottom:2px solid rgba(255,255,255,.25);background:transparent;" @change="answers[step.id] = $event.target.value">
                                            <option value="" disabled :selected="!answers[step.id]" style="background:var(--bg)">Select an option...</option>
                                            <template x-for="(opt, oi) in step.options" :key="oi">
                                                <option :value="opt" x-text="opt" :selected="answers[step.id] === opt" style="background:var(--bg)"></option>
                                            </template>
                                        </select>
                                        <div class="mt-4"><button @click="next()" class="tf-btn" x-text="step.logic.button_label || 'OK'"></button></div>
                                    </div>
                                </template>

                                {{-- Rating --}}
                                <template x-if="step.type === 'rating'">
                                    <div>
                                        <div class="flex gap-2 flex-wrap">
                                            <template x-for="n in (step.logic.scale || 5)" :key="n">
                                                <button class="rating-btn" :class="answers[step.id] >= n ? 'selected' : ''" @click="answers[step.id] = n; setTimeout(() => next(), 300)">
                                                    <template x-if="(step.logic.shape || 'star') === 'star'"><span>&#9733;</span></template>
                                                    <template x-if="(step.logic.shape || 'star') === 'number'"><span x-text="n"></span></template>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Yes/No --}}
                                <template x-if="step.type === 'yes_no'">
                                    <div class="flex gap-3">
                                        <button class="yesno-btn" :class="answers[step.id] === 'Yes' ? 'selected' : ''" @click="answers[step.id] = 'Yes'; setTimeout(() => next(), 300)">
                                            <span class="block text-lg">&#10003;</span> Yes
                                        </button>
                                        <button class="yesno-btn" :class="answers[step.id] === 'No' ? 'selected' : ''" @click="answers[step.id] = 'No'; setTimeout(() => next(), 300)">
                                            <span class="block text-lg">&#10007;</span> No
                                        </button>
                                    </div>
                                </template>

                                {{-- Date --}}
                                <template x-if="step.type === 'date'">
                                    <div>
                                        <input type="date" class="tf-input" style="color-scheme:dark;" :value="answers[step.id] || ''" @input="answers[step.id] = $event.target.value" @keydown.enter.prevent="next()">
                                        <div class="enter-hint"><kbd>Enter</kbd> <span>↵</span></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- Navigation --}}
    <div class="fixed bottom-0 left-0 right-0 px-6 py-4 flex items-center justify-between z-40"
         x-show="steps[currentStep]?.type !== 'welcome_screen' && steps[currentStep]?.type !== 'end_screen'">
        <button @click="prev()" :disabled="currentStep === 0" class="text-white/40 hover:text-white disabled:opacity-20 disabled:cursor-not-allowed transition p-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
        </button>
        <div class="text-xs text-white/20">Powered by Form Builder</div>
        <button @click="next()" :disabled="submitting" class="tf-btn text-sm">
            <span x-text="isLastInput ? (submitting ? 'Sending...' : (settings.submit_label || 'Submit')) : (steps[currentStep]?.logic?.button_label || 'OK ✓')"></span>
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
            const remaining = this.steps.slice(this.currentStep + 1).filter(s => !['end_screen'].includes(s.type));
            return remaining.length === 0 || (remaining.length === 0 && this.steps[this.currentStep]?.type !== 'end_screen');
        },
        get progress() {
            return Math.round(((this.currentStep) / (this.steps.length - 1)) * 100);
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
            if (i < this.currentStep) return 'slide-exit';
            return 'slide-enter';
        },

        next() {
            if (this.submitting || this.submitted) return;

            const step = this.steps[this.currentStep];
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
                    setTimeout(() => this.next(), 300);
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
