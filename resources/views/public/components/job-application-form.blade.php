@php
    $locale = $locale ?? app()->getLocale();
    $settings = $settings ?? [];

    $formIntro      = $settings['form_intro'][$locale] ?? __('messages.careers.form_intro');
    $privacyText    = $settings['form_privacy_text'][$locale] ?? __('messages.careers.form_labels.privacy', ['url' => '#']);
    $privacyPolicyUrl = route('pages.show', ['slug' => 'politica-privacitat']);

    $departments = [
        'fiscal'    => __('messages.careers.dept_fiscal'),
        'laboral'   => __('messages.careers.dept_laboral'),
        'comptable' => __('messages.careers.dept_comptable'),
        'altres'    => __('messages.careers.dept_altres'),
    ];
@endphp

{{-- Success flash --}}
@if(session('success'))
<div class="mb-8 p-6 bg-green-50 border border-green-200 rounded-2xl flex items-start gap-4"
     x-data x-init="$el.scrollIntoView({behavior:'smooth', block:'center'})">
    <span class="material-symbols-outlined text-green-600 text-[24px] mt-0.5">check_circle</span>
    <p class="text-green-800 font-medium">{{ session('success') }}</p>
</div>
@endif

@if($formIntro)
<p class="text-[#424751] text-[16px] leading-[1.7] mb-8">{{ $formIntro }}</p>
@endif

<form method="POST" action="{{ route('careers.store') }}" enctype="multipart/form-data"
      x-data="{ loading: false }" @submit="loading = true" novalidate>
    @csrf

    {{-- Errors summary --}}
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
        <ul class="text-red-700 text-sm space-y-1 list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-medium text-[#1E293B] mb-1">
                {{ __('messages.careers.form_labels.name') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   class="w-full px-4 py-3 border rounded-xl text-[#1E293B] text-sm focus:outline-none focus:ring-2 focus:ring-[#00346f] @error('name') border-red-400 bg-red-50 @else border-[#CBD5E1] @enderror"
                   required maxlength="100">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Last name --}}
        <div>
            <label for="last_name" class="block text-sm font-medium text-[#1E293B] mb-1">
                {{ __('messages.careers.form_labels.last_name') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}"
                   class="w-full px-4 py-3 border rounded-xl text-[#1E293B] text-sm focus:outline-none focus:ring-2 focus:ring-[#00346f] @error('last_name') border-red-400 bg-red-50 @else border-[#CBD5E1] @enderror"
                   required maxlength="100">
            @error('last_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-[#1E293B] mb-1">
                {{ __('messages.careers.form_labels.email') }} <span class="text-red-500">*</span>
            </label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="w-full px-4 py-3 border rounded-xl text-[#1E293B] text-sm focus:outline-none focus:ring-2 focus:ring-[#00346f] @error('email') border-red-400 bg-red-50 @else border-[#CBD5E1] @enderror"
                   required maxlength="255">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-[#1E293B] mb-1">
                {{ __('messages.careers.form_labels.phone') }}
            </label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                   class="w-full px-4 py-3 border rounded-xl text-[#1E293B] text-sm focus:outline-none focus:ring-2 focus:ring-[#00346f] @error('phone') border-red-400 bg-red-50 @else border-[#CBD5E1] @enderror"
                   maxlength="30">
            @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Department --}}
        <div class="sm:col-span-2">
            <label for="department" class="block text-sm font-medium text-[#1E293B] mb-1">
                {{ __('messages.careers.form_labels.department') }} <span class="text-red-500">*</span>
            </label>
            <select id="department" name="department"
                    class="w-full px-4 py-3 border rounded-xl text-[#1E293B] text-sm focus:outline-none focus:ring-2 focus:ring-[#00346f] @error('department') border-red-400 bg-red-50 @else border-[#CBD5E1] @enderror"
                    required>
                <option value="" disabled {{ old('department') ? '' : 'selected' }}>—</option>
                @foreach($departments as $value => $label)
                    <option value="{{ $value }}" {{ old('department') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('department')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Message --}}
        <div class="sm:col-span-2">
            <label for="message" class="block text-sm font-medium text-[#1E293B] mb-1">
                {{ __('messages.careers.form_labels.message') }} <span class="text-red-500">*</span>
            </label>
            <textarea id="message" name="message" rows="5" maxlength="2000"
                      class="w-full px-4 py-3 border rounded-xl text-[#1E293B] text-sm focus:outline-none focus:ring-2 focus:ring-[#00346f] resize-y @error('message') border-red-400 bg-red-50 @else border-[#CBD5E1] @enderror"
                      required>{{ old('message') }}</textarea>
            @error('message')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- CV Upload --}}
        <div class="sm:col-span-2">
            <label for="cv" class="block text-sm font-medium text-[#1E293B] mb-1">
                {{ __('messages.careers.form_labels.cv') }}
            </label>
            <input type="file" id="cv" name="cv" accept=".pdf,.doc,.docx"
                   class="w-full text-sm text-[#424751] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#00346f]/10 file:text-[#00346f] hover:file:bg-[#00346f]/20 @error('cv') text-red-600 @enderror">
            @error('cv')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Privacy --}}
        <div class="sm:col-span-2">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="privacy_accepted" value="1"
                       {{ old('privacy_accepted') ? 'checked' : '' }}
                       class="mt-1 w-4 h-4 rounded border-[#CBD5E1] text-[#00346f] focus:ring-[#00346f] @error('privacy_accepted') border-red-400 @enderror"
                       required>
                <span class="text-sm text-[#424751] leading-[1.6]">
                    {!! $privacyText !!}
                </span>
            </label>
            @error('privacy_accepted')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- Submit --}}
    <div class="mt-8">
        <button type="submit"
                :disabled="loading"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#00346f] text-white font-semibold px-10 py-4 rounded-xl hover:bg-[#00285a] disabled:opacity-60 disabled:cursor-not-allowed transition-colors duration-200">
            <span x-show="!loading">{{ __('messages.careers.form_labels.submit') }}</span>
            <span x-show="loading" class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ __('messages.careers.form_labels.submit') }}...
            </span>
        </button>
    </div>

</form>
