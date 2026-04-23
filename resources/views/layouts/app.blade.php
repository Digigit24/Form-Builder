<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Form Builder') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', system-ui, sans-serif; }
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="antialiased bg-[#0f0f12] text-gray-200">
        <div class="min-h-screen flex"
             x-data="{ sidebarOpen: {{ request()->routeIs('forms.edit', 'forms.responses') ? 'false' : 'true' }} }">

            {{-- Sidebar --}}
            <aside class="bg-[#18181f] border-r border-white/5 flex flex-col fixed inset-y-0 left-0 z-30 overflow-hidden transition-[width] duration-200 ease-in-out"
                   :class="sidebarOpen ? 'w-[240px]' : 'w-[60px]'">

                {{-- Header --}}
                <div class="flex items-center border-b border-white/5 h-[69px] shrink-0"
                     :class="sidebarOpen ? 'px-5 justify-between' : 'justify-center px-2'">
                    <div x-show="sidebarOpen" x-cloak class="min-w-0 overflow-hidden">
                        <div class="text-lg font-bold text-white tracking-tight whitespace-nowrap">Form Builder</div>
                        <div class="text-xs text-gray-500 mt-0.5 truncate">{{ auth()->user()->tenant->name ?? '' }}</div>
                    </div>
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="p-1.5 rounded-md text-gray-400 hover:text-white hover:bg-white/5 shrink-0"
                            :title="sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'">
                        {{-- Double left chevron (collapse) --}}
                        <svg x-show="sidebarOpen" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5"/>
                        </svg>
                        {{-- Double right chevron (expand) --}}
                        <svg x-show="!sidebarOpen" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 4.5l7.5 7.5-7.5 7.5m-6-15l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </button>
                </div>

                {{-- Nav --}}
                <nav class="flex-1 px-3 py-4 space-y-1">
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center py-2 rounded-lg text-sm {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                       :class="sidebarOpen ? 'gap-3 px-3' : 'justify-center px-2'"
                       :title="!sidebarOpen ? 'Forms' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z"/></svg>
                        <span x-show="sidebarOpen" x-cloak class="whitespace-nowrap">Forms</span>
                    </a>
                    <a href="{{ route('theme.edit') }}"
                       class="flex items-center py-2 rounded-lg text-sm {{ request()->routeIs('theme.*') ? 'bg-white/10 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                       :class="sidebarOpen ? 'gap-3 px-3' : 'justify-center px-2'"
                       :title="!sidebarOpen ? 'Theme' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.25"/></svg>
                        <span x-show="sidebarOpen" x-cloak class="whitespace-nowrap">Theme</span>
                    </a>
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center py-2 rounded-lg text-sm {{ request()->routeIs('profile.*') ? 'bg-white/10 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                       :class="sidebarOpen ? 'gap-3 px-3' : 'justify-center px-2'"
                       :title="!sidebarOpen ? 'Profile' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                        <span x-show="sidebarOpen" x-cloak class="whitespace-nowrap">Profile</span>
                    </a>
                </nav>

                {{-- User section --}}
                <div class="py-4 border-t border-white/5 shrink-0"
                     :class="sidebarOpen ? 'px-3' : 'px-2'">
                    <div class="flex items-center gap-3"
                         :class="sidebarOpen ? 'px-3' : 'justify-center'">
                        <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-bold text-white shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div x-show="sidebarOpen" x-cloak class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <form x-show="sidebarOpen" x-cloak method="POST" action="{{ route('logout') }}" class="mt-3 px-3">
                        @csrf
                        <button type="submit" class="text-xs text-gray-500 hover:text-white">Sign out</button>
                    </form>
                </div>
            </aside>

            {{-- Main content --}}
            <div class="flex-1 transition-[margin] duration-200 ease-in-out"
                 :class="sidebarOpen ? 'ml-[240px]' : 'ml-[60px]'">
                @isset($header)
                    <header class="border-b border-white/5 bg-[#18181f]/80 backdrop-blur-sm sticky top-0 z-20">
                        <div class="max-w-7xl mx-auto px-6 py-4">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
