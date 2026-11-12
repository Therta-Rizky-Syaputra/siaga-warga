"""Membuat dokumen perancangan Siaga Warga (DOCX) dari diagram di docs/diagram."""
import os
from docx import Document
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml.ns import qn
from docx.oxml import OxmlElement
from docx.shared import Cm, Pt, RGBColor
from PIL import Image

BASE = r"C:\Users\Therta\siaga-warga\docs"
DIA = os.path.join(BASE, "diagram")
OUT = os.path.join(BASE, "Perancangan-Sistem-Siaga-Warga.docx")

BIRU = RGBColor(0x1E, 0x3A, 0x8A)
ABU = RGBColor(0x47, 0x55, 0x69)

doc = Document()

# gaya dasar
normal = doc.styles["Normal"]
normal.font.name = "Calibri"
normal.font.size = Pt(11)
normal._element.rPr.rFonts.set(qn("w:eastAsia"), "Calibri")
for nama, ukuran, warna in [("Heading 1", 16, BIRU), ("Heading 2", 13, BIRU), ("Heading 3", 11.5, BIRU)]:
    st = doc.styles[nama]
    st.font.name = "Calibri"
    st.font.size = Pt(ukuran)
    st.font.color.rgb = warna
    st.font.bold = True

for s in doc.sections:
    s.top_margin = s.bottom_margin = Cm(2.0)
    s.left_margin = s.right_margin = Cm(2.2)


def pasang_nomor_halaman(section):
    par_ = section.footer.paragraphs[0]
    par_.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run = par_.add_run()
    run.font.size = Pt(9)
    run.font.color.rgb = ABU
    awal = OxmlElement("w:fldChar")
    awal.set(qn("w:fldCharType"), "begin")
    instr = OxmlElement("w:instrText")
    instr.set(qn("xml:space"), "preserve")
    instr.text = "PAGE"
    akhir = OxmlElement("w:fldChar")
    akhir.set(qn("w:fldCharType"), "end")
    for el in (awal, instr, akhir):
        run._r.append(el)


for s in doc.sections:
    pasang_nomor_halaman(s)

LEBAR = Cm(16.6)


def judul(teks, level=1):
    doc.add_heading(teks, level=level)


def par(teks, tebal=False, miring=False, abu=False, ukuran=11, spasi_setelah=6):
    p = doc.add_paragraph()
    r = p.add_run(teks)
    r.bold = tebal
    r.italic = miring
    r.font.size = Pt(ukuran)
    if abu:
        r.font.color.rgb = ABU
    p.paragraph_format.space_after = Pt(spasi_setelah)
    return p


def butir(teks):
    p = doc.add_paragraph(teks, style="List Bullet")
    p.paragraph_format.space_after = Pt(2)
    return p


def ulang_header(t):
    baris_header = t.rows[0]._tr
    properti = baris_header.get_or_add_trPr()
    el = OxmlElement("w:tblHeader")
    el.set(qn("w:val"), "true")
    properti.append(el)


def tabel(kepala, baris, lebar_kolom=None):
    t = doc.add_table(rows=1, cols=len(kepala))
    t.style = "Light Grid Accent 1"
    t.alignment = WD_TABLE_ALIGNMENT.CENTER
    ulang_header(t)
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
    for baris_ in t.rows:                     # cegah satu baris terbelah antar halaman
        trPr = baris_._tr.get_or_add_trPr()
        trPr.append(OxmlElement("w:cantSplit"))
    if lebar_kolom:
        for baris_ in t.rows:
            for i, w in enumerate(lebar_kolom):
                baris_.cells[i].width = w
    doc.add_paragraph().paragraph_format.space_after = Pt(4)
    return t


def gambar(nama, keterangan, lebar=LEBAR):
    path = os.path.join(DIA, nama)
    w, h = Image.open(path).size
    lebar_final = lebar if h <= w * 1.45 else Cm(11.5)
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


