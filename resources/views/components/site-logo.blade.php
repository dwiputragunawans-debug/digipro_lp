@props([
    'url' => null,
    'adaptive' => null,
    'ratio' => null,
    'height' => '100%',
    'name' => null,
])

@php
    $setting = \App\Models\SiteSetting::current();

    $url ??= $setting->logoUrl();
    $adaptive = $adaptive === null ? (bool) $setting->logo_adaptive : (bool) $adaptive;
    $ratio ??= $setting->logoAspectRatio();
    $name ??= $setting->siteName();
@endphp

@if ($url)
    @if ($adaptive)
        {{--
            The logo is drawn as a mask that is filled with the current text
            colour, so it automatically takes on the colour of whatever it is
            placed on: dark on the light landing page header, white in the dark
            footer and the panel text colour (dark mode included) in the admin
            panel. This needs an image with a transparent background, because
            the transparent pixels are what get masked away.
        --}}
        <span
            role="img"
            aria-label="{{ $name }}"
            {{ $attributes->merge(['class' => 'inline-block']) }}
            style="height: {{ $height }}; aspect-ratio: {{ $ratio }}; background-color: currentColor; -webkit-mask-image: url('{{ $url }}'); mask-image: url('{{ $url }}'); -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; -webkit-mask-position: center; mask-position: center; -webkit-mask-size: contain; mask-size: contain;"
        ></span>
    @else
        <img
            src="{{ $url }}"
            alt="{{ $name }}"
            {{ $attributes->merge(['class' => 'w-auto']) }}
            style="height: {{ $height }};"
        />
    @endif
@endif
