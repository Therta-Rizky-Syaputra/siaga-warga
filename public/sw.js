// Siaga Warga — service worker
// Tujuan: halaman yang pernah dibuka tetap terbuka saat sinyal warga putus-putus.
const VERSI = 'siaga-warga-v1';
const ASET_DASAR = ['/offline.html', '/manifest.webmanifest', '/ikon/ikon-192.png'];

self.addEventListener('install', (e) => {
    e.waitUntil(caches.open(VERSI).then((c) => c.addAll(ASET_DASAR)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys()
            .then((kunci) => Promise.all(kunci.filter((k) => k !== VERSI).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (e) => {
    const r = e.request;
    if (r.method !== 'GET') return;                       // unggah laporan/jawaban pengurus: jangan disentuh
    const u = new URL(r.url);
    if (u.origin !== location.origin) return;
    if (u.pathname.startsWith('/perubahan')) return;      // aliran waktu-nyata: langsung ke server

    // berkas build Vite ber-hash: aman diambil dari cache lebih dulu
    if (u.pathname.startsWith('/build/') || u.pathname.startsWith('/ikon/')) {
        e.respondWith(
            caches.match(r).then((temu) => temu || fetch(r).then((jawab) => {
                const salinan = jawab.clone();
                caches.open(VERSI).then((c) => c.put(r, salinan));
                return jawab;
            }))
        );
        return;
    }

    // halaman: utamakan jaringan, simpan salinan, pakai salinan bila jaringan mati
    e.respondWith(
        fetch(r)
            .then((jawab) => {
                if (jawab.ok && r.mode === 'navigate') {
                    const salinan = jawab.clone();
                    caches.open(VERSI).then((c) => c.put(r, salinan));
                }
                return jawab;
            })
            .catch(() => caches.match(r).then((temu) => temu || caches.match('/offline.html')))
    );
});
