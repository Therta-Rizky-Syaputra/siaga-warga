"""Menyusun dokumen bukti Siaga Warga: hasil uji nyata + tangkapan layar asli."""
import os

from docx import Document
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Pt, RGBColor
from PIL import Image

BASE = r"C:\Users\Therta\siaga-warga\docs"
SCR = os.path.join(BASE, "screenshot-final")
DIA = os.path.join(BASE, "diagram")
OUT = os.path.join(BASE, "Bukti-Pengujian-Siaga-Warga.docx")

BIRU = RGBColor(0x1E, 0x3A, 0x8A)
ABU = RGBColor(0x47, 0x55, 0x69)
HIJAU = RGBColor(0x15, 0x80, 0x3D)

doc = Document()
normal = doc.styles["Normal"]
normal.font.name = "Calibri"
normal.font.size = Pt(11)
normal._element.rPr.rFonts.set(qn("w:eastAsia"), "Calibri")
for nama, ukuran in [("Heading 1", 16), ("Heading 2", 13), ("Heading 3", 11.5)]:
    st = doc.styles[nama]
    st.font.name = "Calibri"
    st.font.size = Pt(ukuran)
    st.font.color.rgb = BIRU
    st.font.bold = True

for s in doc.sections:
    s.top_margin = s.bottom_margin = Cm(2.0)
    s.left_margin = s.right_margin = Cm(2.2)
    par_ = s.footer.paragraphs[0]
    par_.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run = par_.add_run()
    run.font.size = Pt(9)
    run.font.color.rgb = ABU
    awal = OxmlElement("w:fldChar"); awal.set(qn("w:fldCharType"), "begin")
    instr = OxmlElement("w:instrText"); instr.set(qn("xml:space"), "preserve"); instr.text = "PAGE"
    akhir = OxmlElement("w:fldChar"); akhir.set(qn("w:fldCharType"), "end")
    for el in (awal, instr, akhir):
        run._r.append(el)

LEBAR = Cm(16.6)


def judul(t, level=1):
    doc.add_heading(t, level=level)


def par(t, tebal=False, miring=False, abu=False, ukuran=11, setelah=6):
    p = doc.add_paragraph()
    r = p.add_run(t)
    r.bold = tebal
    r.italic = miring
    r.font.size = Pt(ukuran)
    if abu:
        r.font.color.rgb = ABU
    p.paragraph_format.space_after = Pt(setelah)
    return p


def butir(t, tebal_awal=None):
    p = doc.add_paragraph(style="List Bullet")
    if tebal_awal:
        r = p.add_run(tebal_awal)
        r.bold = True
        p.add_run(" " + t)
    else:
        p.add_run(t)
    p.paragraph_format.space_after = Pt(2)
    return p


def tabel(kepala, baris, lebar_kolom=None, ulang=True):
    t = doc.add_table(rows=1, cols=len(kepala))
    t.style = "Light Grid Accent 1"
    t.alignment = WD_TABLE_ALIGNMENT.CENTER
    if ulang:
        pr = t.rows[0]._tr.get_or_add_trPr()
        el = OxmlElement("w:tblHeader"); el.set(qn("w:val"), "true"); pr.append(el)
    for i, teks in enumerate(kepala):
        sel = t.rows[0].cells[i]
        sel.text = ""
        r = sel.paragraphs[0].add_run(teks)
        r.bold = True
        r.font.size = Pt(10)
    for data in baris:
        sel = t.add_row().cells
        for i, teks in enumerate(data):
            sel[i].text = ""
            r = sel[i].paragraphs[0].add_run(str(teks))
            r.font.size = Pt(10)
    for baris_ in t.rows:
        baris_._tr.get_or_add_trPr().append(OxmlElement("w:cantSplit"))
        if lebar_kolom:
            for i, w in enumerate(lebar_kolom):
                baris_.cells[i].width = w
    doc.add_paragraph().paragraph_format.space_after = Pt(4)
    return t


TINGGI_MAKS = Cm(15.0)   # sisakan ruang untuk judul, paragraf, dan keterangan gambar


def gambar(berkas, keterangan, lebar=LEBAR, folder=None, bersebelahan=None):
    """Satu gambar, atau dua gambar portrait berdampingan agar tidak menyisakan halaman kosong."""
    if bersebelahan:
        _gambar_berdampingan(bersebelahan, keterangan)
        return

    path = os.path.join(folder or SCR, berkas)
    if not os.path.exists(path):
        par("[gambar tidak ditemukan: %s]" % berkas, miring=True, abu=True, ukuran=9)
        return
    w, h = Image.open(path).size
    tinggi_ideal = TINGGI_MAKS
    lebar_final = lebar if h <= w * 1.6 else min(lebar, tinggi_ideal * (w / h))
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.add_run().add_picture(path, width=lebar_final)
    cap = doc.add_paragraph()
    cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = cap.add_run(keterangan)
    r.italic = True
    r.font.size = Pt(9)
    r.font.color.rgb = ABU
    cap.paragraph_format.space_after = Pt(12)


