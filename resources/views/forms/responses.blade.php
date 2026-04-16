<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('forms.edit', $form) }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to builder</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Responses &mdash; {{ $form->title }}</h2>
            </div>
            <div class="text-sm text-gray-500">
                {{ $responses->total() }} {{ Str::plural('response', $responses->total()) }}
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 text-green-800 px-4 py-3 rounded-md text-sm">{{ session('status') }}</div>
            @endif

            @if ($responses->isEmpty())
                <div class="bg-white rounded-lg shadow-sm p-10 text-center text-gray-500">
                    No submissions yet.
                    @if ($form->is_published)
                        Share the link: <a href="{{ route('public.form.show', $form->slug) }}" target="_blank" class="text-indigo-600 underline">/f/{{ $form->slug }}</a>
                    @else
                        <a href="{{ route('forms.edit', $form) }}" class="text-indigo-600 underline">Publish the form</a> to start collecting responses.
                    @endif
                </div>
            @else
                {{-- Table header: question names --}}
                <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3 whitespace-nowrap">#</th>
                                @foreach ($form->steps as $step)
                                    <th class="px-4 py-3 whitespace-nowrap max-w-xs truncate" title="{{ $step->question }}">{{ Str::limit($step->question, 30) }}</th>
                                @endforeach
                                <th class="px-4 py-3 whitespace-nowrap">Submitted</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($responses as $i => $response)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $responses->firstItem() + $i }}</td>
                                    @foreach ($form->steps as $step)
                                        @php
                                            $answer = $response->answers->firstWhere('step_id', $step->id);
                                            $value = $answer ? $answer->answer : null;
                                        @endphp
                                        <td class="px-4 py-3 text-gray-700 max-w-xs truncate" title="{{ is_array($value) ? (isset($value['value']) ? $value['value'] : implode(', ', $value)) : $value }}">
                                            @if (is_null($value))
                                                <span class="text-gray-300">&mdash;</span>
                                            @elseif (is_array($value) && isset($value['value']))
                                                {{ Str::limit($value['value'], 60) }}
                                            @elseif (is_array($value))
                                                {{ Str::limit(implode(', ', $value), 60) }}
                                            @else
                                                {{ Str::limit($value, 60) }}
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs">{{ $response->created_at->diffForHumans() }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('forms.responses.destroy', [$form, $response]) }}" onsubmit="return confirm('Delete this response?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>{{ $responses->links() }}</div>
            @endif

            {{-- Expandable detail view --}}
            @if ($responses->isNotEmpty())
                <div class="space-y-4" x-data="{ openId: null }">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Detailed view</h3>
                    @foreach ($responses as $response)
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <button
                                @click="openId = openId === '{{ $response->id }}' ? null : '{{ $response->id }}'"
                                class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-gray-50"
                            >
                                <div>
                                    <span class="font-medium text-gray-800">Response #{{ $responses->firstItem() + $loop->index }}</span>
                                    <span class="text-gray-400 text-sm ml-2">{{ $response->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openId === '{{ $response->id }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="openId === '{{ $response->id }}'" x-collapse class="border-t border-gray-100">
                                <dl class="divide-y divide-gray-50">
                                    @foreach ($response->answers as $answer)
                                        <div class="px-5 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                            <dt class="text-sm font-medium text-gray-500">{{ $answer->step?->question ?? 'Deleted question' }}</dt>
                                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">
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
        </div>
    </div>
</x-app-layout>
