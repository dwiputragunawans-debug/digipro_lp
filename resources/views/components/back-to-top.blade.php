{{--
    Tombol kembali ke atas di kiri bawah halaman. Class "flex" baru dipasang oleh
    resources/js/app.js setelah pengunjung menggulir halaman, jadi tombolnya
    tidak tampil dan tidak menghalangi konten selama masih di bagian atas.

    Warnanya bisa diganti pemanggilnya lewat prop colorClasses; halaman
    LajuKasir memakai gradasi teal, sedangkan landing page DigiPro Solution
    memakai warna bawaannya.
--}}
@props([
    'colorClasses' => 'bg-blue-600 hover:bg-blue-700',
])

<button
    type="button"
    data-back-to-top
    aria-label="Kembali ke atas halaman"
    class="fixed bottom-6 left-6 z-50 hidden h-11 w-11 items-center justify-center rounded-lg {{ $colorClasses }} text-white shadow-lg transition-colors print:hidden"
>
    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
    </svg>
</button>
