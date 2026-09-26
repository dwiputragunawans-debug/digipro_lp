@props([
    'question',
    'answer' => null,
])

{{--
    Satu tanya jawab di bagian "Pertanyaan Umum".

    Dibuka dan ditutup memakai <details> bawaan browser, jadi tidak perlu
    JavaScript tambahan dan tetap bisa dipakai walau skripnya gagal dimuat.
--}}
<details class="group rounded-2xl border border-brand-100 bg-white px-6 py-5 transition-all duration-300 open:border-brand-300 open:shadow-lg open:shadow-brand-500/5">
    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-semibold text-slate-900 sm:text-lg">
        <span>{{ $question }}</span>
        <svg class="h-5 w-5 shrink-0 text-brand-600 transition-transform duration-300 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
    </summary>

    @if (filled($answer))
        <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ $answer }}</p>
    @endif
</details>
