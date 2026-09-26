@props([
    'number' => 1,
    'icon' => null,
    'title' => null,
    'description' => null,
])

{{--
    Satu langkah pada bagian "Cara Mulai". Lingkarannya berisi ikon dari menu
    Cara Mulai kalau ada, dan kembali menampilkan nomor langkah kalau ikonnya
    kosong.
--}}
<div class="relative z-10 flex flex-col items-center text-center group">
    <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full border-4 border-brand-100 bg-white text-brand-700 shadow-lg transition-transform duration-300 group-hover:scale-110 group-hover:border-brand-200">
        @if (filled($icon))
            <x-dynamic-component :component="$icon" class="h-10 w-10" />
        @else
            <span class="text-2xl font-bold">{{ $number }}</span>
        @endif
    </div>

    <h3 class="mb-2 text-xl font-bold text-slate-900">{{ $title }}</h3>

    @if (filled($description))
        <p class="text-sm text-slate-500">{{ $description }}</p>
    @endif
</div>
