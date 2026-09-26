@props([
    'icon' => \App\Support\Heroicons::FALLBACK,
    'title',
    'description' => null,
])

{{--
    Satu kartu pada bagian "Fitur Unggulan" halaman LajuKasir. Isinya diambil
    dari menu Fitur Unggulan di panel admin.
--}}
<div class="group rounded-3xl border border-brand-100 bg-white p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-2xl hover:shadow-brand-500/10">
    <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-100 to-brand-200 text-brand-700 transition-colors group-hover:from-brand-500 group-hover:to-brand-600 group-hover:text-white">
        <x-dynamic-component :component="$icon" class="h-7 w-7" />
    </div>

    <h3 class="mb-3 text-xl font-bold text-slate-900">{{ $title }}</h3>

    @if (filled($description))
        <p class="text-sm leading-relaxed text-slate-600">{{ $description }}</p>
    @endif
</div>
