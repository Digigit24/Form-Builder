<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">Forms</h2>
            <a href="{{ route('forms.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                New form
            </a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
    @endif

    {{-- Stats row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-[#18181f] rounded-xl border border-white/5 p-5">
            <div class="text-xs text-gray-500 uppercase tracking-wider">Workspace</div>
            <div class="mt-1 text-lg font-semibold text-white">{{ auth()->user()->tenant->name }}</div>
        </div>
        <div class="bg-[#18181f] rounded-xl border border-white/5 p-5">
            <div class="text-xs text-gray-500 uppercase tracking-wider">Total forms</div>
            <div class="mt-1 text-2xl font-bold text-white">{{ $forms->count() }}</div>
        </div>
        <div class="bg-[#18181f] rounded-xl border border-white/5 p-5">
            <div class="text-xs text-gray-500 uppercase tracking-wider">Total responses</div>
            <div class="mt-1 text-2xl font-bold text-white">{{ $totalResponses }}</div>
        </div>
    </div>

    @if ($forms->isEmpty())
        <div class="bg-[#18181f] rounded-xl border border-white/5 p-16 text-center">
            <svg class="mx-auto w-12 h-12 text-gray-600" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
            <p class="mt-4 text-gray-500 text-sm">No forms yet. Create your first form to get started.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($forms as $form)
                <div class="bg-[#18181f] rounded-xl border border-white/5 hover:border-white/10 transition group">
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <h3 class="font-semibold text-white truncate pr-3">{{ $form->title }}</h3>
                            @if ($form->is_published)
                                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-500/10 text-green-400 border border-green-500/20">Live</span>
                            @else
                                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-500/10 text-gray-500 border border-gray-500/20">Draft</span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-gray-500 truncate">/f/{{ $form->slug }}</p>

                        <div class="mt-4 flex items-center gap-4 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>
                                {{ $form->responses_count }} {{ Str::plural('response', $form->responses_count) }}
                            </span>
                            <span>{{ $form->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    <div class="border-t border-white/5 px-5 py-3 flex items-center gap-3">
                        <a href="{{ route('forms.edit', $form) }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">Edit</a>
                        <a href="{{ route('forms.responses', $form) }}" class="text-xs text-gray-400 hover:text-white font-medium">Responses</a>
                        @if ($form->is_published)
                            <a href="{{ route('public.form.show', $form->slug) }}" target="_blank" class="text-xs text-gray-400 hover:text-white font-medium">Open</a>
                            <button onclick="navigator.clipboard.writeText('{{ url('/f/'.$form->slug) }}').then(() => this.textContent = 'Copied!', () => {})" class="text-xs text-gray-400 hover:text-white font-medium ml-auto">Copy link</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
