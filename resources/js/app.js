// Perilaku landing page yang butuh sedikit JavaScript: menu navigasi untuk
// ponsel dan tablet, tombol mengambang (kartu obrolan WhatsApp dan tombol
// kembali ke atas), serta pelaporan klik WhatsApp ke dasbor analitik.
//
// Semuanya opsional: setiap fungsi berhenti dengan tenang kalau elemen yang
// dibutuhkannya memang tidak dirender.

function initWhatsAppWidget() {
    const widget = document.querySelector('[data-whatsapp-widget]');

    if (!widget) {
        return;
    }

    const panel = widget.querySelector('[data-whatsapp-panel]');
    const toggle = widget.querySelector('[data-whatsapp-toggle]');
    const close = widget.querySelector('[data-whatsapp-close]');

    if (!panel || !toggle) {
        return;
    }

    // Kartu obrolan disembunyikan lewat class "hidden" supaya pengunjung yang
    // belum menekan tombolnya tidak ikut melihat kartunya.
    const setOpen = (open) => {
        panel.classList.toggle('hidden', !open);
        panel.classList.toggle('block', open);
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    const isOpen = () => !panel.classList.contains('hidden');

    toggle.addEventListener('click', () => setOpen(!isOpen()));

    if (close) {
        close.addEventListener('click', () => setOpen(false));
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });

    // Menekan di luar kartu obrolan juga menutupnya.
    document.addEventListener('click', (event) => {
        if (isOpen() && !widget.contains(event.target)) {
            setOpen(false);
        }
    });
}

/**
 * Menu navigasi untuk ponsel dan tablet.
 *
 * Tombolnya hanya tampil di bawah lebar lg (1024px). Di layar lebar menu
 * mendatar yang dipakai, jadi panelnya selalu ditutup lagi saat jendela
 * diperbesar — kalau tidak, menunya bisa menggantung di bawah header.
 */
function initMobileNav() {
    const nav = document.querySelector('[data-nav]');

    if (!nav) {
        return;
    }

    const panel = nav.querySelector('[data-nav-panel]');
    const toggle = nav.querySelector('[data-nav-toggle]');

    if (!panel || !toggle) {
        return;
    }

    const iconOpen = toggle.querySelector('[data-nav-icon-open]');
    const iconClose = toggle.querySelector('[data-nav-icon-close]');
    const desktop = window.matchMedia('(min-width: 1024px)');

    // Ikon dan labelnya berganti mengikuti keadaannya, supaya pengunjung yang
    // memakai pembaca layar tetap tahu menu itu terbuka atau tertutup.
    const setOpen = (open) => {
        panel.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');

        if (iconOpen) {
            iconOpen.classList.toggle('hidden', open);
        }

        if (iconClose) {
            iconClose.classList.toggle('hidden', !open);
        }
    };

    const isOpen = () => !panel.classList.contains('hidden');

    toggle.addEventListener('click', () => setOpen(!isOpen()));

    // Menekan salah satu menu menutup panelnya, supaya pengunjung langsung
    // melihat bagian yang dituju.
    panel.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });

    // Menekan di luar header juga menutup menunya.
    document.addEventListener('click', (event) => {
        if (isOpen() && !nav.contains(event.target)) {
            setOpen(false);
        }
    });

    const closeOnDesktop = () => {
        if (desktop.matches) {
            setOpen(false);
        }
    };

    if (typeof desktop.addEventListener === 'function') {
        desktop.addEventListener('change', closeOnDesktop);
    } else {
        // Safari lama belum punya addEventListener pada matchMedia.
        desktop.addListener(closeOnDesktop);
    }

    // Panelnya juga ditutup saat halaman pertama kali dibuka di layar lebar.
    closeOnDesktop();
}

function initBackToTop() {
    const button = document.querySelector('[data-back-to-top]');

    if (!button) {
        return;
    }

    // Tombolnya baru muncul setelah pengunjung melewati bagian pembuka.
    const threshold = 400;

    const update = () => {
        const visible = window.scrollY > threshold;

        button.classList.toggle('hidden', !visible);
        button.classList.toggle('flex', visible);
    };

    button.addEventListener('click', () => {
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });

    window.addEventListener('scroll', update, { passive: true });
    update();
}

/**
 * Melaporkan klik tombol WhatsApp ke /analytics/whatsapp-click supaya jumlah
 * pengunjung yang menghubungi admin bisa dilihat di dasbor.
 *
 * Tautan WhatsApp tetap dibuka seperti biasa; laporannya dikirim dengan
 * keepalive agar tetap sampai walau pengunjung langsung berpindah ke WhatsApp.
 * Kalau pelaporannya gagal, pengunjung tidak melihat apa pun.
 */
function initWhatsAppClickTracking() {
    const links = document.querySelectorAll('[data-whatsapp-click]');

    if (!links.length) {
        return;
    }

    const endpoint = document.querySelector('meta[name="whatsapp-click-endpoint"]')?.content;
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!endpoint || !token) {
        return;
    }

    links.forEach((link) => {
        link.addEventListener('click', () => {
            fetch(endpoint, {
                method: 'POST',
                keepalive: true,
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ path: window.location.pathname }),
            }).catch(() => {});
        });
    });
}

initWhatsAppWidget();
initMobileNav();
initBackToTop();
initWhatsAppClickTracking();
