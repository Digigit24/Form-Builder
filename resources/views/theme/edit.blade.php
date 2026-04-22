<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">Workspace theme</h2>
    </x-slot>

    @if (session('status'))
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('theme.update') }}" class="bg-[#18181f] rounded-xl border border-white/5 p-6 space-y-6">
            @csrf
            @method('PUT')

            <p class="text-sm text-gray-500">These colors are applied to every published form in this workspace.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-gray-400 mb-2">Primary color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="primary_color" value="{{ old('primary_color', $tenant->primary_color) }}" class="h-10 w-14 rounded border border-white/10 bg-transparent cursor-pointer">
                        <input type="text" value="{{ old('primary_color', $tenant->primary_color) }}" class="flex-1 bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0" readonly>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-2">Secondary color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="secondary_color" value="{{ old('secondary_color', $tenant->secondary_color) }}" class="h-10 w-14 rounded border border-white/10 bg-transparent cursor-pointer">
                        <input type="text" value="{{ old('secondary_color', $tenant->secondary_color) }}" class="flex-1 bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0" readonly>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-2">Background color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="background_color" value="{{ old('background_color', $tenant->background_color) }}" class="h-10 w-14 rounded border border-white/10 bg-transparent cursor-pointer">
                        <input type="text" value="{{ old('background_color', $tenant->background_color) }}" class="flex-1 bg-white/5 border border-white/10 rounded-lg text-xs text-white px-3 py-2 focus:border-indigo-500 focus:ring-0" readonly>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-2">Font family</label>
                    <select name="font_family" class="w-full bg-white/5 border border-white/10 rounded-lg text-sm text-white px-3 py-2.5 focus:border-indigo-500 focus:ring-0">
                        @foreach (['Inter', 'Roboto', 'Lato', 'Poppins', 'Merriweather', 'Playfair Display'] as $font)
                            <option value="{{ $font }}" @selected(old('font_family', $tenant->font_family) === $font)>{{ $font }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-lg transition">
                    Save theme
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