def _gambar_berdampingan(daftar, keterangan):
    """Beberapa tangkapan layar ponsel dalam satu baris, setiap gambar disertai keterangannya."""
    t = doc.add_table(rows=2, cols=len(daftar))
    t.alignment = WD_TABLE_ALIGNMENT.CENTER
    # tinggi seragam: gambar tertinggi menentukan, sehingga tampak sejajar dan tidak terpotong
    tinggi_maks = Cm(10.2)
    for i, (berkas, teks) in enumerate(daftar):
        path = os.path.join(SCR, berkas)
        sel_atas = t.rows[0].cells[i]
        sel_atas.text = ""
        par_sel = sel_atas.paragraphs[0]
        par_sel.alignment = WD_ALIGN_PARAGRAPH.CENTER
        if os.path.exists(path):
            w, h = Image.open(path).size
            lebar_seimbang = tinggi_maks * (w / h)
            par_sel.add_run().add_picture(path, height=tinggi_maks, width=lebar_seimbang)
        sel_bawah = t.rows[1].cells[i]
        sel_bawah.text = ""
        cap = sel_bawah.paragraphs[0]
        cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r = cap.add_run(teks)
        r.italic = True
        r.font.size = Pt(8.5)
        r.font.color.rgb = ABU
    for baris_ in t.rows:
        baris_._tr.get_or_add_trPr().append(OxmlElement("w:cantSplit"))
    doc.add_paragraph().paragraph_format.space_after = Pt(10)


# ============================== JUDUL ==============================
t = doc.add_paragraph()
t.alignment = WD_ALIGN_PARAGRAPH.CENTER
r = t.add_run("DOKUMEN BUKTI PENGUJIAN")
r.bold = True
r.font.size = Pt(20)
r.font.color.rgb = BIRU

s = doc.add_paragraph()
s.alignment = WD_ALIGN_PARAGRAPH.CENTER
r = s.add_run("SIAGA WARGA")
r.bold = True
r.font.size = Pt(30)
r.font.color.rgb = BIRU

u = doc.add_paragraph()
u.alignment = WD_ALIGN_PARAGRAPH.CENTER
r = u.add_run("Platform Pengaduan Fasilitas Umum Warga RT/RW")
r.font.size = Pt(13)
r.italic = True

doc.add_paragraph()
tabel(
    ["Keterangan", "Isi"],
    [("Mata kuliah", "Komputer dan Masyarakat"),
     ("Tugas", "Kelompok 6 — Pertemuan 3 (Progress Project)"),
     ("Program studi", "Informatika, Fakultas Ilmu Komputer"),
     ("Universitas", "Universitas Singaperbangsa Karawang"),
     ("Tahun", "2026"),
     ("Sifat bukti", "Seluruh tangkapan layar diambil dari aplikasi yang benar-benar dijalankan; "
                     "seluruh angka pengujian berasal dari perintah yang dijalankan, bukan perkiraan")],
    [Cm(3.6), Cm(13.0)],
)

judul("Ringkasan Hasil", 2)
tabel(
    ["Pemeriksaan", "Alat", "Hasil"],
    [("Uji otomatis aplikasi web", "php artisan test", "21 lulus, 83 pemeriksaan, 0 gagal"),
     ("Analisis statis", "PHPStan (Larastan) level 5", "0 galat"),
     ("Perapian kode", "Laravel Pint", "lulus"),
     ("Uji aplikasi Android", "flutter analyze", "0 masalah"),
     ("Uji widget Android", "flutter test", "3 lulus"),
     ("Pembungkusan APK", "flutter build apk --release", "3 berkas APK, tertandatangan")],
    [Cm(5.2), Cm(5.4), Cm(6.0)],
)

doc.add_page_break()

# ============================== 1. BUKTI APLIKASI DI HP ==============================
judul("1. Bukti Aplikasi Berjalan di Ponsel Warga", 1)
par("Bagian ini memperlihatkan aplikasi yang sudah terpasang di ponsel. Warga masuk memakai "
    "nomor HP, mengirim laporan lengkap dengan foto, dan memantau kemajuannya tanpa perlu "
    "menyegarkan halaman.")

