@props([
    'image' => null,
    'title' => null,
    'caption' => null,
])

{{--
    Satu tangkapan layar di bagian "Tampilan Aplikasi". Kalau gambarnya belum
    diunggah — atau berkasnya sudah tidak ada di disk — yang tampil adalah bingkai
    kosong dengan nama bagiannya, bukan gambar yang rusak.
--}}
<figure class="overflow-hidden rounded-3xl border border-brand-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-2xl hover:shadow-brand-500/10">
    <div class="bg-gradient-to-br from-brand-50 to-brand-100 p-6">
        @if (filled($image))
            <img src="{{ $image }}" alt="{{ $title ?: 'Tampilan aplikasi LajuKasir' }}" class="mx-auto h-72 w-auto rounded-2xl object-contain shadow-md">
        @else
            <div class="mx-auto flex h-72 items-center justify-center rounded-2xl border border-dashed border-brand-300 bg-white/60 px-4 text-center text-sm text-brand-600">
                {{ $title ?: 'Tangkapan layar aplikasi' }}
            </div>
        @endif
    </div>

    @if (filled($title) || filled($caption))
        <figcaption class="px-6 py-5">
            @if (filled($title))
                <p class="text-base font-bold text-slate-900">{{ $title }}</p>
            @endif

            @if (filled($caption))
                <p class="mt-1 text-sm text-slate-600">{{ $caption }}</p>
            @endif
        </figcaption>
    @endif
</figure>
