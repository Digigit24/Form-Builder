<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('forms.edit', $form) }}" class="text-gray-500 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                </a>
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ $form->title }}</h2>
                    <p class="text-xs text-gray-500">{{ $responses->total() }} {{ Str::plural('response', $responses->total()) }}</p>
                </div>
            </div>
            @if ($form->is_published)
                <a href="{{ route('public.form.show', $form->slug) }}" target="_blank" class="text-xs text-gray-400 hover:text-white">Open form</a>
            @endif
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
    @endif

    @if ($responses->isEmpty())
        <div class="bg-[#18181f] rounded-xl border border-white/5 p-16 text-center">
            <svg class="mx-auto w-12 h-12 text-gray-600" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
            <p class="mt-4 text-gray-500 text-sm">No submissions yet.</p>
            @if ($form->is_published)
                <p class="mt-1 text-xs text-gray-600">Share: <a href="{{ route('public.form.show', $form->slug) }}" class="text-indigo-400 hover:text-indigo-300">/f/{{ $form->slug }}</a></p>
            @endif
        </div>
    @else
        {{-- Table --}}
        <div class="bg-[#18181f] rounded-xl border border-white/5 overflow-x-auto mb-6">
            <table class="w-full text-left text-sm">
                <thead class="text-[10px] uppercase tracking-wider text-gray-500 border-b border-white/5">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        @foreach ($form->steps->reject(fn ($s) => in_array($s->type, ['welcome_screen', 'end_screen', 'statement'])) as $step)
                            <th class="px-4 py-3 max-w-[200px] truncate" title="{{ $step->question }}">{{ Str::limit($step->question, 25) }}</th>
                        @endforeach
                        <th class="px-4 py-3">Submitted</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($responses as $i => $response)
                        <tr class="hover:bg-white/[0.02]">
                            <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $responses->firstItem() + $i }}</td>
                            @foreach ($form->steps->reject(fn ($s) => in_array($s->type, ['welcome_screen', 'end_screen', 'statement'])) as $step)
                                @php
                                    $answer = $response->answers->firstWhere('step_id', $step->id);
                                    $value = $answer?->answer;
                                @endphp
                                <td class="px-4 py-3 text-gray-300 max-w-[200px] truncate">
                                    @if (is_null($value))
                                        <span class="text-gray-700">&mdash;</span>
                                    @elseif (is_array($value) && isset($value['value']))
                                        {{ Str::limit($value['value'], 50) }}
                                    @elseif (is_array($value))
                                        {{ Str::limit(implode(', ', $value), 50) }}
                                    @else
                                        {{ Str::limit($value, 50) }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs">{{ $response->created_at->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('forms.responses.destroy', [$form, $response]) }}" onsubmit="return confirm('Delete this response?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500/50 hover:text-red-400 text-xs">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>{{ $responses->links() }}</div>

        {{-- Detail cards --}}
        <div class="space-y-3 mt-8" x-data="{ openId: null }">
            <h3 class="text-xs text-gray-500 uppercase tracking-wider font-medium">Detail view</h3>
            @foreach ($responses as $response)
                <div class="bg-[#18181f] rounded-xl border border-white/5 overflow-hidden">
                    <button
                        @click="openId = openId === '{{ $response->id }}' ? null : '{{ $response->id }}'"
                        class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-white/[0.02] transition"
                    >
                        <div>
                            <span class="font-medium text-white text-sm">Response #{{ $responses->firstItem() + $loop->index }}</span>
                            <span class="text-gray-600 text-xs ml-2">{{ $response->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-600 transition-transform" :class="openId === '{{ $response->id }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openId === '{{ $response->id }}'" x-collapse class="border-t border-white/5">
                        <dl class="divide-y divide-white/5">
                            @foreach ($response->answers as $answer)
                                <div class="px-5 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-xs text-gray-500">{{ $answer->step?->question ?? 'Deleted question' }}</dt>
                                    <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-300">
                                        @if (is_array($answer->answer) && isset($answer->answer['value']))
                                            {{ $answer->answer['value'] }}
                                        @elseif (is_array($answer->answer))
                                            {{ implode(', ', $answer->answer) }}
                                        @else
                                            {{ $answer->answer }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