# ============================== HALAMAN JUDUL ==============================
t = doc.add_paragraph()
t.alignment = WD_ALIGN_PARAGRAPH.CENTER
r = t.add_run("DOKUMEN PERANCANGAN SISTEM")
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
     ("Isi dokumen", "Use Case Diagram, Flowchart alur per aktor, ERD & skema tabel, "
                     "Wireframe UI/UX, spesifikasi teknis")],
    [Cm(4.2), Cm(12.4)],
)

judul("Anggota Kelompok", 2)
tabel(
    ["No", "Nama", "NPM"],
    [("1", "Indyra Putri Pratama", "2410631170144"),
     ("2", "Inong Muliya Zahra", "2410631170145"),
     ("3", "Lacinta Theo Gracia Hutasoit", "2410631170078"),
     ("4", "Afip Ghazanfar", "2410631170054"),
     ("5", "Therta Rizky Syaputra", "2410631170114")],
    [Cm(1.2), Cm(9.4), Cm(6.0)],
)

doc.add_page_break()

# ============================== 1. RINGKASAN ==============================
judul("1. Ringkasan Solusi", 1)
par("Siaga Warga adalah aplikasi web pengaduan fasilitas umum untuk lingkungan RT/RW. "
    "Warga mengirim laporan kerusakan (lampu jalan mati, saluran air tersumbat, jalan berlubang) "
    "beserta foto bukti dan lokasi acuan teks, lalu memantau statusnya. Pengurus RT/RW menerima "
    "laporan dalam satu antrean terpusat, menetapkan prioritas, mengubah status, dan menanggapi. "
    "Admin sistem mengelola basis data pengguna.")

judul("1.1 Aktor dan Hak Akses", 2)
tabel(
    ["Aktor", "Peran dalam sistem", "Hak akses utama"],
    [("Warga", "Pelapor (usia 18–60 tahun, literasi digital beragam)",
      "Kirim laporan + foto, pantau status, tambah tanggapan, baca pengumuman"),
     ("Pengurus RT/RW", "Verifikator dan penanggung jawab penanganan",
      "Dashboard ringkasan, verifikasi, ubah status & prioritas, tanggapi laporan, publikasi pengumuman"),
     ("Admin Sistem", "Pengelola basis data pengguna",
      "Tambah pengguna, ubah peran, hapus pengguna, filter peran")],
    [Cm(3.0), Cm(5.4), Cm(8.2)],
)

judul("1.2 Kebutuhan Non-Fungsional", 2)
for teks in [
    "Waktu respons sistem cepat — halaman memakai satu query ringkas per daftar dan paginasi 10 baris.",
    "Antarmuka responsif — satu tata letak melayani layar mobile maupun desktop (grid adaptif).",
    "Keamanan data pelapor — kata sandi di-hash, laporan hanya bisa dibuka pelapornya sendiri atau pengurus (HTTP 403 bila dilanggar).",
    "Desain intuitif — label berbahasa Indonesia, tombol minimal 44 px, formulir tiga kolom wajib dengan petunjuk pengisian.",
    "Metrik waktu respons — sistem mencatat kolom ditangani_pada saat laporan pertama kali masuk tahap Diproses.",
]:
    butir(teks)

doc.add_page_break()

# ============================== 2. USE CASE ==============================
judul("2. Use Case Diagram", 1)
par("Aktor dipisah tegas menjadi tiga dan tidak ada use case yang boleh diakses aktor lain. "
    "Warga memakai tujuh use case, pengurus tujuh use case, admin satu use case.")
gambar("01-use-case.png", "Gambar 1. Use Case Diagram Siaga Warga — tiga aktor, lima belas use case.")
tabel(
    ["Aktor", "Use case"],
    [("Warga", "Mendaftar; Masuk (login); Mengirim Laporan; Melampirkan Foto Bukti; "
               "Memantau Status Laporan; Menambah Tanggapan; Membaca Pengumuman"),
     ("Pengurus RT/RW", "Menerima Laporan Masuk; Melihat Dashboard Ringkasan; Memverifikasi Laporan; "
                        "Mengubah Status Laporan; Menetapkan Prioritas; Menanggapi Laporan; "
                        "Mempublikasikan Pengumuman"),
     ("Admin Sistem", "Mengelola Basis Data Pengguna")],
    [Cm(3.4), Cm(13.2)],
)

