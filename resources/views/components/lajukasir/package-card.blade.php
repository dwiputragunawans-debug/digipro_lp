@props([
    'name',
    'price' => null,
    'priceUnit' => null,
    'description' => null,
    'badge' => null,
    'benefits' => [],
    'highlighted' => false,
    'ctaText' => null,
    'ctaUrl' => null,
    'track' => false,
])

@php
    $highlighted = (bool) $highlighted;
    $benefits = collect($benefits)->filter(fn ($benefit): bool => filled($benefit))->values();
    $button = filled($ctaText) ? $ctaText : ($highlighted ? 'Pilih Paket Ini' : 'Tanya Dulu');
@endphp

{{--
    Satu kartu di bagian "Paket Harga" halaman LajuKasir. Paket dengan
    highlighted = true digambar dengan gradasi gelap yang sama seperti bagian
    pembuka halaman — biru tua menuju hijau teal, dua warna logonya — dan badge
    diisi teks seperti "TERLARIS".
--}}
<div @class([
    'relative flex flex-col rounded-3xl p-8',
    'border border-brand-200 bg-white transition-all duration-300 hover:border-brand-400 hover:shadow-2xl hover:shadow-brand-500/10' => ! $highlighted,
    'border border-ink-900 bg-gradient-to-br from-ink-950 via-ink-900 to-brand-700 shadow-xl transition-all duration-300 hover:-translate-y-2' => $highlighted,
])>
    @if (filled($badge))
        <div class="absolute right-0 top-0 rounded-bl-2xl bg-gradient-to-r from-brand-500 to-brand-400 px-3 py-1 text-xs font-bold text-white">{{ $badge }}</div>
    @endif

    <h3 @class(['text-2xl font-bold', 'text-slate-900' => ! $highlighted, 'text-white' => $highlighted])>{{ $name }}</h3>

    @if (filled($price))
        <p class="mt-4 flex flex-wrap items-baseline gap-x-2">
            <span @class(['text-3xl font-extrabold tracking-tight', 'text-brand-700' => ! $highlighted, 'text-white' => $highlighted])>{{ $price }}</span>
            @if (filled($priceUnit))
                <span @class(['text-sm font-medium', 'text-slate-500' => ! $highlighted, 'text-brand-100' => $highlighted])>{{ $priceUnit }}</span>
            @endif
        </p>
    @endif

    @if (filled($description))
        <p @class(['mt-4 text-sm leading-relaxed', 'text-slate-600' => ! $highlighted, 'text-brand-100' => $highlighted])>{{ $description }}</p>
    @endif

    <ul class="mb-8 mt-8 flex-1 space-y-4">
        @foreach ($benefits as $benefit)
            <li class="flex items-start">
                <svg @class(['mr-3 h-5 w-5 shrink-0', 'text-brand-500' => ! $highlighted, 'text-brand-200' => $highlighted]) fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span @class(['text-sm', 'text-slate-600' => ! $highlighted, 'text-brand-50' => $highlighted])>{{ $benefit }}</span>
            </li>
        @endforeach
    </ul>

    <a
        href="{{ filled($ctaUrl) ? $ctaUrl : '#' }}"
        @if ($track) data-whatsapp-click target="_blank" rel="noopener" @endif
        @class([
            'block w-full rounded-xl px-4 py-3 text-center font-semibold transition-all duration-300',
            'bg-brand-50 text-brand-800 hover:bg-brand-100' => ! $highlighted,
            'bg-white text-brand-800 hover:bg-brand-50 hover:shadow-lg' => $highlighted,
        ])
    >{{ $button }}</a>
</div>
