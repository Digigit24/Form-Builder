<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Workspace theme</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
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

            <form method="POST" action="{{ route('theme.update') }}" class="bg-white rounded-lg shadow-sm p-6 space-y-5">
                @csrf
                @method('PUT')

                <p class="text-sm text-gray-600">These colors are applied to every published form on this workspace.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Primary color</label>
                        <input type="color" name="primary_color" value="{{ old('primary_color', $tenant->primary_color) }}" class="h-10 w-20 rounded border border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Secondary color</label>
                        <input type="color" name="secondary_color" value="{{ old('secondary_color', $tenant->secondary_color) }}" class="h-10 w-20 rounded border border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Background color</label>
                        <input type="color" name="background_color" value="{{ old('background_color', $tenant->background_color) }}" class="h-10 w-20 rounded border border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Font family</label>
                        <select name="font_family" class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (['Inter', 'Roboto', 'Lato', 'Poppins', 'Merriweather', 'Playfair Display'] as $font)
                                <option value="{{ $font }}" @selected(old('font_family', $tenant->font_family) === $font)>{{ $font }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md">
                        Save theme
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