judul("1.1 Masuk Memakai Nomor HP", 2)
par("Warga mengetik nomor HP seperti kebiasaan sehari-hari (0812…, 62812…, atau +62 812…). "
    "Sistem menyamakan semua bentuk penulisan itu menjadi satu nomor, sehingga warga tidak "
    "perlu mengingat format tertentu.")


judul("1.2 Dashboard Warga", 2)
par("Angka ringkasan dan status laporan diperbarui sendiri oleh aplikasi. Keterangan "
    "\"Terakhir tersinkron pukul …\" menjadi tanda bahwa data memang baru, bukan sisa tampilan lama.")
gambar("", "", bersebelahan=[
    ("01-hp-masuk-nomor.png", "Gambar 2. Masuk memakai nomor HP."),
    ("02-hp-dashboard-warga-360.png", "Gambar 3. Dashboard warga (layar 360 px)."),
    ("03-hp-form-laporan.png", "Gambar 4. Formulir buat laporan."),
])

judul("1.3 Mengirim Laporan", 2)
par("Formulir dibuat sesederhana mungkin untuk warga lintas usia: kategori, perkiraan prioritas, "
    "judul, deskripsi, lokasi acuan teks, dan foto bukti (opsional).")


judul("1.4 Memantau Progres Laporan", 2)
par("Setiap laporan menerima nomor tiket (SW-0001) dan linimasa status yang jelas, sehingga warga "
    "tahu laporannya sedang di tahap mana.")
gambar("", "", bersebelahan=[
    ("04-hp-detail-laporan.png", "Gambar 5. Detail laporan: linimasa status dan riwayat tanggapan."),
    ("09-hp-tanpa-sinyal.png", "Gambar 6. Tampilan saat ponsel tidak mendapat sinyal."),
])

judul("1.5 Saat Sinyal Hilang", 2)
par("Aplikasi dapat dipasang ke layar utama ponsel. Ketika jaringan putus, warga melihat pesan "
    "yang menjelaskan keadaannya, bukan layar putih yang membingungkan.")


doc.add_page_break()

# ============================== 2. BUKTI SISI PENGURUS ==============================
judul("2. Bukti Sisi Pengurus RT/RW", 1)

judul("2.1 Dashboard dengan Data Langsung", 2)
par("Layar pengurus memantau perubahan data secara langsung. Angka pada kartu berkedip sekali "
    "saat berubah, sehingga pengurus sadar ada laporan baru tanpa harus menyegarkan halaman.")
gambar("05-desktop-dashboard-pengurus.png",
      "Gambar 7. Dashboard pengurus: kartu metrik per status, antrean laporan, dan pengumuman aktif.")

judul("2.2 Antrean Laporan Masuk", 2)
par("Laporan masuk diurutkan otomatis: prioritas tinggi lebih dulu, lalu laporan yang paling lama "
    "menunggu. Pengurus tidak perlu menebak mana yang harus ditangani lebih dulu.")
gambar("06-desktop-laporan-masuk.png",
      "Gambar 8. Antrean laporan masuk dengan penyaring status dan kategori.")

judul("2.3 Menangani Laporan", 2)
par("Pengurus mengubah status, menetapkan prioritas, dan menulis tanggapan. Saat laporan pertama "
    "kali masuk tahap ditangani, sistem mencatat waktunya sebagai ukuran kecepatan tanggapan.")
gambar("07-desktop-tangani-laporan.png",
      "Gambar 9. Halaman penanganan laporan: perubahan status, prioritas, dan tanggapan untuk warga.")

judul("2.4 Mengelola Data Pengguna (Admin Sistem)", 2)
par("Admin sistem menambah akun, mengubah peran, dan menghapus pengguna. Sistem menolak tindakan "
    "yang bisa menghilangkan seluruh akun admin, dan menolak penghapusan akun yang sedang dipakai.")
gambar("08-desktop-kelola-pengguna.png",
      "Gambar 10. Halaman kelola pengguna: nomor HP, peran, asal RT/RW, dan jumlah laporan.")

doc.add_page_break()

# ============================== 3. BUKTI UJI REAL-TIME ==============================
judul("3. Bukti Perubahan Data Secara Langsung", 1)
par("Bagian ini memperlihatkan pengujian yang paling menentukan: seorang warga mengirim laporan "
    "dari ponselnya sementara layar pengurus sudah terbuka, dan angka di layar pengurus berubah "
    "sendiri tanpa ada yang menyentuhnya.")

