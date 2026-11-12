// ponytail: hanya interaksi yang butuh JS — sisanya HTML native

// konfirmasi aksi berbahaya
document.addEventListener('click', (e) => {
    const tombol = e.target.closest('[data-konfirmasi]');
    if (tombol && !confirm(tombol.dataset.konfirmasi)) {
        e.preventDefault();
    }
});

// nama berkas foto yang dipilih (input file bawaan tidak menampilkannya)
const foto = document.querySelector('input[type=file]');
foto?.addEventListener('change', () => {
    const el = document.getElementById('nama-foto');
    if (el) el.textContent = foto.files[0]?.name ?? '';
});

// ---- real-time ----
// Pakai SSE bila layar pengurus (lapisan terbaik), sisanya denyut tiap 10 detik.
// Kenapa bukan SSE untuk semua: browser membatasi jumlah koneksi per-IP, dan
// puluhan warga di RT yang sama akan saling memblokir aliran.
const papan = document.querySelector('[data-pantau]');

if (papan) {
    const aliranUrl = papan.dataset.aliran || '';
    const denyutUrl = papan.dataset.denyut;

    let kirimanSebelum = null;   // jumlah laporan menunggu pada pantauan sebelumnya

    const perbarui = (ringkas) => {
        for (const [kunci, nilai] of Object.entries(ringkas)) {
            const el = document.querySelector(`[data-hitung="${kunci}"]`);
            if (!el || typeof nilai !== 'number') continue;
            if (el.textContent.trim() === String(nilai)) continue;
            el.textContent = nilai;
            el.closest('[data-kartu]')?.classList.add('berdenyut');
        }

        const jam = document.getElementById('diperbarui-pada');
        if (jam && ringkas.waktu) jam.textContent = `${ringkas.waktu} WIB`;

        // tawarkan muat ulang HANYA saat jumlah laporan yang menunggu bertambah,
        // bukan selama masih ada laporan yang belum ditangani
        const tawaran = document.getElementById('tawaran-muat');
        if (tawaran && typeof ringkas.dikirim === 'number') {
            if (kirimanSebelum !== null && ringkas.dikirim > kirimanSebelum) {
                tawaran.hidden = false;
            }
            kirimanSebelum = ringkas.dikirim;
        }
    };

    const mulaiDenyut = () => {
        const cek = async () => {
            if (document.visibilityState !== 'visible') return;   // hemat baterai HP warga
            try {
                const r = await fetch(denyutUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
                if (r.ok) perbarui(await r.json());
            } catch { /* sinyal hilang sesaat: coba lagi pada denyut berikutnya */ }
        };
        cek();
        setInterval(cek, 10000);
    };

    let sumber = null;
    const mulaiAliran = () => {
        if (!aliranUrl || !('EventSource' in window)) return mulaiDenyut();

        sumber = new EventSource(aliranUrl);

        const tangani = (data) => {
            if (data === 'ulang') {
                sumber.close();
                setTimeout(mulaiAliran, 500);          // server menutup tiap 10 menit
                return;
            }
            try { perbarui(JSON.parse(data)); } catch { /* abaikan bingkai tak dikenal */ }
        };

        // Laravel mengirim "event: update", jadi onmessage saja TIDAK terpanggil.
        sumber.addEventListener('update', (e) => tangani(e.data));
        sumber.onmessage = (e) => tangani(e.data);     // jaga-jaga bila nama event berubah

        // pantau kesehatan aliran: kalau 25 detik sunyi padahal data sering berubah,
        // turun ke mode denyut agar pengurus tetap melihat laporan baru
        let denyutTerakhir = Date.now();
        setInterval(() => {
            if (Date.now() - denyutTerakhir > 25000) {
                sumber.close();
                mulaiDenyut();
            }
        }, 25000);
        sumber.onerror = () => {
            sumber.close();
            setTimeout(mulaiDenyut, 4000);             // aliran gagal → turun ke denyut
        };
    };

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState !== 'visible') {
            sumber?.close();
            sumber = null;
        } else if (!sumber) {
            mulaiAliran();
        }
    });

    mulaiAliran();
}

// ---- pasang ke layar utama HP (PWA) ----
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => { /* mode privat: abaikan */ });
    });
}

let tawaranPasang = null;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    tawaranPasang = e;
    const tombol = document.getElementById('pasang-ke-hp');
    if (tombol) tombol.hidden = false;
});

document.getElementById('pasang-ke-hp')?.addEventListener('click', async (e) => {
    e.target.hidden = true;
    tawaranPasang?.prompt();
    tawaranPasang = null;
});
