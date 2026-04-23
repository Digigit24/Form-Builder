<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between h-full gap-3">
            {{-- Left: back + title + save status dot --}}
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('dashboard') }}" class="shrink-0 p-1.5 rounded-md text-gray-500 hover:text-white hover:bg-white/5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                </a>
                <span class="h-4 w-px bg-white/10 shrink-0"></span>
                <h2 class="text-sm font-medium text-white truncate max-w-[180px]">{{ $form->title }}</h2>

                {{-- Tiny save-status dot — receives events from the Alpine component --}}
                <div x-data="{ ss: 'idle' }" @save-status-update.window="ss = $event.detail.status"
                     class="flex items-center gap-1.5 text-xs transition-all">
                    <span class="w-1.5 h-1.5 rounded-full transition-all"
                          :class="{
                              'bg-yellow-400 animate-pulse': ss === 'pending',
                              'bg-blue-400 animate-pulse':   ss === 'saving',
                              'bg-green-400':                ss === 'saved',
                              'bg-amber-400 animate-pulse':  ss === 'offline',
                              'bg-red-400':                  ss === 'error',
                              'opacity-0 pointer-events-none': ss === 'idle',
                          }"></span>
                    <span x-show="ss !== 'idle'"
                          :class="{
                              'text-yellow-400': ss === 'pending',
                              'text-blue-400':   ss === 'saving',
                              'text-green-400':  ss === 'saved',
                              'text-amber-400':  ss === 'offline',
                              'text-red-400':    ss === 'error',
                          }"
                          x-text="{
                              pending: 'Unsaved',
                              saving:  'Saving…',
                              saved:   'Saved',
                              offline: 'Offline',
                              error:   'Failed',
                              idle:    '',
                          }[ss]"></span>
                </div>
            </div>

            {{-- Right: actions --}}
            <div class="flex items-center gap-1.5 shrink-0">
                <a href="{{ route('forms.responses', $form) }}"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-gray-400 hover:text-white hover:bg-white/5 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0-3.75-3.75M17.25 21 21 17.25"/></svg>
                    Responses
                    <span class="bg-white/10 text-gray-300 text-[10px] px-1.5 py-0.5 rounded-full tabular-nums">{{ $form->responses_count }}</span>
                </a>

                @if($form->is_published)
                <a href="{{ route('public.form.show', $form->slug) }}" target="_blank"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-gray-400 hover:text-white hover:bg-white/5 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    Preview
                </a>
                @endif

                <span class="h-4 w-px bg-white/10"></span>

                <button onclick="document.dispatchEvent(new CustomEvent('open-share'))"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-gray-400 hover:text-white border border-white/10 hover:border-white/20 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"/></svg>
                    Share
                </button>

                <form method="POST" action="{{ route('forms.publish', $form) }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-semibold text-white transition
                                   {{ $form->is_published ? 'bg-green-500 hover:bg-green-400' : 'bg-indigo-600 hover:bg-indigo-500' }}">
                        @if($form->is_published)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        @endif
                        {{ $form->is_published ? 'Published' : 'Publish' }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    {{-- ── Main Alpine component ────────────────────────────────────────────── --}}
    <div
        x-data="formBuilder({{ Js::from([
            'formId'     => $form->id,
            'updatedAt'  => $form->updated_at->timestamp,
            'title'      => $form->title,
            'description'=> $form->description ?? '',
            'isPublished'=> $form->is_published,
            'settings'   => $form->settings ?? [
                'progress_bar'   => 'bar',
                'submit_label'   => 'Submit',
                'redirect_url'   => '',
                'notify_email'   => '',
                'close_form'     => false,
                'response_limit' => null,
            ],
            'design'     => $form->theme_config,
            'steps'      => $form->steps->map(fn ($s) => [
                'id'      => $s->id,       // kept stable so response_answers stay linked
                'type'    => $s->type,
                'question'=> $s->question,
                'options' => $s->options ?? [],
                'logic'   => $s->logic ?? [],
            ])->values(),
            'updateUrl'  => route('forms.update', $form),
            'mediaUrl'   => route('forms.media', $form),
            'publicUrl'  => route('public.form.show', $form->slug),
        ]) }})"
        @open-share.document="showShareModal = true"
        class="relative -m-6"
    >
        {{-- ── Share Modal ──────────────────────────────────────────────────── --}}
        <div x-show="showShareModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
             @click.self="showShareModal = false">
            <div class="bg-[#1a1a24] rounded-xl border border-white/10 shadow-2xl w-full max-w-md mx-4 overflow-hidden"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
                    <h3 class="text-sm font-semibold text-white">Share your form</h3>
                    <button @click="showShareModal = false" class="text-gray-500 hover:text-white transition p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs text-gray-400 font-medium">Public URL</label>
                            <span x-show="!isPublished" class="text-[10px] text-amber-400 bg-amber-400/10 px-2 py-0.5 rounded-full">Not published</span>
                            <span x-show="isPublished"  class="text-[10px] text-green-400 bg-green-400/10 px-2 py-0.5 rounded-full">Live</span>
                        </div>
                        <div class="flex gap-2">
                            <input type="text" :value="publicUrl" readonly
                                   class="flex-1 bg-white/5 border border-white/10 rounded-lg text-xs text-gray-300 px-3 py-2.5 font-mono focus:outline-none select-all">
                            <button @click="copyUrl()"
                                    class="px-4 py-2.5 rounded-lg text-xs font-medium transition"
                                    :class="urlCopied ? 'bg-green-500/20 text-green-400 border border-green-500/20' : 'bg-indigo-600 hover:bg-indigo-500 text-white'">
                                <span x-text="urlCopied ? 'Copied!' : 'Copy'"></span>
                            </button>
                        </div>
                    </div>
                    <div class="p-3 rounded-lg bg-white/[0.03] border border-white/5">
                        <p class="text-xs text-gray-500">
                            Form is currently
                            <span x-text="isPublished ? 'published and accepting responses.' : 'a draft — publish it to share the link.'"
                                  :class="isPublished ? 'text-green-400' : 'text-amber-400'"></span>
                        </p>
                    </div>
                    <template x-if="!isPublished">
                        <form method="POST" action="{{ route('forms.publish', $form) }}">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg transition">
                                Publish now
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>

        {{-- ── Main layout ───────────────────────────────────────────────────── --}}
        <div class="flex h-[calc(100vh-73px)]">

            {{-- ── LEFT: sidebar ──────────────────────────────────────────────── --}}
            <div class="w-[272px] bg-[#18181f] border-r border-white/5 flex flex-col overflow-hidden shrink-0">

                {{-- Tabs --}}
                <div class="flex border-b border-white/5 shrink-0">
                    <button @click="tab = 'blocks'"
                            :class="tab === 'blocks' ? 'text-white border-indigo-500' : 'text-gray-500 border-transparent hover:text-gray-300'"
                            class="flex-1 px-3 py-3 text-xs font-medium border-b-2 transition">Blocks</button>
                    <button @click="tab = 'design'"
                            :class="tab === 'design' ? 'text-white border-indigo-500' : 'text-gray-500 border-transparent hover:text-gray-300'"
                            class="flex-1 px-3 py-3 text-xs font-medium border-b-2 transition">Design</button>
                    <button @click="tab = 'settings'"
                            :class="tab === 'settings' ? 'text-white border-indigo-500' : 'text-gray-500 border-transparent hover:text-gray-300'"
                            class="flex-1 px-3 py-3 text-xs font-medium border-b-2 transition">Settings</button>
                </div>

                {{-- ── BLOCKS tab ───────────────────────────────────────────────── --}}
                <div x-show="tab === 'blocks'" class="flex-1 overflow-y-auto p-3 space-y-1">
                    <template x-for="(step, i) in steps" :key="i">
                        <div
                            draggable="true"
                            @dragstart="dragStart(i, $event)"
                            @dragover.prevent="dragOver(i)"
                            @drop.prevent="dragDrop(i)"
                            @dragend="dragEnd()"
                            @click="activeStep = i"
                            :class="{
                                'bg-indigo-600/20 border-indigo-500/30': activeStep === i,
                                'bg-white/[0.02] border-white/5 hover:bg-white/5': activeStep !== i,
                                'opacity-40': drag.active === i,
                                'border-t-2 !border-t-indigo-500': drag.over === i && drag.active !== i,
                            }"
                            class="p-2.5 rounded-lg border cursor-pointer transition-all group select-none"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-gray-600 group-hover:text-gray-400 transition cursor-grab active:cursor-grabbing shrink-0">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M8 6a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Zm5 0a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0ZM8 12a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Zm5 0a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0ZM8 18a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Zm5 0a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z"/></svg>
                                </span>
                                <span class="text-[10px] text-gray-600 font-mono w-4 shrink-0 text-right" x-text="i + 1"></span>
                                <span class="text-[10px] uppercase tracking-wider font-medium px-1.5 py-0.5 rounded"
                                      :class="typeColor(step.type)" x-text="typeLabel(step.type)"></span>
                                <div class="ml-auto flex gap-0.5 opacity-0 group-hover:opacity-100 transition">
                                    <button @click.stop="moveStep(i, -1)" :disabled="i === 0" class="text-gray-500 hover:text-white disabled:opacity-30 p-0.5 rounded transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                    </button>
                                    <button @click.stop="moveStep(i, 1)" :disabled="i === steps.length - 1" class="text-gray-500 hover:text-white disabled:opacity-30 p-0.5 rounded transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                    </button>
                                    <button @click.stop="removeStep(i)"
                                            x-show="step.type !== 'welcome_screen' && step.type !== 'end_screen'"
                                            class="text-gray-500 hover:text-red-400 p-0.5 rounded transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-400 truncate pl-[1.625rem]" x-text="step.question || 'Untitled'"></p>
                        </div>
                    </template>

                    {{-- Add block --}}
                    <div class="relative pt-2" x-data="{ showPicker: false }">
                        <button @click="showPicker = !showPicker"
                                class="w-full flex items-center justify-center gap-2 px-3 py-2.5 border border-dashed border-white/10 rounded-lg text-xs text-gray-500 hover:text-white hover:border-white/20 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Add block
                        </button>
                        <div x-show="showPicker" @click.away="showPicker = false" x-transition
                             class="absolute bottom-full left-0 right-0 mb-1 bg-[#22222a] rounded-lg border border-white/10 shadow-xl p-2 z-20 max-h-[320px] overflow-y-auto">
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider px-2 py-1">Input</p>
                            <template x-for="t in inputTypes" :key="t.value">
                                <button @click="addStep(t.value); showPicker = false"
                                        class="w-full text-left px-3 py-2 text-xs text-gray-300 hover:bg-white/5 rounded flex items-center gap-2 transition">
                                    <span x-text="t.icon" class="text-sm w-5 text-center shrink-0"></span>
                                    <span x-text="t.label"></span>
                                </button>
                            </template>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider px-2 py-1 mt-1">Choice</p>
                            <template x-for="t in choiceTypes" :key="t.value">
                                <button @click="addStep(t.value); showPicker = false"
                                        class="w-full text-left px-3 py-2 text-xs text-gray-300 hover:bg-white/5 rounded flex items-center gap-2 transition">
                                    <span x-text="t.icon" class="text-sm w-5 text-center shrink-0"></span>
                                    <span x-text="t.label"></span>
                                </button>
                            </template>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider px-2 py-1 mt-1">Other</p>
                            <template x-for="t in otherTypes" :key="t.value">
                                <button @click="addStep(t.value); showPicker = false"
                                        class="w-full text-left px-3 py-2 text-xs text-gray-300 hover:bg-white/5 rounded flex items-center gap-2 transition">
                                    <span x-text="t.icon" class="text-sm w-5 text-center shrink-0"></span>
                                    <span x-text="t.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- ── DESIGN tab ───────────────────────────────────────────────── --}}
                <div x-show="tab === 'design'" class="flex-1 overflow-y-auto">

                    {{-- Colors --}}
                    <div class="px-4 pt-4 pb-3">
                        <p class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold mb-3">Colors</p>
                        <div class="space-y-1.5">
                            <template x-for="[key, label] in [['background','Background'],['questions','Questions'],['answers','Answers'],['buttons','Buttons'],['button_text','Button Text'],['star_rating','Star Rating']]" :key="key">
                                <div class="flex items-center justify-between py-1">
                                    <span class="text-xs text-gray-300" x-text="label"></span>
                                    <div class="flex items-center gap-2">
                                        <label class="relative w-6 h-6 rounded-md border border-white/10 cursor-pointer overflow-hidden flex-shrink-0 shadow-sm">
                                            <div class="absolute inset-0 rounded-md" :style="`background:${design.colors[key]}`"></div>
                                            <input type="color" :value="design.colors[key]"
                                                   @input="design.colors[key] = $event.target.value"
                                                   class="absolute -inset-2 opacity-0 cursor-pointer w-10 h-10">
                                        </label>
                                        <input type="text" :value="design.colors[key]"
                                               @input="design.colors[key] = $event.target.value"
                                               maxlength="7"
                                               class="w-[72px] bg-white/5 border border-white/10 rounded text-[11px] text-gray-300 px-2 py-1 font-mono focus:border-indigo-500 focus:outline-none transition">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mx-4 border-t border-white/5"></div>

                    {{-- Alignment --}}
                    <div class="px-4 py-3">
                        <p class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold mb-1.5">Alignment</p>
                        <p class="text-[11px] text-gray-600 mb-2.5 leading-relaxed">Align all the blocks from here that don't have an alignment set.</p>
                        <div class="flex gap-1.5">
                            <button @click="design.alignment = 'left'; scheduleSave()"
                                    :class="design.alignment === 'left' ? 'border-indigo-500 text-white bg-indigo-500/10' : 'border-white/10 text-gray-500 hover:border-white/20 hover:text-gray-300'"
                                    class="flex-1 py-2 border rounded-lg text-xs font-medium transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h10.5m-10.5 5.25h16.5"/></svg>Left
                            </button>
                            <button @click="design.alignment = 'center'; scheduleSave()"
                                    :class="design.alignment === 'center' ? 'border-indigo-500 text-white bg-indigo-500/10' : 'border-white/10 text-gray-500 hover:border-white/20 hover:text-gray-300'"
                                    class="flex-1 py-2 border rounded-lg text-xs font-medium transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M7.5 12h9M3.75 17.25h16.5"/></svg>Center
                            </button>
                            <button @click="design.alignment = 'right'; scheduleSave()"
                                    :class="design.alignment === 'right' ? 'border-indigo-500 text-white bg-indigo-500/10' : 'border-white/10 text-gray-500 hover:border-white/20 hover:text-gray-300'"
                                    class="flex-1 py-2 border rounded-lg text-xs font-medium transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M9.75 12h10.5M3.75 17.25h16.5"/></svg>Right
                            </button>
                        </div>
                    </div>

                    <div class="mx-4 border-t border-white/5"></div>

                    {{-- Font --}}
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between mb-2.5">
                            <p class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold">Font</p>
                            <span class="text-[10px] text-gray-600 cursor-not-allowed select-none">+ Custom font <span class="text-[9px] bg-amber-500/20 text-amber-400 px-1 py-px rounded font-bold tracking-wider">PRO</span></span>
                        </div>
                        <select x-model="design.font" @change="scheduleSave()"
                                class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0 appearance-none">
                            <option value="Inter">Inter</option>
                            <option value="Arial">Arial</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Verdana">Verdana</option>
                            <option value="Roboto">Roboto</option>
                            <option value="Poppins">Poppins</option>
                            <option value="DM Sans">DM Sans</option>
                        </select>
                        <div class="mt-3">
                            <p class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold mb-2">Font size</p>
                            <div class="flex gap-1.5">
                                <button @click="design.font_size = 'small'; scheduleSave()"
                                        :class="design.font_size === 'small' ? 'border-indigo-500 text-white bg-indigo-500/10' : 'border-white/10 text-gray-500 hover:border-white/20 hover:text-gray-300'"
                                        class="flex-1 py-2 border rounded-lg text-xs transition">small</button>
                                <button @click="design.font_size = 'medium'; scheduleSave()"
                                        :class="design.font_size === 'medium' ? 'border-indigo-500 text-white bg-indigo-500/10' : 'border-white/10 text-gray-500 hover:border-white/20 hover:text-gray-300'"
                                        class="flex-1 py-2 border rounded-lg text-xs transition">medium</button>
                                <button @click="design.font_size = 'large'; scheduleSave()"
                                        :class="design.font_size === 'large' ? 'border-indigo-500 text-white bg-indigo-500/10' : 'border-white/10 text-gray-500 hover:border-white/20 hover:text-gray-300'"
                                        class="flex-1 py-2 border rounded-lg text-xs transition">large</button>
                            </div>
                        </div>
                    </div>

                    <div class="mx-4 border-t border-white/5"></div>

                    {{-- Background Image --}}
                    <div class="px-4 py-3">
                        <p class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold mb-2.5">Background Image</p>
                        <template x-if="!design.background_image">
                            <label class="flex flex-col items-center justify-center gap-2 w-full h-20 border border-dashed border-white/10 rounded-lg cursor-pointer hover:border-indigo-500/40 hover:bg-indigo-500/5 transition">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                <span class="text-xs text-gray-500">Select image</span>
                                <input type="file" accept="image/*" @change="uploadImage('background', $event)" class="hidden">
                            </label>
                        </template>
                        <template x-if="design.background_image">
                            <div class="relative group rounded-lg overflow-hidden">
                                <img :src="design.background_image" class="w-full h-24 object-cover rounded-lg"
                                     :style="`opacity: ${design.background_opacity / 100}; filter: blur(${Math.min(design.background_blur, 4)}px);`">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                    <button @click="design.background_image = null; scheduleSave()"
                                            class="px-3 py-1.5 bg-red-500/80 hover:bg-red-500 text-white text-xs rounded-md transition">Remove</button>
                                </div>
                            </div>
                        </template>

                        {{-- Blur & Opacity sliders --}}
                        <div x-show="design.background_image" x-transition class="mt-3 space-y-3">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs text-gray-400">Blur</label>
                                    <span class="text-[11px] text-gray-500 font-mono tabular-nums" x-text="design.background_blur + 'px'"></span>
                                </div>
                                <input type="range" min="0" max="20" step="1"
                                       x-model.number="design.background_blur"
                                       class="w-full h-1 rounded-full appearance-none cursor-pointer accent-indigo-500"
                                       style="background:linear-gradient(to right,#6366f1 0%,#6366f1 var(--v,0%),rgba(255,255,255,.1) var(--v,0%))"
                                       @input="$el.style.setProperty('--v',($el.value/20*100)+'%')">
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs text-gray-400">Opacity</label>
                                    <span class="text-[11px] text-gray-500 font-mono tabular-nums" x-text="design.background_opacity + '%'"></span>
                                </div>
                                <input type="range" min="0" max="100" step="1"
                                       x-model.number="design.background_opacity"
                                       class="w-full h-1 rounded-full appearance-none cursor-pointer accent-indigo-500"
                                       style="background:linear-gradient(to right,#6366f1 0%,#6366f1 var(--v,100%),rgba(255,255,255,.1) var(--v,100%))"
                                       @input="$el.style.setProperty('--v',$el.value+'%')">
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-3">
                            <div>
                                <span class="text-xs text-gray-400">Apply per block</span>
                                <p class="text-[10px] text-gray-600 mt-0.5">Set different backgrounds per block</p>
                            </div>
                            <button @click="design.background_per_block = !design.background_per_block; scheduleSave()"
                                    :class="design.background_per_block ? 'bg-indigo-600' : 'bg-white/10'"
                                    class="relative inline-flex h-5 w-9 rounded-full transition shrink-0">
                                <span :class="design.background_per_block ? 'translate-x-4' : 'translate-x-0.5'"
                                      class="inline-block h-4 w-4 mt-0.5 transform rounded-full bg-white transition shadow-sm"></span>
                            </button>
                        </div>
                    </div>

                    <div class="mx-4 border-t border-white/5"></div>

                    {{-- Logo --}}
                    <div class="px-4 py-3">
                        <p class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold mb-2.5">Logo</p>
                        <template x-if="!design.logo">
                            <label class="flex flex-col items-center justify-center gap-2 w-full h-16 border border-dashed border-white/10 rounded-lg cursor-pointer hover:border-indigo-500/40 hover:bg-indigo-500/5 transition">
                                <span class="text-xs text-gray-500">Select your logo</span>
                                <input type="file" accept="image/*" @change="uploadImage('logo', $event)" class="hidden">
                            </label>
                        </template>
                        <template x-if="design.logo">
                            <div class="flex items-center gap-3 p-3 bg-white/5 rounded-lg">
                                <img :src="design.logo" class="h-8 w-auto max-w-[80px] object-contain rounded">
                                <div class="flex-1 min-w-0"><p class="text-xs text-gray-400 truncate">Logo</p></div>
                                <button @click="design.logo = null; scheduleSave()" class="text-gray-500 hover:text-red-400 transition p-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="mx-4 border-t border-white/5"></div>

                    {{-- Round corners --}}
                    <div class="px-4 py-3 mb-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs text-gray-300 font-medium">Round corners</span>
                                <p class="text-[11px] text-gray-600 mt-0.5">Applies to buttons & inputs</p>
                            </div>
                            <button @click="design.round_corners = !design.round_corners; scheduleSave()"
                                    :class="design.round_corners ? 'bg-indigo-600' : 'bg-white/10'"
                                    class="relative inline-flex h-5 w-9 rounded-full transition shrink-0">
                                <span :class="design.round_corners ? 'translate-x-4' : 'translate-x-0.5'"
                                      class="inline-block h-4 w-4 mt-0.5 transform rounded-full bg-white transition shadow-sm"></span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── SETTINGS tab ──────────────────────────────────────────────── --}}
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
                        <label class="text-xs text-gray-400 block mb-1">Progress indicator</label>
                        <select x-model="settings.progress_bar" @change="scheduleSave()"
                                class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
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
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Redirect URL after submit</label>
                        <input type="url" x-model="settings.redirect_url" placeholder="https://..." class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Notify email</label>
                        <input type="email" x-model="settings.notify_email" placeholder="you@example.com" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="text-xs text-gray-400">Close form (stop accepting)</label>
                        <button @click="settings.close_form = !settings.close_form; scheduleSave()"
                                :class="settings.close_form ? 'bg-indigo-600' : 'bg-white/10'"
                                class="relative inline-flex h-5 w-9 rounded-full transition">
                            <span :class="settings.close_form ? 'translate-x-4' : 'translate-x-0.5'"
                                  class="inline-block h-4 w-4 mt-0.5 transform rounded-full bg-white transition"></span>
                        </button>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Response limit</label>
                        <input type="number" x-model.number="settings.response_limit" placeholder="No limit" min="1"
                               class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
                    </div>
                </div>

                {{-- ── Save status indicator (replaces save button) ─────────────── --}}
                <div class="px-4 py-3 border-t border-white/5 shrink-0">
                    {{-- Draft restored notice --}}
                    <div x-show="draftRestored" x-transition
                         class="mb-2.5 px-3 py-2 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span class="text-[11px] text-amber-400 flex-1">Restored local changes</span>
                        <button @click="draftRestored = false" class="text-amber-400/60 hover:text-amber-400 transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Status row --}}
                    <div class="h-7 flex items-center px-1">
                        <div x-show="saveStatus === 'idle'" class="flex items-center gap-1.5 text-xs text-gray-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            All changes saved
                        </div>
                        <div x-show="saveStatus === 'pending'" class="flex items-center gap-1.5 text-xs text-gray-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500 animate-pulse"></span>
                            Unsaved changes
                        </div>
                        <div x-show="saveStatus === 'saving'" class="flex items-center gap-1.5 text-xs text-blue-400">
                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Saving…
                        </div>
                        <div x-show="saveStatus === 'saved'" class="flex items-center gap-1.5 text-xs text-green-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Saved
                        </div>
                        <div x-show="saveStatus === 'offline'" class="flex items-center gap-1.5 text-xs text-amber-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M8.111 8.111A8.001 8.001 0 0 0 4.28 12H2m4.28-4.28A8 8 0 0 1 12 4c4.418 0 8 3.582 8 8 0 1.848-.627 3.55-1.672 4.9M12 18.5a6.48 6.48 0 0 1-2.5-.5"/></svg>
                            Offline · saved locally
                        </div>
                        <div x-show="saveStatus === 'error'" class="flex flex-col gap-1 text-xs text-red-400 w-full">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                <span>Save failed</span>
                                <button @click="_retries=0; doSave()" class="ml-auto px-2 py-0.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded text-[11px] transition">Retry</button>
                            </div>
                            <p x-show="_saveErrorMsg" x-text="_saveErrorMsg"
                               class="text-[10px] text-red-400/70 leading-relaxed truncate pl-4" title="See browser console for full details"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── CENTER: question editor (live design preview) ────────────────── --}}
            <div class="flex-1 overflow-hidden relative transition-colors duration-300"
                 :style="`background-color: ${design.colors.background};`">

                {{-- Background image layer with blur/opacity (separate element for CSS filter support) --}}
                <div x-show="activeBgImage()"
                     class="absolute inset-0 pointer-events-none z-0"
                     :style="bgImageLayerStyle()"></div>

                {{-- Draft restored banner --}}
                <div x-show="draftRestored" x-transition
                     class="absolute top-0 left-0 right-0 z-20 flex items-center gap-3 px-4 py-2.5 bg-amber-500/90 backdrop-blur-sm text-xs text-white">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span>Local changes from your last offline session were restored and will sync now.</span>
                    <button @click="draftRestored = false" class="ml-auto text-white/70 hover:text-white">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Scrollable editor content --}}
                <div class="absolute inset-0 overflow-y-auto z-10">
                    <div class="max-w-2xl mx-auto py-10 px-6">
                        <template x-if="steps[activeStep]">
                            <div>
                                <div class="mb-6 flex items-center gap-3">
                                    <span class="text-[10px] uppercase tracking-wider font-medium px-2 py-0.5 rounded"
                                          :class="typeColor(steps[activeStep].type)"
                                          x-text="typeLabel(steps[activeStep].type)"></span>
                                    <span class="text-xs opacity-50"
                                          :style="`color: ${design.colors.questions}`"
                                          x-text="'Block ' + (activeStep + 1) + ' of ' + steps.length"></span>
                                </div>

                                <input type="text" x-model="steps[activeStep].question"
                                       placeholder="Type your question…"
                                       class="w-full bg-transparent border-0 text-2xl font-bold focus:ring-0 px-0 mb-2"
                                       :style="`color: ${design.colors.questions}; caret-color: ${design.colors.questions};`">

                                <input type="text" x-model="steps[activeStep].logic.description"
                                       x-init="steps[activeStep].logic.description = steps[activeStep].logic.description || ''"
                                       placeholder="Description (optional)"
                                       class="w-full bg-transparent border-0 text-sm focus:ring-0 px-0 mb-8"
                                       :style="`color: ${design.colors.answers}; opacity: 0.6; caret-color: ${design.colors.answers};`">

                                {{-- Welcome screen --}}
                                <template x-if="steps[activeStep].type === 'welcome_screen'">
                                    <div class="space-y-3">
                                        <input type="text" x-model="steps[activeStep].logic.subtitle" placeholder="Subtitle…" class="w-full bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-0">
                                        <input type="text" x-model="steps[activeStep].logic.button_label" placeholder="Button label (e.g. Start)" class="w-44 bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-0">
                                    </div>
                                </template>
                                <template x-if="steps[activeStep].type === 'end_screen'">
                                    <div class="space-y-3">
                                        <input type="text" x-model="steps[activeStep].logic.subtitle" placeholder="Subtitle…" class="w-full bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-0">
                                        <input type="url" x-model="steps[activeStep].logic.redirect_url" placeholder="Redirect URL (optional)" class="w-full bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-0">
                                    </div>
                                </template>
                                <template x-if="steps[activeStep].type === 'short_text' || steps[activeStep].type === 'long_text'">
                                    <div class="border-b-2 border-white/20 pb-2">
                                        <span class="text-gray-600 text-lg" x-text="steps[activeStep].logic.placeholder || 'Type your answer here…'"></span>
                                    </div>
                                </template>
                                <template x-if="steps[activeStep].type === 'email'">
                                    <div class="border-b-2 border-white/20 pb-2"><span class="text-gray-600 text-lg">name@example.com</span></div>
                                </template>
                                <template x-if="steps[activeStep].type === 'phone'">
                                    <div class="border-b-2 border-white/20 pb-2"><span class="text-gray-600 text-lg">+1 (555) 000-0000</span></div>
                                </template>
                                <template x-if="steps[activeStep].type === 'number'">
                                    <div class="border-b-2 border-white/20 pb-2 flex items-center gap-2">
                                        <span class="text-gray-600 text-lg">0</span>
                                        <span x-show="steps[activeStep].logic.unit" class="text-gray-600 text-sm" x-text="steps[activeStep].logic.unit"></span>
                                    </div>
                                </template>
                                <template x-if="steps[activeStep].type === 'multiple_choice'">
                                    <div class="space-y-2">
                                        <template x-for="(opt, oi) in steps[activeStep].options" :key="oi">
                                            <div class="flex items-center gap-2">
                                                <span class="w-6 h-6 rounded-md border border-white/20 flex items-center justify-center text-[10px] text-gray-500 font-mono shrink-0" x-text="String.fromCharCode(65+oi)"></span>
                                                <input type="text" x-model="steps[activeStep].options[oi]" placeholder="Choice" class="flex-1 bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-0">
                                                <button @click="steps[activeStep].options.splice(oi,1); scheduleSave()" class="text-gray-600 hover:text-red-400 p-1 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
                                            </div>
                                        </template>
                                        <button @click="steps[activeStep].options.push(''); scheduleSave()" class="text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1 mt-2 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>Add choice
                                        </button>
                                    </div>
                                </template>
                                <template x-if="steps[activeStep].type === 'checkboxes'">
                                    <div class="space-y-2">
                                        <template x-for="(opt, oi) in steps[activeStep].options" :key="oi">
                                            <div class="flex items-center gap-2">
                                                <span class="w-5 h-5 rounded border border-white/20 shrink-0"></span>
                                                <input type="text" x-model="steps[activeStep].options[oi]" placeholder="Choice" class="flex-1 bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-0">
                                                <button @click="steps[activeStep].options.splice(oi,1); scheduleSave()" class="text-gray-600 hover:text-red-400 p-1 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
                                            </div>
                                        </template>
                                        <button @click="steps[activeStep].options.push(''); scheduleSave()" class="text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1 mt-2 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>Add choice
                                        </button>
                                    </div>
                                </template>
                                <template x-if="steps[activeStep].type === 'dropdown'">
                                    <div class="space-y-2">
                                        <template x-for="(opt, oi) in steps[activeStep].options" :key="oi">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-gray-600 w-5 text-right shrink-0" x-text="oi+1"></span>
                                                <input type="text" x-model="steps[activeStep].options[oi]" placeholder="Option" class="flex-1 bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-0">
                                                <button @click="steps[activeStep].options.splice(oi,1); scheduleSave()" class="text-gray-600 hover:text-red-400 p-1 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
                                            </div>
                                        </template>
                                        <button @click="steps[activeStep].options.push(''); scheduleSave()" class="text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1 mt-2 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>Add option
                                        </button>
                                    </div>
                                </template>
                                <template x-if="steps[activeStep].type === 'rating'">
                                    <div class="flex gap-2 flex-wrap">
                                        <template x-for="n in (steps[activeStep].logic.scale||5)" :key="n">
                                            <div class="w-10 h-10 rounded-lg border border-white/20 flex items-center justify-center text-gray-500 text-lg">
                                                <template x-if="(steps[activeStep].logic.shape||'star')==='star'"><span>&#9733;</span></template>
                                                <template x-if="(steps[activeStep].logic.shape||'star')==='number'"><span class="text-sm" x-text="n"></span></template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="steps[activeStep].type === 'yes_no'">
                                    <div class="flex gap-3 max-w-xs">
                                        <div class="flex-1 py-4 rounded-lg border border-white/20 text-center text-white font-medium">Yes</div>
                                        <div class="flex-1 py-4 rounded-lg border border-white/20 text-center text-white font-medium">No</div>
                                    </div>
                                </template>
                                <template x-if="steps[activeStep].type === 'date'">
                                    <div class="border-b-2 border-white/20 pb-2"><span class="text-gray-600 text-lg">MM / DD / YYYY</span></div>
                                </template>
                                <template x-if="steps[activeStep].type === 'statement'">
                                    <div><input type="text" x-model="steps[activeStep].logic.button_label" placeholder="Button label (e.g. Continue)" class="w-44 bg-white/5 border border-white/10 rounded-lg text-sm text-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-0"></div>
                                </template>

                                {{-- Per-question settings --}}
                                <div class="mt-10 pt-6 border-t border-white/5 space-y-4"
                                     x-show="!['welcome_screen','end_screen','statement'].includes(steps[activeStep].type)">
                                    <h4 class="text-xs text-gray-500 uppercase tracking-wider font-medium">Block settings</h4>
                                    <div class="flex items-center justify-between">
                                        <label class="text-sm text-gray-400">Required</label>
                                        <button @click="steps[activeStep].logic.required = !steps[activeStep].logic.required; scheduleSave()"
                                                :class="steps[activeStep].logic.required ? 'bg-indigo-600' : 'bg-white/10'"
                                                class="relative inline-flex h-5 w-9 rounded-full transition">
                                            <span :class="steps[activeStep].logic.required ? 'translate-x-4' : 'translate-x-0.5'"
                                                  class="inline-block h-4 w-4 mt-0.5 transform rounded-full bg-white transition"></span>
                                        </button>
                                    </div>
                                    <div x-show="['short_text','long_text','email','phone','number'].includes(steps[activeStep].type)">
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
                                                <select x-model.number="steps[activeStep].logic.scale" @change="scheduleSave()" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
                                                    <option value="5">1–5</option><option value="10">1–10</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-xs text-gray-400 block mb-1">Shape</label>
                                                <select x-model="steps[activeStep].logic.shape" @change="scheduleSave()" class="w-full bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0">
                                                    <option value="star">Stars</option><option value="number">Numbers</option>
                                                </select>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- Per-block background (when per_block is on) --}}
                                <div x-show="design.background_per_block" class="mt-6 pt-6 border-t border-white/5">
                                    <h4 class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-3">Block background</h4>
                                    <template x-if="!steps[activeStep].logic.background_image">
                                        <label class="flex items-center gap-2 px-3 py-2.5 border border-dashed border-white/10 rounded-lg cursor-pointer hover:border-indigo-500/40 hover:bg-indigo-500/5 transition text-xs text-gray-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                            Upload block background
                                            <input type="file" accept="image/*" @change="uploadBlockImage(activeStep, $event)" class="hidden">
                                        </label>
                                    </template>
                                    <template x-if="steps[activeStep].logic.background_image">
                                        <div class="relative group rounded-lg overflow-hidden">
                                            <img :src="steps[activeStep].logic.background_image" class="w-full h-20 object-cover rounded-lg">
                                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                                <button @click="steps[activeStep].logic.background_image = null; scheduleSave()"
                                                        class="px-3 py-1.5 bg-red-500/80 hover:bg-red-500 text-white text-xs rounded-md transition">Remove</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>{{-- /max-w-2xl --}}
                </div>{{-- /scrollable --}}
            </div>{{-- /center panel --}}

        </div>{{-- /flex --}}
    </div>{{-- /x-data --}}

    @push('scripts')
    <script>
    function formBuilder(cfg) {
        // ── Default design config ──────────────────────────────────────────
        const DESIGN_DEFAULTS = {
            colors: { background:'#ffffff', questions:'#000000', answers:'#000000', buttons:'#000000', button_text:'#ffffff', star_rating:'#000000' },
            alignment:'left', font:'Inter', font_size:'medium',
            background_image:null, background_blur:0, background_opacity:100,
            background_per_block:false, logo:null, round_corners:true,
        };
        const d = cfg.design || {};

        // Build initial snapshot for dirty-detection
        const _initialData = () => ({
            title:       cfg.title,
            description: cfg.description,
            settings:    cfg.settings,
            design:      cfg.design,
            steps:       cfg.steps,
        });

        return {
            // ── Form data ────────────────────────────────────────────────────
            formId:      cfg.formId,
            title:       cfg.title,
            description: cfg.description,
            settings:    cfg.settings,
            design: {
                colors:               Object.assign({}, DESIGN_DEFAULTS.colors, d.colors || {}),
                alignment:            d.alignment            ?? DESIGN_DEFAULTS.alignment,
                font:                 d.font                 ?? DESIGN_DEFAULTS.font,
                font_size:            d.font_size            ?? DESIGN_DEFAULTS.font_size,
                background_image:     d.background_image     ?? DESIGN_DEFAULTS.background_image,
                background_blur:      d.background_blur      ?? DESIGN_DEFAULTS.background_blur,
                background_opacity:   d.background_opacity   ?? DESIGN_DEFAULTS.background_opacity,
                background_per_block: d.background_per_block ?? DESIGN_DEFAULTS.background_per_block,
                logo:                 d.logo                 ?? DESIGN_DEFAULTS.logo,
                round_corners:        d.round_corners        !== undefined ? d.round_corners : DESIGN_DEFAULTS.round_corners,
            },
            steps: cfg.steps.map(s => ({ ...s, logic: s.logic || {} })),

            // ── UI state ─────────────────────────────────────────────────────
            activeStep:     0,
            tab:            'blocks',
            showShareModal: false,
            urlCopied:      false,
            isPublished:    cfg.isPublished,
            publicUrl:      cfg.publicUrl,
            updateUrl:      cfg.updateUrl,
            mediaUrl:       cfg.mediaUrl,
            drag:           { active: null, over: null },

            // ── Auto-save state ──────────────────────────────────────────────
            saveStatus:     'idle',   // idle | pending | saving | saved | error | offline
            draftRestored:  false,
            _saveErrorMsg:  null,
            _saveTimer:     null,
            _saveInFlight:  false,
            _saveQueued:    false,
            _retries:       0,
            _lastSaved:     JSON.stringify(_initialData()),
            _DEBOUNCE_MS:   1500,
            _RETRY_DELAYS:  [3000, 8000, 20000],
            _DRAFT_KEY:     `form_draft_${cfg.formId}`,

            // ── Alpine lifecycle ─────────────────────────────────────────────
            init() {
                // 1. Restore any offline draft saved in a previous session
                this._checkDraftOnLoad(cfg.updatedAt);

                // 2. Catch all user-input events via bubbling (text, selects, ranges, color pickers)
                this.$el.addEventListener('input',  () => this.scheduleSave(), { passive: true });
                this.$el.addEventListener('change', () => this.scheduleSave(), { passive: true });

                // 3. Broadcast status changes to the header indicator
                this.$watch('saveStatus', v =>
                    window.dispatchEvent(new CustomEvent('save-status-update', { detail: { status: v } }))
                );

                // 4. Online / offline
                window.addEventListener('online',  () => this._onOnline());
                window.addEventListener('offline', () => this._onOffline());

                // 5. Warn before navigating away with unsaved changes
                window.addEventListener('beforeunload', e => {
                    const dirty = JSON.stringify(this._getFormData()) !== this._lastSaved;
                    if (dirty || this._saveInFlight) {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });
            },

            // ── Auto-save core ────────────────────────────────────────────────

            // Call this from any change that doesn't fire a DOM input/change event
            scheduleSave() {
                if (this._saveInFlight) { this._saveQueued = true; return; }
                this.saveStatus = 'pending';
                clearTimeout(this._saveTimer);
                this._saveTimer = setTimeout(() => this.doSave(), this._DEBOUNCE_MS);
            },

            async doSave() {
                // Already saving — queue it
                if (this._saveInFlight) { this._saveQueued = true; return; }

                // No real change since last save — skip
                const snapshot = JSON.stringify(this._getFormData());
                if (snapshot === this._lastSaved) { this.saveStatus = 'idle'; return; }

                // Offline — persist locally and wait
                if (!navigator.onLine) { this._saveLocally(snapshot); this.saveStatus = 'offline'; return; }

                clearTimeout(this._saveTimer);
                this._saveInFlight = true;
                this.saveStatus = 'saving';

                try {
                    const res = await fetch(this.updateUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: snapshot,
                    });

                    if (!res.ok) {
                        // Parse server error body for logging and display
                        let errBody = {};
                        try { errBody = await res.json(); } catch {}
                        const errMsg = errBody.message
                            || (errBody.errors ? Object.values(errBody.errors).flat().join('; ') : null)
                            || `HTTP ${res.status}`;
                        console.error('[AutoSave] Server rejected save:', res.status, errBody);
                        throw Object.assign(new Error(errMsg), { status: res.status, body: errBody });
                    }

                    this._lastSaved = snapshot;
                    this._clearLocalDraft();
                    this._retries = 0;
                    this.saveStatus = 'saved';
                    this._saveErrorMsg = null;
                    setTimeout(() => { if (this.saveStatus === 'saved') this.saveStatus = 'idle'; }, 3000);

                } catch (err) {
                    console.error('[AutoSave] Save error:', err.message, err.body || '');
                    this._saveErrorMsg = err.message;
                    if (!navigator.onLine) {
                        this._saveLocally(snapshot);
                        this.saveStatus = 'offline';
                    } else if (this._retries < this._RETRY_DELAYS.length) {
                        const delay = this._RETRY_DELAYS[this._retries++];
                        this.saveStatus = 'error';
                        setTimeout(() => this.doSave(), delay);
                    } else {
                        // Max retries exhausted — save locally so data is not lost
                        this._saveLocally(snapshot);
                        this.saveStatus = 'error';
                    }
                } finally {
                    this._saveInFlight = false;
                    if (this._saveQueued) {
                        this._saveQueued = false;
                        setTimeout(() => this.doSave(), 100);
                    }
                }
            },

            // ── Data helpers ──────────────────────────────────────────────────

            _getFormData() {
                return {
                    title:       this.title,
                    description: this.description,
                    settings:    this.settings,
                    design:      this.design,
                    steps:       this.steps,
                };
            },

            // ── Local draft (offline safety net) ──────────────────────────────

            _saveLocally(snapshot) {
                try {
                    localStorage.setItem(this._DRAFT_KEY, JSON.stringify({
                        snapshot, savedAt: Date.now(),
                    }));
                } catch (e) { /* QuotaExceededError — silently ignore */ }
            },

            _clearLocalDraft() {
                localStorage.removeItem(this._DRAFT_KEY);
            },

            _getLocalDraft() {
                try {
                    const raw = localStorage.getItem(this._DRAFT_KEY);
                    return raw ? JSON.parse(raw) : null;
                } catch { return null; }
            },

            // On page load: restore draft if it is newer than the server's version
            _checkDraftOnLoad(serverTimestamp) {
                const draft = this._getLocalDraft();
                if (!draft) return;

                // serverTimestamp is Unix seconds; draft.savedAt is ms
                if (draft.savedAt > serverTimestamp * 1000) {
                    try {
                        const data = JSON.parse(draft.snapshot);
                        // Apply over current state
                        if (data.title !== undefined)       this.title       = data.title;
                        if (data.description !== undefined) this.description = data.description;
                        if (data.settings)                  this.settings    = data.settings;
                        if (data.design)                    this.design      = { ...this.design, ...data.design, colors: { ...this.design.colors, ...(data.design.colors || {}) } };
                        if (data.steps)                     this.steps       = data.steps.map(s => ({ ...s, logic: s.logic || {} }));
                        this.draftRestored = true;
                        // Sync to server if online
                        if (navigator.onLine) this.$nextTick(() => this.doSave());
                    } catch { this._clearLocalDraft(); }
                } else {
                    // Server is newer — draft is stale, discard it
                    this._clearLocalDraft();
                }
            },

            // ── Connectivity handlers ─────────────────────────────────────────

            _onOnline() {
                if (this.saveStatus === 'offline') this.doSave();
            },

            _onOffline() {
                if (this.saveStatus === 'saving' || this.saveStatus === 'pending') {
                    clearTimeout(this._saveTimer);
                    this._saveLocally(JSON.stringify(this._getFormData()));
                    this.saveStatus = 'offline';
                }
            },

            // ── Center panel preview helpers ──────────────────────────────────

            activeBgImage() {
                return this.design.background_per_block
                    ? (this.steps[this.activeStep]?.logic?.background_image || this.design.background_image)
                    : this.design.background_image;
            },

            bgImageLayerStyle() {
                const img = this.activeBgImage();
                if (!img) return '';
                const blur    = this.design.background_blur    || 0;
                const opacity = (this.design.background_opacity ?? 100) / 100;
                const scale   = blur > 0 ? (1 + blur * 0.012).toFixed(3) : '1';
                return [
                    `background-image:url('${img}')`,
                    'background-size:cover',
                    'background-position:center',
                    `filter:blur(${blur}px)`,
                    `opacity:${opacity}`,
                    `transform:scale(${scale})`,
                ].join(';');
            },

            // ── Block management ──────────────────────────────────────────────

            inputTypes:  [
                {value:'short_text', label:'Short Text', icon:'Aa'},
                {value:'long_text',  label:'Long Text',  icon:'¶'},
                {value:'email',      label:'Email',      icon:'@'},
                {value:'phone',      label:'Phone',      icon:'☎'},
                {value:'number',     label:'Number',     icon:'#'},
                {value:'date',       label:'Date',       icon:'📅'},
            ],
            choiceTypes: [
                {value:'multiple_choice', label:'Multiple Choice', icon:'○'},
                {value:'checkboxes',      label:'Checkboxes',      icon:'☐'},
                {value:'dropdown',        label:'Dropdown',        icon:'▾'},
                {value:'rating',          label:'Rating',          icon:'★'},
                {value:'yes_no',          label:'Yes / No',        icon:'✓'},
            ],
            otherTypes:  [{value:'statement', label:'Statement', icon:'ℹ'}],

            typeLabel(t) {
                return {welcome_screen:'Welcome',end_screen:'End Screen',short_text:'Short Text',long_text:'Long Text',email:'Email',phone:'Phone',number:'Number',multiple_choice:'Choice',checkboxes:'Checkboxes',dropdown:'Dropdown',rating:'Rating',yes_no:'Yes/No',date:'Date',statement:'Statement'}[t] || t;
            },
            typeColor(t) {
                if (['welcome_screen','end_screen'].includes(t))              return 'bg-purple-500/20 text-purple-400';
                if (['multiple_choice','checkboxes','dropdown','yes_no'].includes(t)) return 'bg-blue-500/20 text-blue-400';
                if (t === 'rating')    return 'bg-yellow-500/20 text-yellow-400';
                if (t === 'statement') return 'bg-gray-500/20 text-gray-400';
                return 'bg-emerald-500/20 text-emerald-400';
            },

            addStep(type) {
                const endIdx = this.steps.findIndex(s => s.type === 'end_screen');
                const idx    = endIdx >= 0 ? endIdx : this.steps.length;
                const step   = { id: null, type, question:'', options:[], logic:{} };
                if (['multiple_choice','checkboxes','dropdown'].includes(type)) step.options = ['Option 1','Option 2'];
                if (type === 'rating')    step.logic = { scale:5, shape:'star' };
                if (type === 'statement') step.logic = { button_label:'Continue' };
                this.steps.splice(idx, 0, step);
                this.activeStep = idx;
                this.scheduleSave();
            },

            removeStep(i) {
                if (this.steps[i]?.type === 'welcome_screen' || this.steps[i]?.type === 'end_screen') return;
                this.steps.splice(i, 1);
                if (this.activeStep >= this.steps.length) this.activeStep = this.steps.length - 1;
                this.scheduleSave();
            },

            moveStep(i, dir) {
                const ni = i + dir;
                if (ni < 0 || ni >= this.steps.length) return;
                if (this.steps[i]?.type === 'welcome_screen' || this.steps[i]?.type === 'end_screen') return;
                if (ni === 0 && this.steps[0]?.type === 'welcome_screen') return;
                if (ni === this.steps.length - 1 && this.steps[this.steps.length - 1]?.type === 'end_screen') return;
                [this.steps[i], this.steps[ni]] = [this.steps[ni], this.steps[i]];
                this.activeStep = ni;
                this.scheduleSave();
            },

            // ── Drag-and-drop ─────────────────────────────────────────────────

            dragStart(i, event) {
                if (['welcome_screen','end_screen'].includes(this.steps[i]?.type)) { event.preventDefault(); return; }
                this.drag.active = i;
                event.dataTransfer.effectAllowed = 'move';
            },
            dragOver(i) {
                if (this.drag.active === null || this.drag.active === i) return;
                if (i === 0 && this.steps[0]?.type === 'welcome_screen') return;
                if (i === this.steps.length - 1 && this.steps[this.steps.length - 1]?.type === 'end_screen') return;
                this.drag.over = i;
            },
            dragDrop(i) {
                const from = this.drag.active;
                this.drag  = { active:null, over:null };
                if (from === null || from === i) return;
                const step = this.steps[from];
                if (['welcome_screen','end_screen'].includes(step?.type)) return;
                if (i === 0 && this.steps[0]?.type === 'welcome_screen') return;
                if (i === this.steps.length - 1 && this.steps[this.steps.length - 1]?.type === 'end_screen') return;
                this.steps.splice(from, 1);
                const newIdx = from < i ? i - 1 : i;
                this.steps.splice(newIdx, 0, step);
                this.activeStep = newIdx;
                this.scheduleSave();
            },
            dragEnd() { this.drag = { active:null, over:null }; },

            // ── Image upload ──────────────────────────────────────────────────

            async uploadImage(type, event) {
                const file = event.target.files[0];
                if (!file) return;
                const fd = new FormData();
                fd.append('file', file);
                fd.append('type', type);
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                try {
                    const res  = await fetch(this.mediaUrl, { method:'POST', body:fd });
                    const data = await res.json();
                    if (type === 'background') this.design.background_image = data.url;
                    if (type === 'logo')       this.design.logo             = data.url;
                    this.scheduleSave();
                } catch(e) { console.error(e); }
                event.target.value = '';
            },

            async uploadBlockImage(stepIdx, event) {
                const file = event.target.files[0];
                if (!file) return;
                const fd = new FormData();
                fd.append('file', file);
                fd.append('type', 'background');
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                try {
                    const res  = await fetch(this.mediaUrl, { method:'POST', body:fd });
                    const data = await res.json();
                    this.steps[stepIdx].logic.background_image = data.url;
                    this.scheduleSave();
                } catch(e) { console.error(e); }
                event.target.value = '';
            },

            // ── Share helpers ─────────────────────────────────────────────────

            copyUrl() {
                navigator.clipboard.writeText(this.publicUrl).then(() => {
                    this.urlCopied = true;
                    setTimeout(() => { this.urlCopied = false; }, 2000);
                });
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
