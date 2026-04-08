<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to forms</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit form</h2>
            </div>
            <div class="flex items-center gap-3">
                @if ($form->is_published)
                    <a href="{{ route('public.form.show', $form->slug) }}" target="_blank" class="text-sm text-gray-600 hover:text-gray-900 underline">Open public link</a>
                @endif
                <form method="POST" action="{{ route('forms.publish', $form) }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-sm rounded-md {{ $form->is_published ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-green-600 text-white hover:bg-green-700' }}">
                        {{ $form->is_published ? 'Unpublish' : 'Publish' }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 bg-green-50 text-green-800 px-4 py-3 rounded-md text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 bg-red-50 text-red-800 px-4 py-3 rounded-md text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div
                x-data="formBuilder({{ Js::from([
                    'title' => $form->title,
                    'description' => $form->description,
                    'steps' => $form->steps->map(fn ($s) => [
                        'type' => $s->type,
                        'question' => $s->question,
                        'options' => $s->options ?? [],
                    ])->values(),
                ]) }})"
                class="space-y-6"
            >
                <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                    <div>
                        <label class="block text-xs uppercase font-medium text-gray-500 mb-1">Title</label>
                        <input type="text" x-model="title" class="w-full text-2xl font-bold border-0 border-b border-gray-200 focus:border-indigo-500 focus:ring-0 px-0">
                    </div>
                    <div>
                        <label class="block text-xs uppercase font-medium text-gray-500 mb-1">Description</label>
                        <textarea x-model="description" rows="2" class="w-full border-0 border-b border-gray-200 focus:border-indigo-500 focus:ring-0 px-0 text-gray-700"></textarea>
                    </div>
                </div>

                <template x-for="(step, index) in steps" :key="index">
                    <div class="bg-white rounded-lg shadow-sm p-6 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-medium text-gray-500" x-text="`Question ${index + 1}`"></div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="moveUp(index)" class="text-gray-400 hover:text-gray-700" title="Move up">&uarr;</button>
                                <button type="button" @click="moveDown(index)" class="text-gray-400 hover:text-gray-700" title="Move down">&darr;</button>
                                <button type="button" @click="removeStep(index)" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <select x-model="step.type" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="text">Short answer</option>
                                <option value="textarea">Long answer</option>
                                <option value="mcq">Single choice</option>
                                <option value="multi">Multiple choice</option>
                            </select>
                            <input type="text" x-model="step.question" placeholder="Question text" class="md:col-span-2 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <template x-if="step.type === 'mcq' || step.type === 'multi'">
                            <div class="space-y-2">
                                <template x-for="(_, oi) in step.options" :key="oi">
                                    <div class="flex items-center gap-2">
                                        <input type="text" x-model="step.options[oi]" placeholder="Option" class="flex-1 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <button type="button" @click="step.options.splice(oi, 1)" class="text-red-500 hover:text-red-700 text-sm">&times;</button>
                                    </div>
                                </template>
                                <button type="button" @click="step.options.push('')" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add option</button>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="flex items-center justify-between">
                    <button type="button" @click="addStep()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        + Add question
                    </button>
                    <button type="button" @click="save()" :disabled="saving" class="inline-flex items-center px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md disabled:opacity-50">
                        <span x-text="saving ? 'Saving...' : 'Save form'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function formBuilder(initial) {
                return {
                    title: initial.title || '',
                    description: initial.description || '',
                    steps: initial.steps || [],
                    saving: false,
                    addStep() {
                        this.steps.push({ type: 'text', question: '', options: [] });
                    },
                    removeStep(i) {
                        this.steps.splice(i, 1);
                    },
                    moveUp(i) {
                        if (i === 0) return;
                        [this.steps[i - 1], this.steps[i]] = [this.steps[i], this.steps[i - 1]];
                    },
                    moveDown(i) {
                        if (i === this.steps.length - 1) return;
                        [this.steps[i + 1], this.steps[i]] = [this.steps[i], this.steps[i + 1]];
                    },
                    async save() {
                        this.saving = true;
                        try {
                            const res = await fetch(@json(route('forms.update', $form)), {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    title: this.title,
                                    description: this.description,
                                    steps: this.steps,
                                }),
                            });
                            if (!res.ok) {
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
