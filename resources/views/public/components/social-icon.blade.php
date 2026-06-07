{{--
    Partial: social-icon
    Usage: @include('public.components.social-icon', ['platform' => 'linkedin'])
    Platforms: linkedin | twitter | instagram | facebook | youtube | tiktok | whatsapp
    Size:    pass a Tailwind text-size class, e.g. 'text-[18px]' (default: 'text-[20px]')
--}}
@php $size = $size ?? 'text-[20px]'; @endphp

@switch($platform ?? '')
    @case('linkedin')
        <i class="fa-brands fa-linkedin {{ $size }} leading-none" aria-hidden="true"></i>
        @break

    @case('twitter')
        <i class="fa-brands fa-x-twitter {{ $size }} leading-none" aria-hidden="true"></i>
        @break

    @case('instagram')
        <i class="fa-brands fa-instagram {{ $size }} leading-none" aria-hidden="true"></i>
        @break

    @case('facebook')
        <i class="fa-brands fa-facebook-f {{ $size }} leading-none" aria-hidden="true"></i>
        @break

    @case('youtube')
        <i class="fa-brands fa-youtube {{ $size }} leading-none" aria-hidden="true"></i>
        @break

    @case('tiktok')
        <i class="fa-brands fa-tiktok {{ $size }} leading-none" aria-hidden="true"></i>
        @break

    @case('whatsapp')
        <i class="fa-brands fa-whatsapp {{ $size }} leading-none" aria-hidden="true"></i>
        @break

    @default
        <span class="material-symbols-outlined {{ $size }}">link</span>
@endswitch