doc.add_page_break()

# ============================== 3. FLOWCHART ==============================
judul("3. Flowchart Alur Proses Bisnis", 1)

judul("3.1 Alur Warga", 2)
par("Warga masuk atau mendaftar, melihat ringkasan laporan sendiri, mengisi formulir laporan "
    "(kategori, prioritas, judul, deskripsi, lokasi acuan, foto), lalu memantau linimasa status. "
    "Bila data formulir tidak valid, sistem menampilkan pesan kesalahan dan warga memperbaiki isian. "
    "Bila status belum final, warga dapat menambah tanggapan dan memantau kembali.")
gambar("02-flowchart-warga.png", "Gambar 2. Flowchart alur warga.")

judul("3.2 Alur Pengurus RT/RW", 2)
par("Pengurus membuka dashboard ringkasan, masuk ke menu Laporan Masuk yang mengurutkan laporan "
    "secara otomatis (prioritas tinggi lebih dulu, lalu laporan paling lama menunggu), memeriksa "
    "detail laporan, lalu memverifikasi. Status laporan yang sah berjalan berurutan: Dikirim → "
    "Diverifikasi → Diproses → Selesai. Laporan yang tidak valid ditolak disertai alasan tertulis.")
gambar("03-flowchart-pengurus.png", "Gambar 3. Flowchart alur pengurus RT/RW.")

judul("3.3 Alur Admin Sistem", 2)
par("Admin membuka halaman Kelola Pengguna, memilih aksi (tambah pengguna, ubah peran, atau hapus), "
    "lalu sistem memvalidasi data. Sistem menolak perubahan yang membuat jumlah admin menjadi nol, "
    "sehingga minimal satu akun admin selalu tersedia.")
gambar("04-flowchart-admin.png", "Gambar 4. Flowchart alur admin sistem.")

doc.add_page_break()

# ============================== 4. ERD ==============================
judul("4. Struktur Basis Data (ERD)", 1)
par("Basis data terdiri atas lima tabel dengan enam relasi satu-ke-banyak. Relasi users → laporans "
    "muncul dua kali karena kolom user_id menyimpan pelapor dan kolom ditangani_oleh menyimpan "
    "petugas yang menangani laporan.")
gambar("05-erd.png", "Gambar 5. Entity Relationship Diagram Siaga Warga.")

doc.add_page_break()   # tabel skema utuh dalam satu halaman
judul("4.1 Skema Tabel", 2)
tabel(
    ["users", "laporans", "kategori_masalahs", "tanggapans", "pengumumans"],
    [("id (PK)", "id (PK)", "id (PK)", "id (PK)", "id (PK)"),
     ("name", "user_id (FK → users)", "nama (unik)", "laporan_id (FK → laporans)", "user_id (FK → users)"),
     ("telepon (unik, login)", "kategori_masalah_id (FK)", "deskripsi", "user_id (FK → users)", "judul"),
     ("password", "judul", "", "isi", "isi"),
     ("role", "deskripsi", "", "status_baru", "berlaku_sampai"),
     ("rt_rw", "lokasi (acuan teks)", "", "", ""),
     ("email (opsional)", "foto (opsional)", "", "", ""),
     ("", "prioritas", "", "", ""),
     ("", "status", "", "", ""),
     ("", "ditangani_oleh (FK → users)\nditangani_pada (waktu respons)", "", "", "")],
    [Cm(3.4), Cm(3.8), Cm(3.0), Cm(3.4), Cm(3.0)],
)

