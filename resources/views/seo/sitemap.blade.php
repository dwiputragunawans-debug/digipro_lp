<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
{{-- Sitemap hanya berisi halaman publik landing page. Isinya berasal dari
     App\Support\Seo\Sitemap, sehingga alamat yang dipakai selalu sama dengan
     tag canonical di halaman. --}}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        <lastmod>{{ $url['lastmod'] }}</lastmod>
        <changefreq>{{ $url['changefreq'] }}</changefreq>
        <priority>{{ $url['priority'] }}</priority>
    </url>
@endforeach
</urlset>
