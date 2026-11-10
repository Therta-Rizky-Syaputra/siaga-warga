// ponytail: hanya interaksi yang butuh JS — sisanya HTML native
document.addEventListener('click', (e) => {
    const tombol = e.target.closest('[data-konfirmasi]');
    if (tombol && !confirm(tombol.dataset.konfirmasi)) {
        e.preventDefault();
    }
});

// tampilkan nama berkas foto yang dipilih (input file bawaan tidak menampilkannya)
const foto = document.querySelector('input[type=file]');
foto?.addEventListener('change', () => {
    document.getElementById('nama-foto').textContent = foto.files[0]?.name ?? '';
});
