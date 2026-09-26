@props([
    'name',
    'store' => null,
    'quote' => null,
    'photo' => null,
])

@php
    // Dipakai kalau fotonya belum diunggah, supaya kartunya tidak terlihat
    // kosong: "Bu Sri" menjadi "BS".
    $initials = \Illuminate\Support\Str::of($name)
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $word): string => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($word, 0, 1)))
        ->implode('');
@endphp

{{--
    Satu testimoni di bagian "Kata Pemilik Toko". Isinya diambil dari menu
    Testimoni di panel admin.
--}}
<figure class="flex h-full flex-col rounded-3xl border border-brand-100 bg-white p-8 shadow-sm">
    <svg class="mb-6 h-8 w-8 text-brand-300" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M7.17 5A5.17 5.17 0 002 10.17V19h8.83v-8.83H6.17A3.5 3.5 0 019.67 6.7V5H7.17zm9 0A5.17 5.17 0 0011 10.17V19h8.83v-8.83h-4.66A3.5 3.5 0 0118.67 6.7V5h-2.5z" /></svg>

    @if (filled($quote))
        <blockquote class="mb-8 flex-1 text-sm leading-relaxed text-slate-600">{{ $quote }}</blockquote>
    @endif

    <figcaption class="flex items-center gap-4">
        @if (filled($photo))
            <img src="{{ $photo }}" alt="{{ $name }}" class="h-12 w-12 rounded-full object-cover">
        @else
            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-sm font-bold text-white">{{ $initials }}</span>
        @endif

        <span>
            <span class="block text-sm font-bold text-slate-900">{{ $name }}</span>
            @if (filled($store))
                <span class="block text-xs text-slate-500">{{ $store }}</span>
            @endif
        </span>
    </figcaption>
</figure>
