@props([
    'number' => 1,
    'icon' => null,
    'title' => null,
    'description' => null,
])

{{--
    Satu langkah di bagian "Proses Mudah Memiliki Website". Lingkarannya berisi
    ikon dari menu Proses kalau ada, dan kembali menampilkan nomor langkah kalau
    ikonnya kosong.
--}}
<div class="relative z-10 flex flex-col items-center text-center group">
    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-lg border-4 border-blue-50 text-blue-600 mb-6 group-hover:scale-110 group-hover:border-blue-100 transition-transform duration-300">
        @if (filled($icon))
            <x-dynamic-component :component="$icon" class="w-10 h-10" />
        @else
            <span class="text-2xl font-bold">{{ $number }}</span>
        @endif
    </div>

    <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $title }}</h3>

    @if (filled($description))
        <p class="text-slate-500 text-sm">{{ $description }}</p>
    @endif
</div>
