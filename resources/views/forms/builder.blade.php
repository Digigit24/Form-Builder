<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                </a>
                <h2 class="text-lg font-semibold text-white truncate">{{ $form->title }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('forms.responses', $form) }}" class="text-xs text-gray-400 hover:text-white">Responses</a>
                @if ($form->is_published)
                    <a href="{{ route('public.form.show', $form->slug) }}" target="_blank" class="text-xs text-gray-400 hover:text-white">Open link</a>
                @endif
                <form method="POST" action="{{ route('forms.publish', $form) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-xs rounded-lg font-medium transition {{ $form->is_published ? 'bg-green-500/10 text-green-400 border border-green-500/20 hover:bg-green-500/20' : 'bg-gray-500/10 text-gray-400 border border-gray-500/20 hover:bg-gray-500/20' }}">
                        {{ $form->is_published ? 'Published' : 'Draft' }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div
        x-data="formBuilder({{ Js::from([
            'formId' => $form->id,
            'title' => $form->title,
            'description' => $form->description ?? '',
            'settings' => $form->settings ?? [
                'progress_bar' => 'bar',
                'submit_label' => 'Submit',
                'redirect_url' => '',
                'notify_email' => '',
                'close_form' => false,
                'response_limit' => null,
            ],
            'steps' => $form->steps->map(fn ($s) => [
                'type' => $s->type,
                'question' => $s->question,
                'options' => $s->options ?? [],
                'logic' => $s->logic ?? [],
            ])->values(),
            'updateUrl' => route('forms.update', $form),
        ]) }})"
        class="-m-6 flex h-[calc(100vh-73px)]"
    >
        {{-- LEFT: Question list --}}
        <div class="w-[280px] bg-[#18181f] border-r border-white/5 flex flex-col overflow-hidden">
            {{-- Tabs --}}
            <div class="flex border-b border-white/5">
                <button @click="tab = 'questions'" :class="tab === 'questions' ? 'text-white border-indigo-500' : 'text-gray-500 border-transparent'" class="flex-1 px-4 py-3 text-xs font-medium border-b-2 transition">Questions</button>
                <button @click="tab = 'design'" :class="tab === 'design' ? 'text-white border-indigo-500' : 'text-gray-500 border-transparent'" class="flex-1 px-4 py-3 text-xs font-medium border-b-2 transition">Design</button>
                <button @click="tab = 'settings'" :class="tab === 'settings' ? 'text-white border-indigo-500' : 'text-gray-500 border-transparent'" class="flex-1 px-4 py-3 text-xs font-medium border-b-2 transition">Settings</button>
            </div>

            {{-- Questions tab --}}
            <div x-show="tab === 'questions'" class="flex-1 overflow-y-auto p-3 space-y-1">
                <template x-for="(step, i) in steps" :key="i">
                    <div
                        @click="activeStep = i"
                        :class="activeStep === i ? 'bg-indigo-600/20 border-indigo-500/30' : 'bg-white/[0.02] border-white/5 hover:bg-white/5'"
                        class="p-3 rounded-lg border cursor-pointer transition group"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-gray-500 font-mono w-4 shrink-0" x-text="i + 1"></span>
                            <span class="text-[10px] uppercase tracking-wider font-medium px-1.5 py-0.5 rounded"
                                  :class="typeColor(step.type)"
                                  x-text="typeLabel(step.type)"></span>
                            <div class="ml-auto flex gap-1 opacity-0 group-hover:opacity-100">
                                <button @click.stop="moveStep(i, -1)" class="text-gray-500 hover:text-white p-0.5"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg></button>
                                <button @click.stop="moveStep(i, 1)" class="text-gray-500 hover:text-white p-0.5"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg></button>
                                <button @click.stop="removeStep(i)" x-show="step.type !== 'welcome_screen' && step.type !== 'end_screen'" class="text-gray-500 hover:text-red-400 p-0.5"><svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-300 truncate pl-6" x-text="step.question || 'Untitled'"></p>
                    </div>
                </template>

                {{-- Add question --}}
                <div class="relative pt-2" x-data="{ showPicker: false }">
                    <button @click="showPicker = !showPicker" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 border border-dashed border-white/10 rounded-lg text-xs text-gray-400 hover:text-white hover:border-white/20 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Add question
                    </button>
                    <div x-show="showPicker" @click.away="showPicker = false" x-transition class="absolute bottom-full left-0 right-0 mb-1 bg-[#22222a] rounded-lg border border-white/10 shadow-xl p-2 z-20 max-h-[300px] overflow-y-auto">
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider px-2 py-1">Input</div>
                        <template x-for="t in inputTypes" :key="t.value">
                            <button @click="addStep(t.value); showPicker = false" class="w-full text-left px-3 py-2 text-xs text-gray-300 hover:bg-white/5 rounded flex items-center gap-2">
                                <span x-text="t.icon" class="text-sm w-5 text-center"></span>
                                <span x-text="t.label"></span>
                            </button>
                        </template>
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider px-2 py-1 mt-1">Choice</div>
                        <template x-for="t in choiceTypes" :key="t.value">
                            <button @click="addStep(t.value); showPicker = false" class="w-full text-left px-3 py-2 text-xs text-gray-300 hover:bg-white/5 rounded flex items-center gap-2">
                                <span x-text="t.icon" class="text-sm w-5 text-center"></span>
                                <span x-text="t.label"></span>
                            </button>
                        </template>
                        <div class="text-[10px] text-gray-500 uppercase tracking-wider px-2 py-1 mt-1">Other</div>
                        <template x-for="t in otherTypes" :key="t.value">
                            <button @click="addStep(t.value); showPicker = false" class="w-full text-left px-3 py-2 text-xs text-gray-300 hover:bg-white/5 rounded flex items-center gap-2">
                                <span x-text="t.icon" class="text-sm w-5 text-center"></span>
                                <span x-text="t.label"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Design tab --}}
            <div x-show="tab === 'design'" class="flex-1 overflow-y-auto p-4 space-y-4">
                <div>
                    <label class="text-xs text-gray-400 block mb-1">Progress bar</label>
                    <select x-model="settings.progress_bar" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
                        <option value="bar">Bar</option>
                        <option value="percentage">Percentage</option>
                        <option value="dots">Dots</option>
                        <option value="hidden">Hidden</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-400 block mb-1">Submit button label</label>
                    <input type="text" x-model="settings.submit_label" placeholder="Submit" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
                </div>
            </div>

            {{-- Settings tab --}}
            <div x-show="tab === 'settings'" class="flex-1 overflow-y-auto p-4 space-y-4">
                <div>
                    <label class="text-xs text-gray-400 block mb-1">Form title</label>
                    <input type="text" x-model="title" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
                </div>
                <div>
                    <label class="text-xs text-gray-400 block mb-1">Description</label>
                    <textarea x-model="description" rows="2" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0"></textarea>
                </div>
                <div>
                    <label class="text-xs text-gray-400 block mb-1">Redirect URL (after submit)</label>
                    <input type="url" x-model="settings.redirect_url" placeholder="https://..." class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
                </div>
                <div>
                    <label class="text-xs text-gray-400 block mb-1">Notify email</label>
                    <input type="email" x-model="settings.notify_email" placeholder="you@example.com" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
                </div>
                <div class="flex items-center justify-between">
                    <label class="text-xs text-gray-400">Close form (stop accepting)</label>
                    <button @click="settings.close_form = !settings.close_form" :class="settings.close_form ? 'bg-indigo-600' : 'bg-white/10'" class="relative inline-flex h-5 w-9 rounded-full transition">
                        <span :class="settings.close_form ? 'translate-x-4' : 'translate-x-0.5'" class="inline-block h-4 w-4 mt-0.5 transform rounded-full bg-white transition"></span>
                    </button>
                </div>
                <div>
                    <label class="text-xs text-gray-400 block mb-1">Response limit</label>
                    <input type="number" x-model.number="settings.response_limit" placeholder="No limit" min="1" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
                </div>
            </div>

            {{-- Save button --}}
            <div class="p-3 border-t border-white/5">
                <button @click="save()" :disabled="saving" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition">
                    <span x-text="saving ? 'Saving...' : 'Save form'"></span>
                </button>
            </div>
        </div>

        {{-- CENTER: Preview + editor --}}
        <div class="flex-1 overflow-y-auto bg-[#0f0f12]">
            <div class="max-w-2xl mx-auto py-10 px-6">
                <template x-if="steps[activeStep]">
                    <div>
                        {{-- Question header --}}
                        <div class="mb-6 flex items-center gap-3">
                            <span class="text-[10px] uppercase tracking-wider font-medium px-2 py-0.5 rounded"
                                  :class="typeColor(steps[activeStep].type)"
                                  x-text="typeLabel(steps[activeStep].type)"></span>
                            <span class="text-xs text-gray-500" x-text="'Step ' + (activeStep + 1) + ' of ' + steps.length"></span>
                        </div>

                        {{-- Question text --}}
                        <input type="text"
                               x-model="steps[activeStep].question"
                               placeholder="Type your question..."
                               class="w-full bg-transparent border-0 text-2xl font-bold text-white placeholder-gray-600 focus:ring-0 px-0 mb-2">

                        {{-- Description --}}
                        <input type="text"
                               x-model="steps[activeStep].logic.description"
                               x-init="steps[activeStep].logic.description = steps[activeStep].logic.description || ''"
                               placeholder="Description (optional)"
                               class="w-full bg-transparent border-0 text-sm text-gray-400 placeholder-gray-700 focus:ring-0 px-0 mb-8">

                        {{-- Type-specific preview --}}

                        {{-- Welcome screen --}}
                        <template x-if="steps[activeStep].type === 'welcome_screen'">
                            <div class="space-y-4">
                                <input type="text" x-model="steps[activeStep].logic.subtitle" placeholder="Subtitle..." class="w-full bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-0">
                                <input type="text" x-model="steps[activeStep].logic.button_label" placeholder="Button label (e.g. Start)" class="w-48 bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-0">
                            </div>
                        </template>

                        {{-- End screen --}}
                        <template x-if="steps[activeStep].type === 'end_screen'">
                            <div class="space-y-4">
                                <input type="text" x-model="steps[activeStep].logic.subtitle" placeholder="Subtitle..." class="w-full bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-0">
                                <input type="url" x-model="steps[activeStep].logic.redirect_url" placeholder="Redirect URL (optional)" class="w-full bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-0">
                            </div>
                        </template>

                        {{-- Short text --}}
                        <template x-if="steps[activeStep].type === 'short_text'">
                            <div class="border-b-2 border-white/20 pb-2">
                                <span class="text-gray-600 text-lg" x-text="steps[activeStep].logic.placeholder || 'Type your answer here...'"></span>
                            </div>
                        </template>

                        {{-- Long text --}}
                        <template x-if="steps[activeStep].type === 'long_text'">
                            <div class="border-b-2 border-white/20 pb-2">
                                <span class="text-gray-600 text-lg" x-text="steps[activeStep].logic.placeholder || 'Type your answer here...'"></span>
                            </div>
                        </template>

                        {{-- Email --}}
                        <template x-if="steps[activeStep].type === 'email'">
                            <div class="border-b-2 border-white/20 pb-2">
                                <span class="text-gray-600 text-lg">name@example.com</span>
                            </div>
                        </template>

                        {{-- Phone --}}
                        <template x-if="steps[activeStep].type === 'phone'">
                            <div class="border-b-2 border-white/20 pb-2">
                                <span class="text-gray-600 text-lg">+1 (555) 000-0000</span>
                            </div>
                        </template>

                        {{-- Number --}}
                        <template x-if="steps[activeStep].type === 'number'">
                            <div class="border-b-2 border-white/20 pb-2 flex items-center gap-2">
                                <span class="text-gray-600 text-lg">0</span>
                                <span x-show="steps[activeStep].logic.unit" class="text-gray-500 text-sm" x-text="steps[activeStep].logic.unit"></span>
                            </div>
                        </template>

                        {{-- Multiple choice --}}
                        <template x-if="steps[activeStep].type === 'multiple_choice'">
                            <div class="space-y-2">
                                <template x-for="(opt, oi) in steps[activeStep].options" :key="oi">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-md border border-white/20 flex items-center justify-center text-[10px] text-gray-500 font-mono" x-text="String.fromCharCode(65 + oi)"></span>
                                        <input type="text" x-model="steps[activeStep].options[oi]" placeholder="Choice" class="flex-1 bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-0">
                                        <button @click="steps[activeStep].options.splice(oi, 1)" class="text-gray-600 hover:text-red-400 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
                                    </div>
                                </template>
                                <button @click="steps[activeStep].options.push('')" class="text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1 mt-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Add choice
                                </button>
                            </div>
                        </template>

                        {{-- Checkboxes --}}
                        <template x-if="steps[activeStep].type === 'checkboxes'">
                            <div class="space-y-2">
                                <template x-for="(opt, oi) in steps[activeStep].options" :key="oi">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded border border-white/20 flex items-center justify-center text-[10px] text-gray-500"></span>
                                        <input type="text" x-model="steps[activeStep].options[oi]" placeholder="Choice" class="flex-1 bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-0">
                                        <button @click="steps[activeStep].options.splice(oi, 1)" class="text-gray-600 hover:text-red-400 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
                                    </div>
                                </template>
                                <button @click="steps[activeStep].options.push('')" class="text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1 mt-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Add choice
                                </button>
                            </div>
                        </template>

                        {{-- Dropdown --}}
                        <template x-if="steps[activeStep].type === 'dropdown'">
                            <div class="space-y-2">
                                <template x-for="(opt, oi) in steps[activeStep].options" :key="oi">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500 w-5 text-right" x-text="oi + 1"></span>
                                        <input type="text" x-model="steps[activeStep].options[oi]" placeholder="Option" class="flex-1 bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-0">
                                        <button @click="steps[activeStep].options.splice(oi, 1)" class="text-gray-600 hover:text-red-400 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
                                    </div>
                                </template>
                                <button @click="steps[activeStep].options.push('')" class="text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1 mt-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Add option
                                </button>
                            </div>
                        </template>

                        {{-- Rating --}}
                        <template x-if="steps[activeStep].type === 'rating'">
                            <div class="flex gap-2">
                                <template x-for="n in (steps[activeStep].logic.scale || 5)" :key="n">
                                    <div class="w-10 h-10 rounded-lg border border-white/20 flex items-center justify-center text-gray-500 text-lg">
                                        <template x-if="(steps[activeStep].logic.shape || 'star') === 'star'"><span>&#9733;</span></template>
                                        <template x-if="(steps[activeStep].logic.shape || 'star') === 'number'"><span x-text="n"></span></template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Yes/No --}}
                        <template x-if="steps[activeStep].type === 'yes_no'">
                            <div class="flex gap-3">
                                <div class="flex-1 py-4 rounded-lg border border-white/20 text-center text-white font-medium">Yes</div>
                                <div class="flex-1 py-4 rounded-lg border border-white/20 text-center text-white font-medium">No</div>
                            </div>
                        </template>

                        {{-- Date --}}
                        <template x-if="steps[activeStep].type === 'date'">
                            <div class="border-b-2 border-white/20 pb-2">
                                <span class="text-gray-600 text-lg">MM / DD / YYYY</span>
                            </div>
                        </template>

                        {{-- Statement --}}
                        <template x-if="steps[activeStep].type === 'statement'">
                            <div>
                                <input type="text" x-model="steps[activeStep].logic.button_label" placeholder="Button label (e.g. Continue)" class="w-48 bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-0">
                            </div>
                        </template>

                        {{-- Per-question settings --}}
                        <div class="mt-10 pt-6 border-t border-white/5 space-y-4"
                             x-show="!['welcome_screen', 'end_screen', 'statement'].includes(steps[activeStep].type)">
                            <h4 class="text-xs text-gray-500 uppercase tracking-wider font-medium">Question settings</h4>
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-400">Required</label>
                                <button @click="steps[activeStep].logic.required = !steps[activeStep].logic.required"
                                        :class="steps[activeStep].logic.required ? 'bg-indigo-600' : 'bg-white/10'"
                                        class="relative inline-flex h-5 w-9 rounded-full transition">
                                    <span :class="steps[activeStep].logic.required ? 'translate-x-4' : 'translate-x-0.5'"
                                          class="inline-block h-4 w-4 mt-0.5 transform rounded-full bg-white transition"></span>
                                </button>
                            </div>
                            <div x-show="['short_text', 'long_text', 'email', 'phone', 'number'].includes(steps[activeStep].type)">
                                <label class="text-xs text-gray-400 block mb-1">Placeholder</label>
                                <input type="text" x-model="steps[activeStep].logic.placeholder" placeholder="Placeholder text" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-0">
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 block mb-1">Button label</label>
                                <input type="text" x-model="steps[activeStep].logic.button_label" placeholder="OK" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-0">
                            </div>
                            <template x-if="steps[activeStep].type === 'number'">
                                <div class="grid grid-cols-3 gap-2">
                                    <div><label class="text-xs text-gray-400 block mb-1">Min</label><input type="number" x-model.number="steps[activeStep].logic.min" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-0"></div>
                                    <div><label class="text-xs text-gray-400 block mb-1">Max</label><input type="number" x-model.number="steps[activeStep].logic.max" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-0"></div>
                                    <div><label class="text-xs text-gray-400 block mb-1">Unit</label><input type="text" x-model="steps[activeStep].logic.unit" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-0"></div>
                                </div>
                            </template>
                            <template x-if="steps[activeStep].type === 'rating'">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-xs text-gray-400 block mb-1">Scale</label>
                                        <select x-model.number="steps[activeStep].logic.scale" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
                                            <option value="5">1–5</option>
                                            <option value="10">1–10</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-400 block mb-1">Shape</label>
                                        <select x-model="steps[activeStep].logic.shape" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
                                            <option value="star">Stars</option>
                                            <option value="number">Numbers</option>
                                        </select>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function formBuilder(cfg) {
        return {
            title: cfg.title,
            description: cfg.description,
            settings: cfg.settings,
            steps: cfg.steps.map(s => ({ ...s, logic: s.logic || {} })),
            activeStep: 0,
            tab: 'questions',
            saving: false,
            updateUrl: cfg.updateUrl,

            inputTypes: [
                { value: 'short_text', label: 'Short Text', icon: 'Aa' },
                { value: 'long_text', label: 'Long Text', icon: "¶" },
                { value: 'email', label: 'Email', icon: '@' },
                { value: 'phone', label: 'Phone', icon: "☎" },
                { value: 'number', label: 'Number', icon: '#' },
                { value: 'date', label: 'Date', icon: "📅" },
            ],
            choiceTypes: [
                { value: 'multiple_choice', label: 'Multiple Choice', icon: "○" },
                { value: 'checkboxes', label: 'Checkboxes', icon: "☐" },
                { value: 'dropdown', label: 'Dropdown', icon: "▾" },
                { value: 'rating', label: 'Rating', icon: "★" },
                { value: 'yes_no', label: 'Yes / No', icon: "✓" },
            ],
            otherTypes: [
                { value: 'statement', label: 'Statement', icon: "ℹ" },
            ],

            typeLabel(t) {
                const map = {
                    welcome_screen:'Welcome', end_screen:'End Screen', short_text:'Short Text',
                    long_text:'Long Text', email:'Email', phone:'Phone', number:'Number',
                    multiple_choice:'Choice', checkboxes:'Checkboxes', dropdown:'Dropdown',
                    rating:'Rating', yes_no:'Yes/No', date:'Date', statement:'Statement',
                };
                return map[t] || t;
            },
            typeColor(t) {
                if (['welcome_screen','end_screen'].includes(t)) return 'bg-purple-500/20 text-purple-400';
                if (['multiple_choice','checkboxes','dropdown','yes_no'].includes(t)) return 'bg-blue-500/20 text-blue-400';
                if (t === 'rating') return 'bg-yellow-500/20 text-yellow-400';
                if (t === 'statement') return 'bg-gray-500/20 text-gray-400';
                return 'bg-emerald-500/20 text-emerald-400';
            },

            addStep(type) {
                const endIdx = this.steps.findIndex(s => s.type === 'end_screen');
                const idx = endIdx >= 0 ? endIdx : this.steps.length;
                const step = { type, question: '', options: [], logic: {} };
                if (['multiple_choice','checkboxes','dropdown'].includes(type)) {
                    step.options = ['Option 1', 'Option 2'];
                }
                if (type === 'rating') step.logic = { scale: 5, shape: 'star' };
                if (type === 'statement') step.logic = { button_label: 'Continue' };
                this.steps.splice(idx, 0, step);
                this.activeStep = idx;
            },

            removeStep(i) {
                if (this.steps[i].type === 'welcome_screen' || this.steps[i].type === 'end_screen') return;
                this.steps.splice(i, 1);
                if (this.activeStep >= this.steps.length) this.activeStep = this.steps.length - 1;
            },

            moveStep(i, dir) {
                const ni = i + dir;
                if (ni < 0 || ni >= this.steps.length) return;
                [this.steps[i], this.steps[ni]] = [this.steps[ni], this.steps[i]];
                this.activeStep = ni;
            },

            async save() {
                this.saving = true;
                try {
                    const res = await fetch(this.updateUrl, {
                        method: 'PUT',
                        redirect: 'manual',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            title: this.title,
                            description: this.description,
                            settings: this.settings,
                            steps: this.steps,
                        }),
                    });
                    if (!res.ok && res.type !== 'opaqueredirect') {
                        const body = await res.json().catch(() => ({}));
                        alert('Save failed: ' + (body.message || res.status));
                        return;
                    }
                    window.location.reload();
                } finally {
                    this.saving = false;
                }
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
