<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Forms') }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('theme.edit') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">Theme</a>
                <form method="POST" action="{{ route('forms.store') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md">
                        + New form
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 text-green-800 px-4 py-3 rounded-md text-sm">{{ session('status') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">Workspace</div>
                    <div class="text-lg font-semibold text-gray-900">{{ auth()->user()->tenant->name }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">Forms</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $forms->count() }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500">Total responses</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $totalResponses }}</div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                @if ($forms->isEmpty())
                    <div class="p-10 text-center text-gray-500">
                        No forms yet. Click <strong>+ New form</strong> to create your first one.
                    </div>
                @else
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Title</th>
                                <th class="px-6 py-3">Slug</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Responses</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @foreach ($forms as $form)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $form->title }}</td>
                                    <td class="px-6 py-4 text-gray-500">/f/{{ $form->slug }}</td>
                                    <td class="px-6 py-4">
                                        @if ($form->is_published)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-green-100 text-green-800">Published</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700">Draft</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">{{ $form->responses_count }}</td>
                                    <td class="px-6 py-4 text-right space-x-3">
                                        <a href="{{ route('forms.edit', $form) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        @if ($form->is_published)
                                            <a href="{{ route('public.form.show', $form->slug) }}" target="_blank" class="text-gray-600 hover:text-gray-900">Open</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
