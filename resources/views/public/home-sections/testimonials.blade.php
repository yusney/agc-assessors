@php
    $locale = app()->getLocale();
    $titles = [
        'ca' => 'Què diuen els nostres clients',
        'es' => 'Qué dicen nuestros clientes',
        'en' => 'What our clients say',
    ];
    $title = $section->localized('title') ?: ($titles[$locale] ?? $titles['ca']);

    $testimonials = [
        [
            'quote' => [
                'ca' => 'AGC Assessors ha transformat la gestió fiscal de la nostra empresa. Finalment tenim tranquil·litat.',
                'es' => 'AGC Assessors ha transformado la gestión fiscal de nuestra empresa. Por fin tenemos tranquilidad.',
                'en' => 'AGC Assessors has transformed our company\'s tax management. We finally have peace of mind.',
            ],
            'name'    => 'Maria G.',
            'role'    => ['ca' => 'Gestora de pime', 'es' => 'Gestora de pyme', 'en' => 'SME Manager'],
            'company' => 'Distribucions Montalt',
            'initials' => 'MG',
        ],
        [
            'quote' => [
                'ca' => 'Professionalitat, rapidesa i tracte personal. Recomano AGC Assessors sense dubtar.',
                'es' => 'Profesionalidad, rapidez y trato personal. Recomiendo AGC Assessors sin dudar.',
                'en' => 'Professionalism, speed and personal touch. I recommend AGC Assessors without hesitation.',
            ],
            'name'    => 'Pere R.',
            'role'    => ['ca' => 'Autònom', 'es' => 'Autónomo', 'en' => 'Freelancer'],
            'company' => 'Disseny Roca',
            'initials' => 'PR',
        ],
        [
            'quote' => [
                'ca' => 'Portem 5 anys amb ells i mai hem tingut cap problema amb Hisenda.',
                'es' => 'Llevamos 5 años con ellos y nunca hemos tenido ningún problema con Hacienda.',
                'en' => 'We have been with them for 5 years and have never had any issues with the tax authorities.',
            ],
            'name'    => 'Anna S.',
            'role'    => ['ca' => 'Directora d\'empresa', 'es' => 'Directora de empresa', 'en' => 'Company Director'],
            'company' => 'Grup Solà',
            'initials' => 'AS',
        ],
    ];
@endphp

<section class="w-full py-16 md:py-24 bg-[#F8FAFC]">
    <div class="max-w-[1280px] mx-auto px-6 md:px-8">

        {{-- Section header --}}
        <div class="text-center mb-12 md:mb-16">
            <p class="text-[13px] font-semibold uppercase tracking-[0.22em] text-[#00B4D8] mb-4">
                {{ app()->getLocale() === 'ca' ? 'Testimonis' : (app()->getLocale() === 'es' ? 'Testimonios' : 'Testimonials') }}
            </p>
            <h2 class="font-headline text-[36px] md:text-[48px] text-[#1E293B] leading-[1.05] tracking-tight font-semibold">
                {{ $title }}
            </h2>
        </div>

        {{-- Testimonial cards grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            @foreach($testimonials as $t)
                <article class="flex flex-col bg-white rounded-2xl shadow-sm border border-[#E2E8F0]/70 p-8 hover:shadow-md transition-shadow duration-200">

                    {{-- Stars --}}
                    <div class="flex gap-1 mb-6" aria-label="5 estrelles" role="img">
                        @for($i = 0; $i < 5; $i++)
                            <span class="material-symbols-outlined text-[#F59E0B] text-[20px]" aria-hidden="true"
                                  style="font-variation-settings: 'FILL' 1">star</span>
                        @endfor
                    </div>

                    {{-- Quote --}}
                    <blockquote class="flex-1 text-[17px] text-[#475569] leading-relaxed font-light mb-8">
                        "{{ $t['quote'][$locale] ?? $t['quote']['ca'] }}"
                    </blockquote>

                    {{-- Person --}}
                    <footer class="flex items-center gap-4">
                        <div class="w-11 h-11 rounded-full bg-[#00346f] flex items-center justify-center shrink-0" aria-hidden="true">
                            <span class="text-white text-[13px] font-semibold tracking-wide">{{ $t['initials'] }}</span>
                        </div>
                        <div>
                            <p class="text-[15px] font-semibold text-[#1E293B]">{{ $t['name'] }}</p>
                            <p class="text-[13px] text-[#64748B]">
                                {{ $t['role'][$locale] ?? $t['role']['ca'] }}
                                <span class="mx-1 text-[#CBD5E1]">·</span>
                                {{ $t['company'] }}
                            </p>
                        </div>
                    </footer>

                </article>
            @endforeach
        </div>

    </div>
</section>
