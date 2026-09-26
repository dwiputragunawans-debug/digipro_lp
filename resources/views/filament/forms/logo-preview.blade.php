{{--
    Live preview of the logo used in the Site Setting form. The surrounding
    admin panel does not load the landing page stylesheet, so everything here is
    styled inline to keep it independent from the compiled panel CSS.
--}}
@php
    // Header DigiPro Solution mewarnai ulang logonya mengikuti warna teks di
    // sekitarnya, sedangkan header LajuKasir selalu memakai warna asli logo.
    // Pemanggil view ini boleh menimpanya lewat $headerAdaptive dan
    // $headerCaption supaya pratinjaunya sama dengan halaman aslinya.
    $headerAdaptive ??= $adaptive;
    $headerCaption ??= 'Header landing page (background terang)';

    $tileStyle = 'display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; padding: 1.25rem 1.5rem;';
    $captionStyle = 'margin: 0.375rem 0 0; font-size: 0.75rem; opacity: 0.6;';
@endphp

@if ($logoUrl)
    <div style="display: grid; gap: 0.75rem;">
        <div>
            <div style="{{ $tileStyle }} background-color: #ffffff; border: 1px solid rgba(17, 24, 39, 0.1);">
                <x-site-logo
                    :url="$logoUrl"
                    :adaptive="$headerAdaptive"
                    :ratio="$ratio"
                    :name="$name"
                    height="2.75rem"
                    class="text-slate-900"
                />
            </div>
            <p style="{{ $captionStyle }}">{{ $headerCaption }}</p>
        </div>

        <div>
            <div style="{{ $tileStyle }} background-color: #020617;">
                <x-site-logo
                    :url="$logoUrl"
                    :adaptive="$adaptive"
                    :ratio="$ratio"
                    :name="$name"
                    height="2.75rem"
                    class="text-white"
                />
            </div>
            <p style="{{ $captionStyle }}">Footer landing page &amp; panel admin (background gelap)</p>
        </div>

        @if ($adaptive)
            <p style="margin: 0; font-size: 0.75rem; opacity: 0.6;">
                Mode warna adaptif memakai area transparan logo sebagai masker, sehingga bentuknya
                terisi penuh oleh warna di atasnya. Lampirkan PNG atau SVG berlatar transparan. Bila
                logo Anda berlatar penuh (misalnya JPG), matikan opsi ini agar warna aslinya dipakai.
            </p>
        @endif
    </div>
@else
    <p style="margin: 0; font-size: 0.75rem; opacity: 0.6;">
        Belum ada logo tersimpan. Pilih berkas lalu simpan untuk melihat pratinjaunya di sini.
    </p>
@endif
