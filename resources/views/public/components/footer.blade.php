<footer class="bg-[#ededf5] w-full py-16 border-t border-[#E2E8F0]">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full px-6 md:px-8 max-w-[1280px] mx-auto items-center">
        <div>
            <div class="font-headline font-bold text-2xl text-[#1E293B] mb-3">
                AGC<span class="text-[#00346f]">.</span>
            </div>
            <p class="text-[15px] text-[#64748B]">
                &copy; {{ date('Y') }} AGC Assessors. {{ __('messages.footer.rights') }}
            </p>
        </div>
        <nav class="flex flex-wrap gap-x-8 gap-y-3 md:justify-end" aria-label="Footer navigation">
            <a href="{{ url('/avis-legal') }}" class="text-[15px] text-[#64748B] hover:text-[#1E293B] transition-colors underline-offset-4 hover:underline">{{ __('messages.footer.legal') }}</a>
            <a href="{{ url('/politica-privacitat') }}" class="text-[15px] text-[#64748B] hover:text-[#1E293B] transition-colors underline-offset-4 hover:underline">{{ __('messages.footer.privacy') }}</a>
            <a href="{{ url('/cookies') }}" class="text-[15px] text-[#64748B] hover:text-[#1E293B] transition-colors underline-offset-4 hover:underline">{{ __('messages.footer.cookies') }}</a>
            <a href="{{ url('/contacte') }}" class="text-[15px] text-[#64748B] hover:text-[#1E293B] transition-colors underline-offset-4 hover:underline">{{ __('messages.nav.contact') }}</a>
        </nav>
    </div>
</footer>
