@props([
    'eyebrow' => null,
    'heading',
    'subheading' => null,
])

{{--
    Judul dan subjudul satu bagian halaman LajuKasir. Dipakai semua bagian
    supaya jarak dan ukuran hurufnya konsisten.
--}}
<div class="mx-auto mb-16 max-w-3xl text-center">
    @if (filled($eyebrow))
        <p class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-brand-600">{{ $eyebrow }}</p>
    @endif

    <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl">{{ $heading }}</h2>

    @if (filled($subheading))
        <p class="mt-4 text-lg text-slate-600">{{ $subheading }}</p>
    @endif
</div>
