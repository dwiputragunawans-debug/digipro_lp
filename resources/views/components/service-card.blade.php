@props([
    'icon' => \App\Support\Heroicons::FALLBACK,
    'title',
    'description' => null,
    'benefits' => [],
    'badge' => null,
    'highlighted' => false,
    'ctaText' => null,
    'ctaUrl' => null,
])

@php
    $highlighted = (bool) $highlighted;
    $benefits = collect($benefits)->filter(fn ($benefit): bool => filled($benefit))->values();
    $button = filled($ctaText) ? $ctaText : ($highlighted ? 'Hubungi Kami' : 'Selengkapnya');
@endphp

{{--
    Satu kartu di bagian "Solusi Digital untuk UMKM". Kartu dengan
    highlighted = true digambar gelap, dan badge diisi teks seperti "POPULER".
--}}
<div @class([
    'rounded-3xl p-8 flex flex-col',
    'relative overflow-hidden' => filled($badge),
    'bg-white border border-slate-200 hover:border-blue-500 hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-300' => ! $highlighted,
    'bg-slate-900 border border-slate-800 shadow-xl transform hover:-translate-y-2 transition-all duration-300' => $highlighted,
])>
    @if (filled($badge))
        <div class="absolute top-0 right-0 bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">{{ $badge }}</div>
    @endif

    <div @class([
        'w-14 h-14 rounded-xl flex items-center justify-center mb-6',
        'bg-blue-50 text-blue-600' => ! $highlighted,
        'bg-blue-900/50 text-blue-400 border border-blue-800' => $highlighted,
    ])>
        <x-dynamic-component :component="$icon" class="w-7 h-7" />
    </div>

    <h3 @class(['text-2xl font-bold mb-2', 'text-slate-900' => ! $highlighted, 'text-white' => $highlighted])>
        {{ $title }}
    </h3>

    @if (filled($description))
        <p @class(['mb-8 text-sm', 'text-slate-500' => ! $highlighted, 'text-slate-400' => $highlighted])>{{ $description }}</p>
    @endif

    <ul class="space-y-4 mb-8 flex-1">
        @foreach ($benefits as $benefit)
            <li class="flex items-start">
                <svg @class(['w-5 h-5 mr-3 shrink-0', 'text-emerald-500' => ! $highlighted, 'text-blue-400' => $highlighted]) fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span @class(['text-sm', 'text-slate-600' => ! $highlighted, 'text-slate-300' => $highlighted])>{{ $benefit }}</span>
            </li>
        @endforeach
    </ul>

    <a href="{{ filled($ctaUrl) ? $ctaUrl : '#' }}" @class([
        'block w-full py-3 px-4 font-semibold rounded-xl transition-colors text-center',
        'bg-slate-100 text-slate-800 hover:bg-slate-200' => ! $highlighted,
        'bg-blue-600 text-white hover:bg-blue-500' => $highlighted,
    ])>{{ $button }}</a>
</div>