judul("4.2 Enum dan Aturan Data", 2)
tabel(
    ["Kolom", "Nilai", "Makna"],
    [("users.role", "warga | pengurus | admin", "Menentukan rute dan menu yang boleh diakses"),
     ("laporans.prioritas", "rendah | sedang | tinggi", "Menentukan urutan antrean pengurus"),
     ("laporans.status", "dikirim | diverifikasi | diproses | selesai | ditolak", "Linimasa penanganan laporan"),
     ("tanggapans.status_baru", "salah satu nilai status, opsional", "Mencatat perubahan status pada riwayat tanggapan")],
    [Cm(4.4), Cm(6.0), Cm(6.2)],
)

doc.add_page_break()

# ============================== 5. WIREFRAME ==============================
judul("5. Desain Antarmuka (Wireframe)", 1)
par("Rancangan antarmuka dibuat dua varian: tampilan mobile untuk warga dan tampilan dashboard web "
    "untuk pengurus. Nomor pada gambar menandai elemen yang dijelaskan di bawahnya.")

judul("5.1 Mobile — Dashboard Warga", 2)
gambar("06-wireframe-mobile-dashboard.png", "Gambar 6. Wireframe dashboard warga (mobile).")
for teks in [
    "1 App bar: merek SiagaWarga dan identitas pengguna.",
    "2 Tombol utama \"+ Buat Laporan\" — satu aksi paling menonjol bagi warga.",
    "3 Ringkasan jumlah laporan: total, sedang ditangani, selesai.",
    "4 Kartu laporan memuat judul, nomor tiket, dan kategori (tanpa perlu membuka detail).",
    "5 Badge status laporan; 6 badge prioritas laporan — dua badge terpisah agar tidak tertukar.",
    "7 Kartu pengumuman resmi dari pengurus.",
    "8 Navigasi bawah tiga menu: Dashboard, Laporan, Pengumuman.",
]:
    butir(teks)

judul("5.2 Mobile — Formulir Buat Laporan", 2)
gambar("07-wireframe-mobile-form.png", "Gambar 7. Wireframe formulir buat laporan (mobile).")
for teks in [
    "1 Header halaman beserta tombol kembali.",
    "2–6 Lima kolom wajib: kategori masalah, perkiraan prioritas, judul, deskripsi, dan lokasi acuan teks.",
    "6 Lokasi acuan diberi petunjuk pengisian karena warga tidak memakai koordinat peta.",
    "7 Foto bukti bersifat opsional (JPG/PNG, maksimal 2 MB).",
    "8 Tombol Kirim Laporan dan Batal.",
]:
    butir(teks)

judul("5.3 Desktop — Dashboard Pengurus RT/RW", 2)
gambar("08-wireframe-desktop-pengurus.png", "Gambar 8. Wireframe dashboard pengurus (desktop).")
for teks in [
    "1 Navigasi menyesuaikan peran pengguna yang sedang masuk.",
    "2 Aksi utama pengurus: masuk ke antrean Laporan Masuk.",
    "3 Enam kartu metrik jumlah laporan per status, termasuk jumlah prioritas tinggi yang belum selesai.",
    "4 Tabel antrean laporan: prioritas tinggi lebih dulu, lalu laporan yang paling lama menunggu.",
    "5 Modul pengumuman resmi pengurus.",
]:
    butir(teks)

doc.add_page_break()

# ============================== 6. TEKNIS ==============================
judul("6. Spesifikasi Teknis", 1)
tabel(
    ["Aspek", "Pilihan", "Alasan"],
    [("Kerangka kerja", "Laravel 13 (PHP 8.4)", "Struktur MVC rapi, ekosistem siap untuk pengembangan lanjutan"),
     ("Asset bundling", "Vite 8 + Tailwind CSS 4", "Sesuai ketentuan tugas dan build aset cepat"),
     ("Basis data", "SQLite (MySQL opsional)", "Ringan untuk demo lokal; skema tetap kompatibel MySQL"),
     ("Tampilan", "Blade Components", "Tanpa Livewire agar alur server-rendered mudah diaudit"),
     ("Uji", "PHPUnit 12 + Larastan", "9 uji fitur dan analisis statis level 5"),
     ("Kontrol versi", "Git (Gitea)", "Kolaborasi kode antar anggota kelompok")],
    [Cm(3.4), Cm(4.6), Cm(8.6)],
)

