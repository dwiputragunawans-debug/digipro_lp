@props([
    'url',
    'greeting' => null,
    'ctaText' => 'Mulai Konsultasi',
])

{{--
    Tombol WhatsApp mengambang di kanan bawah landing page. Menekan tombol
    bulatnya membuka kartu obrolan kecil berisi sapaan, dan tombol "Mulai
    Konsultasi" di dalam kartu membuka WhatsApp dengan pesan yang sudah terisi.
    Nomor, sapaan dan pesannya diatur di menu Pengaturan Situs.
--}}
<div data-whatsapp-widget class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-4 print:hidden">
    <div
        id="whatsapp-chat-card"
        data-whatsapp-panel
        role="dialog"
        aria-label="Obrolan WhatsApp"
        aria-hidden="true"
        class="hidden w-80 max-w-[calc(100vw_-_3rem)] overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-900/10"
    >
        <div class="flex items-center justify-between bg-[#25D366] px-5 py-4">
            <div class="flex items-center gap-2 text-white">
                <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.898-4.45 9.898-9.898 0-5.448-4.45-9.897-9.898-9.897-5.448 0-9.898 4.45-9.898 9.897 0 2.115.597 3.716 1.591 5.392l-1.121 4.1 4.236-1.186zm10.514-4.249c-.273-.139-1.613-.796-1.863-.888-.25-.093-.432-.139-.614.139-.182.278-.705.888-.864 1.073-.159.185-.318.209-.591.07-2.613-1.332-4.041-2.909-5.595-5.503-.159-.278.149-.247.669-.748.24-.24.364-.37.495-.61.131-.24.066-.453-.027-.638-.093-.185-.614-1.482-.841-2.029-.22-.533-.443-.46-.614-.467-.168-.007-.363-.009-.556-.009-.193 0-.505.071-.773.349-.268.278-1.023 1-1.023 2.436s1.045 2.825 1.19 3.01c.145.185 2.053 3.136 4.97 4.398 2.096.904 2.868 1.055 3.906.883.953-.158 2.836-1.157 3.238-2.276.402-1.119.402-2.078.282-2.276-.12-.198-.433-.306-.706-.445z"/></svg>
                <span class="text-lg font-semibold">WhatsApp</span>
            </div>
            <button
                type="button"
                data-whatsapp-close
                aria-label="Tutup obrolan WhatsApp"
                class="grid h-9 w-9 place-items-center rounded-full bg-black/10 text-white transition-colors hover:bg-black/20"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="space-y-4 bg-slate-50 px-5 py-6">
            <p class="rounded-2xl rounded-tl-sm bg-white px-4 py-3 text-slate-700 shadow-sm">{{ $greeting }}</p>

            <a
                href="{{ $url }}"
                data-whatsapp-click
                target="_blank"
                rel="noopener"
                class="flex w-full items-center justify-center gap-2 rounded-full bg-[#25D366] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1DA851]"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.898-4.45 9.898-9.898 0-5.448-4.45-9.897-9.898-9.897-5.448 0-9.898 4.45-9.898 9.897 0 2.115.597 3.716 1.591 5.392l-1.121 4.1 4.236-1.186zm10.514-4.249c-.273-.139-1.613-.796-1.863-.888-.25-.093-.432-.139-.614.139-.182.278-.705.888-.864 1.073-.159.185-.318.209-.591.07-2.613-1.332-4.041-2.909-5.595-5.503-.159-.278.149-.247.669-.748.24-.24.364-.37.495-.61.131-.24.066-.453-.027-.638-.093-.185-.614-1.482-.841-2.029-.22-.533-.443-.46-.614-.467-.168-.007-.363-.009-.556-.009-.193 0-.505.071-.773.349-.268.278-1.023 1-1.023 2.436s1.045 2.825 1.19 3.01c.145.185 2.053 3.136 4.97 4.398 2.096.904 2.868 1.055 3.906.883.953-.158 2.836-1.157 3.238-2.276.402-1.119.402-2.078.282-2.276-.12-.198-.433-.306-.706-.445z"/></svg>
                {{ $ctaText }}
            </a>
        </div>
    </div>

    <button
        type="button"
        data-whatsapp-toggle
        aria-expanded="false"
        aria-controls="whatsapp-chat-card"
        aria-label="Hubungi kami lewat WhatsApp"
        class="grid h-14 w-14 place-items-center rounded-full bg-[#25D366] text-white shadow-lg shadow-emerald-600/30 transition-transform duration-300 hover:scale-110"
    >
        <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.898-4.45 9.898-9.898 0-5.448-4.45-9.897-9.898-9.897-5.448 0-9.898 4.45-9.898 9.897 0 2.115.597 3.716 1.591 5.392l-1.121 4.1 4.236-1.186zm10.514-4.249c-.273-.139-1.613-.796-1.863-.888-.25-.093-.432-.139-.614.139-.182.278-.705.888-.864 1.073-.159.185-.318.209-.591.07-2.613-1.332-4.041-2.909-5.595-5.503-.159-.278.149-.247.669-.748.24-.24.364-.37.495-.61.131-.24.066-.453-.027-.638-.093-.185-.614-1.482-.841-2.029-.22-.533-.443-.46-.614-.467-.168-.007-.363-.009-.556-.009-.193 0-.505.071-.773.349-.268.278-1.023 1-1.023 2.436s1.045 2.825 1.19 3.01c.145.185 2.053 3.136 4.97 4.398 2.096.904 2.868 1.055 3.906.883.953-.158 2.836-1.157 3.238-2.276.402-1.119.402-2.078.282-2.276-.12-.198-.433-.306-.706-.445z"/></svg>
    </button>
</div>
