<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">New form</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-white">Select a template</h1>
            <p class="mt-2 text-gray-500 text-sm">Start from scratch or pick a ready-made template</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

            {{-- Blank form --}}
            <form method="POST" action="{{ route('forms.store') }}">
                @csrf
                <input type="hidden" name="template" value="blank">
                <button type="submit" class="w-full text-left group">
                    <div class="bg-[#18181f] border border-white/8 rounded-2xl overflow-hidden hover:border-indigo-500/40 transition-all duration-200 hover:shadow-lg hover:shadow-indigo-500/5">
                        <div class="h-40 flex items-center justify-center bg-white/[0.02]">
                            <svg class="w-10 h-10 text-gray-600 group-hover:text-gray-400 transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                        </div>
                        <div class="px-4 py-3 border-t border-white/5">
                            <p class="text-sm font-semibold text-white">Blank form</p>
                            <p class="text-xs text-gray-500 mt-0.5">Start from scratch</p>
                        </div>
                    </div>
                </button>
            </form>

            {{-- Customer Feedback --}}
            <form method="POST" action="{{ route('forms.store') }}">
                @csrf
                <input type="hidden" name="template" value="customer_feedback">
                <button type="submit" class="w-full text-left group">
                    <div class="bg-[#18181f] border border-white/8 rounded-2xl overflow-hidden hover:border-indigo-500/40 transition-all duration-200 hover:shadow-lg hover:shadow-indigo-500/5">
                        <div class="h-40 relative overflow-hidden" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 px-6">
                                <div class="text-white/90 text-sm font-semibold text-center">How would you rate us?</div>
                                <div class="flex gap-1 mt-1">
                                    @for ($i = 0; $i < 5; $i++)
                                        <svg class="w-5 h-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                                <div class="w-20 h-6 bg-white/20 rounded mt-1 text-[10px] text-white/70 flex items-center justify-center font-medium">Submit →</div>
                            </div>
                        </div>
                        <div class="px-4 py-3 border-t border-white/5">
                            <p class="text-sm font-semibold text-white">Customer feedback</p>
                            <p class="text-xs text-gray-500 mt-0.5">Name, email, rating, feedback</p>
                        </div>
                    </div>
                </button>
            </form>

            {{-- Job Application --}}
            <form method="POST" action="{{ route('forms.store') }}">
                @csrf
                <input type="hidden" name="template" value="job_application">
                <button type="submit" class="w-full text-left group">
                    <div class="bg-[#18181f] border border-white/8 rounded-2xl overflow-hidden hover:border-indigo-500/40 transition-all duration-200 hover:shadow-lg hover:shadow-indigo-500/5">
                        <div class="h-40 relative overflow-hidden" style="background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);">
                            <div class="absolute inset-0 flex flex-col justify-center gap-1.5 px-6">
                                <div class="text-white/90 text-xs font-medium">Full name</div>
                                <div class="h-5 bg-white/20 rounded w-3/4"></div>
                                <div class="text-white/90 text-xs font-medium mt-1">Position</div>
                                <div class="h-5 bg-white/20 rounded w-1/2"></div>
                                <div class="text-white/90 text-xs font-medium mt-1">Experience</div>
                                <div class="flex gap-1">
                                    <div class="h-5 bg-white/30 rounded-full px-2 text-[10px] text-white flex items-center">1–3 yrs</div>
                                    <div class="h-5 bg-white/10 rounded-full px-2 text-[10px] text-white/60 flex items-center">3–5 yrs</div>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 border-t border-white/5">
                            <p class="text-sm font-semibold text-white">Job application</p>
                            <p class="text-xs text-gray-500 mt-0.5">Name, email, position, experience</p>
                        </div>
                    </div>
                </button>
            </form>

            {{-- Contact Form --}}
            <form method="POST" action="{{ route('forms.store') }}">
                @csrf
                <input type="hidden" name="template" value="contact_form">
                <button type="submit" class="w-full text-left group">
                    <div class="bg-[#18181f] border border-white/8 rounded-2xl overflow-hidden hover:border-indigo-500/40 transition-all duration-200 hover:shadow-lg hover:shadow-indigo-500/5">
                        <div class="h-40 relative overflow-hidden" style="background: linear-gradient(135deg, #10b981 0%, #0ea5e9 100%);">
                            <div class="absolute inset-0 flex flex-col justify-center gap-2 px-6">
                                <div class="text-white font-semibold text-sm">Your message</div>
                                <div class="bg-white/15 rounded-lg p-2 text-[10px] text-white/60 leading-relaxed">
                                    How can we help you today?<br>Feel free to reach out...
                                </div>
                                <div class="w-16 h-5 bg-white/25 rounded text-[10px] text-white flex items-center justify-center font-medium">Send →</div>
                            </div>
                        </div>
                        <div class="px-4 py-3 border-t border-white/5">
                            <p class="text-sm font-semibold text-white">Contact form</p>
                            <p class="text-xs text-gray-500 mt-0.5">Name, email, message</p>
                        </div>
                    </div>
                </button>
            </form>

            {{-- NPS Survey --}}
            <form method="POST" action="{{ route('forms.store') }}">
                @csrf
                <input type="hidden" name="template" value="nps_survey">
                <button type="submit" class="w-full text-left group">
                    <div class="bg-[#18181f] border border-white/8 rounded-2xl overflow-hidden hover:border-indigo-500/40 transition-all duration-200 hover:shadow-lg hover:shadow-indigo-500/5">
                        <div class="h-40 relative overflow-hidden" style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);">
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 px-4">
                                <div class="text-white/90 text-xs font-medium text-center">How likely to recommend us?</div>
                                <div class="flex gap-0.5">
                                    @for ($i = 1; $i <= 10; $i++)
                                        <div class="w-5 h-5 rounded text-[9px] font-bold flex items-center justify-center {{ $i <= 7 ? 'bg-white/20 text-white/70' : 'bg-white text-amber-500' }}">{{ $i }}</div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 border-t border-white/5">
                            <p class="text-sm font-semibold text-white">NPS survey</p>
                            <p class="text-xs text-gray-500 mt-0.5">Net Promoter Score + reason</p>
                        </div>
                    </div>
                </button>
            </form>

            {{-- Exit Survey --}}
            <form method="POST" action="{{ route('forms.store') }}">
                @csrf
                <input type="hidden" name="template" value="exit_survey">
                <button type="submit" class="w-full text-left group">
                    <div class="bg-[#18181f] border border-white/8 rounded-2xl overflow-hidden hover:border-indigo-500/40 transition-all duration-200 hover:shadow-lg hover:shadow-indigo-500/5">
                        <div class="h-40 relative overflow-hidden" style="background: linear-gradient(135deg, #ec4899 0%, #f59e0b 100%);">
                            <div class="absolute inset-0 flex flex-col justify-center gap-1.5 px-6">
                                <div class="text-white/90 text-xs font-medium">Why are you leaving?</div>
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full border border-white/40"></div><div class="text-[10px] text-white/70">Too expensive</div></div>
                                    <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full bg-white border border-white"></div><div class="text-[10px] text-white font-medium">Missing features</div></div>
                                    <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full border border-white/40"></div><div class="text-[10px] text-white/70">Better alternative</div></div>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 border-t border-white/5">
                            <p class="text-sm font-semibold text-white">Exit survey</p>
                            <p class="text-xs text-gray-500 mt-0.5">Reason, rating, open feedback</p>
                        </div>
                    </div>
                </button>
            </form>

        </div>
    </div>
</x-app-layout>
