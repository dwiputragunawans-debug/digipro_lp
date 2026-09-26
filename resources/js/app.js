// Tombol mengambang pada landing page: kartu obrolan WhatsApp dan tombol
// kembali ke atas. Keduanya opsional, jadi skripnya berhenti dengan tenang
// kalau elemennya memang tidak dirender.

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
initBackToTop();
initWhatsAppClickTracking();
