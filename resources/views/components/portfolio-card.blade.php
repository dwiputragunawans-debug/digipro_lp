@props([
    'image' => null,
    'title' => null,
    'link' => null,
])

{{--
    Satu kartu di bagian "Karya Kami". Kalau gambarnya belum diunggah — atau
    filenya sudah tidak ada di disk — kotak abu-abu "Project Mockup" yang tampil,
    sama seperti desain awalnya.
--}}
<div class="group relative rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500">
    <div class="bg-blue-100/50 flex items-center justify-center p-8">
        @if (filled($image))
            <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-64 object-cover rounded-lg">
        @else
            <div class="w-full h-64 bg-slate-200 rounded-lg flex items-center justify-center text-slate-400">
                Project Mockup
            </div>
        @endif
    </div>

    @if (filled($title))
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-900/80 to-transparent p-6 pt-12">
            @if (filled($link))
                <a href="{{ $link }}" class="text-lg font-bold text-white hover:underline">{{ $title }}</a>
            @else
                <h3 class="text-lg font-bold text-white">{{ $title }}</h3>
            @endif
        </div>
    @endif
</div>