judul("6.1 Peta Rute dan Middleware", 2)
tabel(
    ["Rute", "Aktor", "Fungsi"],
    [("/masuk, /daftar", "Tamu", "Autentikasi dan pendaftaran akun warga"),
     ("/dashboard", "Semua", "Dashboard berbeda per peran"),
     ("/laporan, /laporan/baru, /laporan/{id}", "Warga", "Kirim dan pantau laporan sendiri"),
     ("/laporan/{id}/tanggapan", "Warga & Pengurus", "Tambah tanggapan pada laporan yang boleh diakses"),
     ("/masuk/laporan, /masuk/laporan/{id}", "Pengurus, Admin", "Antrean, verifikasi, ubah status & prioritas"),
     ("/pengumuman (GET)", "Semua", "Baca pengumuman resmi"),
     ("/pengumuman/baru, ubah, hapus", "Pengurus, Admin", "Publikasi dan kelola pengumuman"),
     ("/admin/pengguna", "Admin", "Kelola basis data pengguna")],
    [Cm(6.4), Cm(3.4), Cm(6.8)],
)
par("Peran dijaga oleh middleware \"peran\" di lapisan rute, dan kepemilikan laporan diperiksa "
    "di controller. Warga yang membuka laporan milik warga lain atau area pengurus menerima HTTP 403.",
    abu=True, ukuran=10)

# ============================== 7. INDIKATOR ==============================
judul("7. Indikator Keberhasilan dan Wujudnya di Sistem", 1)
tabel(
    ["Indikator", "Wujud dalam sistem"],
    [("Zero Missed Report", "Setiap laporan wajib memiliki kategori, lokasi, status, dan nomor tiket unik (SW-0001)"),
     ("Efisiensi waktu respons", "Kolom ditangani_pada terisi otomatis saat status pertama kali masuk Diproses atau Selesai"),
     ("Kualitas teknis (UAT)", "9 uji fitur pada tests/Feature dan analisis statis Larastan level 5 tanpa error"),
     ("Dampak jangka panjang", "Dipantau di luar sistem: tingkat adopsi warga dan arsip pemeliharaan fasilitas")],
    [Cm(5.0), Cm(11.6)],
)
par("Indikator dampak jangka panjang dipantau di luar sistem melalui jumlah warga yang mengadopsi "
    "aplikasi dan kerapian arsip pemeliharaan fasilitas publik.", miring=True, abu=True, ukuran=9.5)

judul("7.1 Rencana Pengujian Penerimaan (UAT)", 2)
tabel(
    ["Skenario", "Langkah", "Hasil yang diharapkan"],
    [("Kirim laporan", "Warga mengisi formulir lengkap lalu menekan Kirim Laporan",
      "Laporan tersimpan berstatus Dikirim dengan nomor tiket SW-0001"),
     ("Validasi formulir", "Kirim tanpa kategori dan lokasi",
      "Pesan kesalahan muncul pada kolom kategori dan lokasi"),
     ("Verifikasi", "Pengurus mengubah status menjadi Diproses",
      "Status berubah, ditangani_pada terisi, dan tanggapan tercatat di riwayat"),
     ("Isolasi data", "Warga membuka laporan milik warga lain",
      "Sistem menolak dengan HTTP 403"),
     ("Kelola pengguna", "Admin menambah pengurus lalu mencoba menghapus akunnya sendiri",
      "Pengguna baru tersimpan; penghapusan akun sendiri ditolak")],
    [Cm(3.6), Cm(6.4), Cm(6.6)],
)

doc.save(OUT)
print("dokumen:", OUT, round(os.path.getsize(OUT) / 1024), "KB")