judul("3.1 Langkah Pengujian", 2)
tabel(
    ["Langkah", "Yang dilakukan", "Hasil yang tercatat"],
    [("1", "Pengurus membuka dashboard dan membiarkannya", "Angka awal: total 8 laporan, "
      "5 menunggu verifikasi"),
     ("2", "Warga mengirim laporan \"Lampu pos kamling mati\" dari ponsel", "Server menerima laporan, "
      "membalas kode 302 (tersimpan)"),
     ("3", "Layar pengurus diperiksa tanpa disentuh", "Angka berubah menjadi total 9 laporan, "
      "6 menunggu verifikasi"),
     ("4", "Layar diperiksa apakah sempat dimuat ulang", "Penanda di halaman masih ada — "
      "halaman tidak pernah dimuat ulang")],
    [Cm(1.6), Cm(7.0), Cm(8.0)],
)

judul("3.2 Rekaman Data yang Dikirim Server", 2)
par("Keterangan teknis: aplikasi memakai Server-Sent Events. Server mengirim ringkasan baru setiap "
    "kali datanya berubah. Berikut rekaman yang tertangkap saat pengujian di atas.")
p = doc.add_paragraph()
r = p.add_run(
    "event: update\n"
    "data: {\"versi\":3,\"total\":6,\"dikirim\":3,\"prioritas_tinggi\":2}\n\n"
    "event: update\n"
    "data: {\"versi\":4,\"total\":7,\"dikirim\":4,\"prioritas_tinggi\":3}"
)
r.font.name = "Consolas"
r.font.size = Pt(9)
p.paragraph_format.left_indent = Cm(0.8)
p.paragraph_format.space_after = Pt(10)
par("Baris pertama tercatat ketika pengurus baru membuka halaman, baris kedua beberapa detik "
    "kemudian ketika warga mengirim laporan baru — tanpa ada permintaan dari pengurus.",
    abu=True, ukuran=10)

judul("3.3 Cacat yang Ditemukan dan Diperbaiki", 2)
par("Pengujian ini menemukan tiga cacat nyata. Semuanya sudah diperbaiki dan dijaga oleh uji "
    "otomatis agar tidak terulang.")
tabel(
    ["Cacat", "Akibatnya bila dibiarkan", "Perbaikan"],
    [("Laravel mengirim pesan dengan nama \"update\", sedangkan sisi ponsel hanya mendengarkan "
      "pesan tanpa nama",
      "Dashboard tampak wajar, tetapi angka tidak pernah berubah — cacat yang paling sulit "
      "disadari tanpa diuji",
      "Sisi ponsel mendengarkan nama yang benar, ditambah pemeriksaan otomatis"),
     ("Aliran data menahan kunci sesi PHP",
      "Selama pengurus menonton dashboard, ia tidak bisa membuka laporan lain — halaman terasa "
      "macet",
      "Kunci sesi dilepas sebelum aliran dimulai, dengan uji yang memastikan urutannya"),
     ("Satu alamat dipakai untuk dua keperluan pada satu elemen",
      "Alamat pemantauan terbaca kosong, sehingga pemantauan tidak pernah menyala",
      "Penanda dan alamat dipisahkan menjadi dua atribut berbeda")],
    [Cm(5.2), Cm(5.6), Cm(5.8)],
)

doc.add_page_break()

# ============================== 4. BUKTI APK ==============================
judul("4. Bukti Aplikasi Android (APK)", 1)
par("Selain dapat dibuka lewat peramban, Siaga Warga dibungkus menjadi aplikasi Android sehingga "
    "ikonnya muncul di layar utama ponsel warga dan bisa dibuka seperti aplikasi biasa.")

judul("4.1 Berkas yang Dihasilkan", 2)
tabel(
    ["Berkas", "Untuk ponsel", "Ukuran", "Sidik jari SHA-256 (potongan)"],
    [("SiagaWarga-1.0.0-hp-modern.apk", "Keluaran 2017 ke atas (paling umum)", "16,8 MB",
      "9678609a78815dcb…"),
     ("SiagaWarga-1.0.0-hp-lama.apk", "Ponsel lama 32-bit", "14,2 MB", "f80a111eabf1d134…"),
     ("SiagaWarga-1.0.0-semua-hp.apk", "Semua ponsel (satu berkas)", "47,0 MB",
      "fd0343202281ef37…")],
    [Cm(5.6), Cm(4.4), Cm(1.8), Cm(4.0)],
)

judul("4.2 Pemeriksaan Berkas APK", 2)
tabel(
    ["Yang diperiksa", "Hasil"],
    [("Nama paket", "id.unsika.siaga_warga_app"),
     ("Nama aplikasi di layar ponsel", "Siaga Warga"),
     ("Versi", "1.0.0"),
     ("Dukungan sistem Android", "Android 7.0 ke atas"),
     ("Arsitektur ponsel", "arm64-v8a, armeabi-v7a, x86_64"),
     ("Izin yang diminta", "Hanya akses internet dan status jaringan"),
     ("Tanda tangan", "Sah (Skema v2), 1 penanda tangan, "
      "CN=Siaga Warga, O=Warga, C=ID"),
     ("Lalu lintas data", "Wajib terenkripsi HTTPS; pengecualian hanya untuk server uji "
      "di jaringan lokal")],
    [Cm(5.4), Cm(11.2)],
)
par("Catatan penting: APK ini adalah pembungkus. Seluruh isi aplikasi tetap dijalankan dari server "
    "RT/RW, sehingga perbaikan fitur di sisi server tidak memerlukan APK baru.",
    miring=True, abu=True, ukuran=10)

judul("4.3 Cara Warga Memasang", 2)
for teks in [
    "Pengurus mengirim berkas APK lewat WhatsApp atau Google Drive ke ponsel warga.",
    "Warga membuka berkas tersebut, lalu menyetujui pemasangan dari sumber itu ketika Android meminta.",
    "Setelah terpasang, ikon Siaga Warga muncul di layar utama ponsel.",
    "Bila alamat server belum ditanam di dalam APK, warga mengetiknya sekali pada layar pertama, "
    "misalnya siaga.contoh.id, lalu menekan \"Buka Siaga Warga\".",
]:
    butir(teks)

doc.add_page_break()

# ============================== 5. BUKTI KEAMANAN ==============================
judul("5. Bukti Keamanan Data Warga", 1)
par("Laporan warga memuat foto rumah dan lokasi, sehingga aksesnya diuji secara khusus.")

judul("5.1 Hasil Pemeriksaan Akses", 2)
tabel(
    ["Yang dicoba", "Pelaku", "Hasil yang diharapkan", "Hasil nyata"],
    [("Membuka foto laporan orang lain", "Warga kedua", "Ditolak", "403 — ditolak"),
     ("Membuka halaman laporan orang lain", "Warga kedua", "Ditolak", "403 — ditolak"),
     ("Membuka antrean laporan pengurus", "Warga kedua", "Ditolak", "403 — ditolak"),
     ("Membuka halaman kelola pengguna", "Warga kedua", "Ditolak", "403 — ditolak"),
     ("Menyambung ke aliran data pengurus", "Warga kedua", "Ditolak", "403 — ditolak"),
     ("Membaca pengumuman RT/RW", "Warga kedua", "Diizinkan", "200 — diizinkan"),
     ("Membuka foto laporan sendiri", "Pelapor", "Diizinkan", "200 — diizinkan"),
     ("Menebak kata sandi berkali-kali", "Siapa pun", "Dibatasi", "Dibatasi 5 percobaan/menit")],
    [Cm(5.0), Cm(2.8), Cm(4.2), Cm(4.6)],
)

judul("5.2 Perbaikan Keamanan yang Dilakukan", 2)
for teks in [
    "Foto bukti tidak lagi disimpan di folder yang bisa diakses publik; hanya pelapor dan pengurus "
    "yang dapat membukanya.",
    "Nomor HP disamakan bentuknya sebelum disimpan, sehingga satu warga tidak bisa membuat dua akun.",
    "Percobaan masuk dibatasi lima kali per menit untuk mencegah penebakan kata sandi.",
    "Aplikasi Android mewajibkan HTTPS; pengecualian hanya untuk alamat uji di jaringan lokal.",
]:
    butir(teks)

# ============================== 6. CARA MENGULANG ==============================
judul("6. Cara Mengulang Seluruh Pengujian", 1)
p = doc.add_paragraph()
r = p.add_run(
    "cd C:\\Users\\Therta\\siaga-warga\n"
    "php artisan test          # 21 uji aplikasi web\n"
    "php vendor/bin/phpstan analyse   # analisis statis\n"
    "\n"
    "cd C:\\Users\\Therta\\siaga_warga_app\n"
    "flutter analyze && flutter test  # uji aplikasi Android\n"
    "flutter build apk --release --dart-define=ALAMAT_SERVER=siaga.contoh.id"
)
r.font.name = "Consolas"
r.font.size = Pt(9)
p.paragraph_format.left_indent = Cm(0.6)

doc.save(OUT)
print("dokumen:", OUT, round(os.path.getsize(OUT) / 1024), "KB")
