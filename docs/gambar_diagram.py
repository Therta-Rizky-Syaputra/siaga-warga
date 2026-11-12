"""Menggambar diagram perancangan Siaga Warga: use case, flowchart, ERD, wireframe."""
import os
import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt
from matplotlib.patches import FancyBboxPatch, FancyArrowPatch, Ellipse, Polygon, Circle, Rectangle

OUT = r"C:\Users\Therta\siaga-warga\docs\diagram"
os.makedirs(OUT, exist_ok=True)

BIRU, BIRU_TUA, BIRU_MUDA = "#1d4ed8", "#1e3a8a", "#dbeafe"
ABU, ABU_MUDA = "#475569", "#e2e8f0"
HIJAU, HIJAU_MUDA = "#15803d", "#dcfce7"
KUNING, KUNING_MUDA = "#b45309", "#fef3c7"
MERAH, MERAH_MUDA = "#b91c1c", "#fee2e2"
UNGU, UNGU_MUDA = "#6d28d9", "#ede9fe"
GELAP = "#0f172a"

plt.rcParams["font.family"] = "DejaVu Sans"


def fig(xmax, ymax, w, h):
    f, ax = plt.subplots(figsize=(w, h))
    ax.set_xlim(0, xmax)
    ax.set_ylim(0, ymax)
    ax.axis("off")
    return f, ax


def simpan(f, nama):
    path = os.path.join(OUT, nama)
    f.savefig(path, dpi=200, bbox_inches="tight", facecolor="white")
    plt.close(f)
    print("ok", nama, os.path.getsize(path))


def box(ax, cx, cy, w, h, text, fc="#ffffff", ec=BIRU, fs=10, bold=False, radius=1.5,
        tc=GELAP, lw=1.6, z=2):
    ax.add_patch(FancyBboxPatch((cx - w / 2, cy - h / 2), w, h,
                                boxstyle=f"round,pad=0,rounding_size={radius}",
                                linewidth=lw, edgecolor=ec, facecolor=fc, zorder=z))
    ax.text(cx, cy, text, ha="center", va="center", fontsize=fs, zorder=z + 1, color=tc,
            fontweight="bold" if bold else "normal", linespacing=1.45)


def pill(ax, cx, cy, w, h, text, fs=10, fc=ABU_MUDA, ec=ABU):
    box(ax, cx, cy, w, h, text, fc=fc, ec=ec, fs=fs, radius=h / 2, bold=True)


def ellipse(ax, cx, cy, w, h, text, fc=BIRU_MUDA, ec=BIRU, fs=9):
    ax.add_patch(Ellipse((cx, cy), w, h, linewidth=1.4, edgecolor=ec, facecolor=fc, zorder=2))
    ax.text(cx, cy, text, ha="center", va="center", fontsize=fs, zorder=3, linespacing=1.4)


def diamond(ax, cx, cy, w, h, text, fc=KUNING_MUDA, ec=KUNING, fs=9):
    pts = [(cx, cy + h / 2), (cx + w / 2, cy), (cx, cy - h / 2), (cx - w / 2, cy)]
    ax.add_patch(Polygon(pts, closed=True, linewidth=1.6, edgecolor=ec, facecolor=fc, zorder=2))
    ax.text(cx, cy, text, ha="center", va="center", fontsize=fs, zorder=3, linespacing=1.35)


def panah(ax, p1, p2, warna=ABU, lw=1.5, rad=0.0, gaya="-|>"):
    ax.add_patch(FancyArrowPatch(p1, p2, arrowstyle=gaya, mutation_scale=14,
                                 color=warna, lw=lw, zorder=1, shrinkA=0, shrinkB=0,
                                 connectionstyle=f"arc3,rad={rad}"))


SEGMEN = []
KOTAK = []   # (x1, y1, x2, y2) tabel, agar label tidak ditaruh menimpa tabel


def rute(ax, titik, warna=ABU, lw=1.5):
    for i in range(len(titik) - 2):
        ax.plot([titik[i][0], titik[i + 1][0]], [titik[i][1], titik[i + 1][1]],
                color=warna, lw=lw, zorder=1)
        SEGMEN.append((titik[i], titik[i + 1]))
    panah(ax, titik[-2], titik[-1], warna=warna, lw=lw)
    SEGMEN.append((titik[-2], titik[-1]))


def jarak_segmen(px, py, seg):
    (x1, y1), (x2, y2) = seg
    if x1 == x2:                       # vertikal
        yl, yh = sorted((y1, y2))
        return abs(px - x1) if yl <= py <= yh else min(((px - x1) ** 2 + (py - y1) ** 2) ** 0.5,
                                                       ((px - x1) ** 2 + (py - y2) ** 2) ** 0.5)
    if y1 == y2:                       # horizontal
        xl, xh = sorted((x1, x2))
        return abs(py - y1) if xl <= px <= xh else min(((px - x1) ** 2 + (py - y1) ** 2) ** 0.5,
                                                       ((px - x2) ** 2 + (py - y2) ** 2) ** 0.5)
    return min(((px - x1) ** 2 + (py - y1) ** 2) ** 0.5, ((px - x2) ** 2 + (py - y2) ** 2) ** 0.5)


def label_edge(ax, teks, sekitar_x, sekitar_y, lebar=24):
    """Tempatkan label di kandidat terbaik: jauh dari segmen garis dan di luar tabel."""
    setengah = lebar / 2
    kandidat = [(0, 4.0), (0, -4.0), (0, 6.5), (0, -6.5), (-16, 0), (16, 0),
                (-16, 4.0), (16, 4.0), (-16, -4.0), (16, -4.0), (0, 0)]
    terbaik, skor = None, -1e9
    for dx, dy in kandidat:
        x, y = sekitar_x + dx, sekitar_y + dy
        x1, x2 = x - setengah, x + setengah
        y1, y2 = y - 1.9, y + 1.9
        if x1 < 2 or x2 > 158:
            continue
        tertutup = any(not (x2 < kx1 or x1 > kx2 or y2 < ky1 or y1 > ky2) for kx1, ky1, kx2, ky2 in KOTAK)
        if tertutup:
            continue
        d = min((jarak_segmen(x, y, s) for s in SEGMEN), default=99)
        if d > skor:
            terbaik, skor = (x, y), d
    if terbaik is None:
        terbaik = (sekitar_x, sekitar_y + 4.0)
    teks_pil(ax, terbaik[0], terbaik[1], teks, "#334155")
    return skor


def teks_pil(ax, x, y, teks, warna=ABU, fs=8.4, ha="center"):
    ax.text(x, y, teks, ha=ha, va="center", fontsize=fs, color=warna, zorder=6,
            bbox=dict(boxstyle="round,pad=0.22", fc="white", ec="none"))


def aktor(ax, x, y, label, tinggi=11):
    r = tinggi * 0.13
    ax.add_patch(Circle((x, y + tinggi * 0.35), r, edgecolor=ABU, facecolor="white", lw=1.6, zorder=2))
    ax.plot([x, x], [y + tinggi * 0.35 - r, y - tinggi * 0.1], color=ABU, lw=1.6, zorder=2)
    ax.plot([x - tinggi * 0.2, x + tinggi * 0.2], [y + tinggi * 0.12, y + tinggi * 0.12], color=ABU, lw=1.6)
    ax.plot([x, x - tinggi * 0.16], [y - tinggi * 0.1, y - tinggi * 0.32], color=ABU, lw=1.6)
    ax.plot([x, x + tinggi * 0.16], [y - tinggi * 0.1, y - tinggi * 0.32], color=ABU, lw=1.6)
    ax.text(x, y - tinggi * 0.45, label, ha="center", va="top", fontsize=9.5, color=GELAP,
            fontweight="bold", linespacing=1.3)


def nomor(ax, x, y, n, r=2.7):
    ax.add_patch(Circle((x, y), r, facecolor=BIRU, edgecolor="white", lw=1.2, zorder=8))
    ax.text(x, y, str(n), color="white", fontsize=8, ha="center", va="center", zorder=9, fontweight="bold")


def anotasi(ax, x_kotak, x_teks, y, n, teks, x_isi):
    ax.plot([x_kotak, x_isi], [y, y], color="#94a3b8", lw=0.9, zorder=6)
    nomor(ax, x_kotak, y, n)
    ax.text(x_teks, y, teks, fontsize=8.4, color=ABU, ha="left", va="center", zorder=7)


# ============================== 1. USE CASE ==============================
f, ax = fig(150, 108, 14, 11)
ax.text(75, 105.5, "Use Case Diagram - Siaga Warga", ha="center", fontsize=16, fontweight="bold", color=BIRU_TUA)
ax.add_patch(FancyBboxPatch((22, 8), 106, 92, boxstyle="round,pad=0.6,rounding_size=2",
                            linewidth=2, edgecolor=BIRU_TUA, facecolor="#f8fbff", zorder=0))
ax.text(75, 96.5, "Sistem Siaga Warga", ha="center", fontsize=12, fontweight="bold", color=BIRU_TUA)

uc_warga = ["Mendaftar", "Masuk (login)", "Mengirim Laporan", "Melampirkan Foto Bukti",
            "Memantau Status Laporan", "Menambah Tanggapan", "Membaca Pengumuman"]
uc_pengurus = ["Menerima Laporan Masuk", "Melihat Dashboard Ringkasan", "Memverifikasi Laporan",
               "Mengubah Status Laporan", "Menetapkan Prioritas", "Menanggapi Laporan",
               "Mempublikasikan Pengumuman"]
y0, jarak = 86.5, 11
for i, t in enumerate(uc_warga):
    ellipse(ax, 47, y0 - i * jarak, 40, 8.6, t)
for i, t in enumerate(uc_pengurus):
    ellipse(ax, 102, y0 - i * jarak, 40, 8.6, t, fc=HIJAU_MUDA, ec=HIJAU)
ellipse(ax, 75, 10.5, 54, 9, "Mengelola Basis Data Pengguna", fc=UNGU_MUDA, ec=UNGU)

aktor(ax, 8, 70, "Warga\n(18-60 tahun)")
aktor(ax, 141, 70, "Pengurus\nRT/RW")
aktor(ax, 141, 17, "Admin\nSistem")

for i in range(len(uc_warga)):
    panah(ax, (12, 70), (27, y0 - i * jarak), warna="#94a3b8", lw=1.1, gaya="-")
for i in range(len(uc_pengurus)):
    panah(ax, (137, 70), (122, y0 - i * jarak), warna="#94a3b8", lw=1.1, gaya="-")
panah(ax, (137, 17), (102, 15), warna="#94a3b8", lw=1.1, gaya="-")

ax.text(75, 4.5, "Biru = hak akses Warga   -   Hijau = hak akses Pengurus RT/RW   -   Ungu = Admin Sistem",
        ha="center", fontsize=9.4, color=ABU)
simpan(f, "01-use-case.png")

# ============================== 2. FLOWCHART ==============================
def flowchart(nama, judul, langkah, cabang=None, label_spine=None):
    celah, atas, lebar = 6.0, 7.0, 44
    total = atas + sum(h for _, _, h in langkah) + celah * (len(langkah) - 1) + 4
    skala = total / 150.0
    f, ax = fig(150, total, 11.5, max(11.5, 11.5 * skala))
    ax.text(75, total - 3, judul, ha="center", fontsize=14, fontweight="bold", color=BIRU_TUA)

    pusat, posisi, y = 72, [], total - atas
    for tipe, teks, h in langkah:
        cy = y - h / 2
        if tipe == "mulai" or tipe == "selesai":
            fc = HIJAU_MUDA if tipe == "selesai" else ABU_MUDA
            ec = HIJAU if tipe == "selesai" else ABU
            pill(ax, pusat, cy, 24, h, teks, fs=10, fc=fc, ec=ec)
        elif tipe == "keputusan":
            diamond(ax, pusat, cy, 34, h, teks, fc=KUNING_MUDA, ec=KUNING, fs=9.2)
        elif tipe == "siap":
            box(ax, pusat, cy, lebar, h, teks, fc=UNGU_MUDA, ec=UNGU, fs=9.5)
        else:
            box(ax, pusat, cy, lebar, h, teks, fc="#ffffff", ec=BIRU, fs=9.5)
        setengah = {"keputusan": 17.0, "mulai": 12.0, "selesai": 12.0}.get(tipe, lebar / 2)
        posisi.append(dict(cy=cy, atas=y, bawah=y - h, h=h,
                           bentuk=tipe, kiri=pusat - setengah, kanan=pusat + setengah))
        y = y - h - celah

    for i in range(len(posisi) - 1):
        panah(ax, (pusat, posisi[i]["bawah"]), (pusat, posisi[i + 1]["atas"]))
        if label_spine and i in label_spine:
            teks_pil(ax, pusat + 6, (posisi[i]["bawah"] + posisi[i + 1]["atas"]) / 2,
                     label_spine[i], "#15803d")

    for cab in cabang or []:
        i, teks, label, target = cab["dari"], cab["teks"], cab["label"], cab["ke"]
        p = posisi[i]
        kanan = cab.get("arah", "kanan") == "kanan"
        bx = 128 if kanan else 19
        h_box = max(p["h"], 9)
        box(ax, bx, p["cy"], 34, h_box, teks, fc=MERAH_MUDA, ec=MERAH, fs=8.8)
        if kanan:
            panah(ax, (p["kanan"], p["cy"]), (bx - 17, p["cy"]))
            teks_pil(ax, (p["kanan"] + bx - 17) / 2, p["cy"] + 2.4, label, MERAH)
            if target is not None:
                t = posisi[target]
                rute(ax, [(bx, p["cy"] + h_box / 2), (bx, t["cy"]), (t["kanan"], t["cy"])],
                     warna=MERAH, lw=1.4)
                if cab.get("teks_balik"):
                    teks_pil(ax, bx, (p["cy"] + t["cy"]) / 2, cab["teks_balik"], MERAH, fs=8.2)
        else:
            panah(ax, (p["kiri"], p["cy"]), (bx + 17, p["cy"]))
            teks_pil(ax, (p["kiri"] + bx + 17) / 2, p["cy"] + 2.4, label, MERAH)
            if target is not None:
                t = posisi[target]
                rute(ax, [(bx, p["cy"] + h_box / 2), (bx, t["cy"]), (t["kiri"], t["cy"])],
                     warna=MERAH, lw=1.4)
                if cab.get("teks_balik"):
                    teks_pil(ax, bx, (p["cy"] + t["cy"]) / 2, cab["teks_balik"], MERAH, fs=8.2)

    ax.text(75, 1.6, "Hijau = selesai   -   Kuning = keputusan   -   Merah = jalur pengecualian",
            ha="center", fontsize=8.8, color=ABU)
    simpan(f, nama)


flowchart(
    "02-flowchart-warga.png", "Flowchart Alur Warga",
    [("mulai", "Mulai", 7),
     ("proses", "Buka aplikasi, masuk atau\ndaftar akun warga", 10),
     ("siap", "Lihat dashboard warga\n(ringkasan laporan sendiri)", 10),
     ("proses", "Pilih menu Buat Laporan", 8),
     ("proses", "Isi kategori, deskripsi, lokasi\nacuan, dan foto bukti", 10),
     ("keputusan", "Data valid?", 14),
     ("proses", "Simpan laporan: status Dikirim\ndan nomor tiket SW-0001", 10),
     ("siap", "Pantau linimasa status\npada halaman laporan", 10),
     ("keputusan", "Status akhir\nselesai atau ditolak?", 15),
     ("selesai", "Selesai", 7)],
    cabang=[{"dari": 5, "teks": "Tampilkan pesan\nkesalahan pada\nformulir", "label": "Tidak", "ke": 4,
              "arah": "kanan", "teks_balik": "perbaiki data"},
            {"dari": 8, "teks": "Tambah tanggapan\natau informasi baru", "label": "Belum", "ke": 7,
              "arah": "kiri", "teks_balik": "pantau lagi"}],
    label_spine={5: "Ya", 8: "Ya"},
)

flowchart(
    "03-flowchart-pengurus.png", "Flowchart Alur Pengurus RT/RW",
    [("mulai", "Mulai", 7),
     ("proses", "Masuk sebagai pengurus RT/RW", 8),
     ("siap", "Dashboard ringkasan: jumlah laporan\nper status dan per prioritas", 10),
     ("proses", "Buka menu Laporan Masuk", 8),
     ("proses", "Antrean otomatis: prioritas tinggi\ndulu, lalu paling lama menunggu", 10),
     ("proses", "Pilih laporan, periksa deskripsi,\nlokasi acuan, dan foto bukti", 10),
     ("keputusan", "Laporan valid?", 14),
     ("proses", "Ubah status (Diverifikasi/Diproses),\ntetapkan prioritas, tulis tanggapan", 11),
     ("proses", "Sistem mencatat waktu respons\n(ditangani_pada) untuk laporan itu", 10),
     ("keputusan", "Pekerjaan\ntuntas?", 14),
     ("selesai", "Selesai", 7)],
    cabang=[{"dari": 6, "teks": "Tolak laporan dan tulis\nalasannya (status Ditolak),\nlanjut ke laporan berikutnya", "label": "Tidak", "ke": 4,
              "arah": "kanan", "teks_balik": "ambil laporan berikutnya"},
            {"dari": 9, "teks": "Tangani laporan\nberikutnya", "label": "Belum", "ke": 5,
              "arah": "kiri", "teks_balik": "tangani berikutnya"}],
    label_spine={6: "Ya", 9: "Ya"},
)

flowchart(
    "04-flowchart-admin.png", "Flowchart Alur Admin Sistem",
    [("mulai", "Mulai", 7),
     ("proses", "Masuk sebagai admin sistem", 8),
     ("siap", "Halaman Kelola Pengguna: daftar\npengguna + filter peran", 10),
     ("proses", "Pilih aksi: tambah pengguna,\nubah peran, atau hapus pengguna", 10),
     ("keputusan", "Data valid?", 14),
     ("keputusan", "Akun admin terakhir\nyang diubah?", 14),
     ("proses", "Simpan perubahan ke tabel users", 8),
     ("selesai", "Selesai", 7)],
    cabang=[{"dari": 4, "teks": "Tampilkan pesan kesalahan\nvalidasi: email harus unik,\nkata sandi minimal 8 karakter",
              "label": "Tidak", "ke": 3, "arah": "kiri", "teks_balik": "perbaiki data"},
            {"dari": 5, "teks": "Tolak perubahan: sistem wajib\nmenyisakan minimal satu admin",
              "label": "Ya", "ke": 7, "arah": "kanan"}],
    label_spine={4: "Ya", 5: "Tidak"},
)

# ============================== 3. ERD ==============================
f, ax = fig(160, 118, 15.5, 11.6)
ax.text(80, 115.6, "Entity Relationship Diagram - Siaga Warga", ha="center", fontsize=16,
        fontweight="bold", color=BIRU_TUA)

# Koridor vertikal yang bebas dari kotak tabel (dihitung dari tata letak di bawah)
K_DITANGANI = 51.0   # antara users (x<=47) dan laporans (x>=63)
K_KATEGORI = 109.0   # antara laporans (x<=105) dan tanggapans (x>=119)
K_TANGGAPAN = 114.0


def tabel(ax, cx, cy, judul, fields, w=30, ec=BIRU, fc=BIRU_MUDA, br=5.0):
    h = br * (len(fields) + 1) + 1.6
    top = cy + h / 2
    ax.add_patch(FancyBboxPatch((cx - w / 2, cy - h / 2), w, h,
                                boxstyle="round,pad=0,rounding_size=1.2",
                                linewidth=1.8, edgecolor=ec, facecolor="white", zorder=3))
    ax.add_patch(Rectangle((cx - w / 2, top - br - 1), w, br + 1,
                           facecolor=fc, edgecolor=ec, linewidth=1.6, zorder=4))
    ax.text(cx, top - (br + 1) / 2, judul, ha="center", va="center", fontsize=10.5,
            fontweight="bold", zorder=5, color=GELAP)
    baris = {}
    for i, (nama, ket) in enumerate(fields):
        y = top - br - 1 - br * (i + 0.5)
        baris[nama] = y
        ax.text(cx - w / 2 + 1.8, y, nama, ha="left", va="center", fontsize=8.2, zorder=5,
                fontweight="bold" if nama == "id" else "normal")
        if ket:
            ax.text(cx + w / 2 - 1.8, y, ket, ha="right", va="center", fontsize=7.2,
                    color=ABU, zorder=5, style="italic")
    KOTAK.append((cx - w / 2, cy - h / 2, cx + w / 2, top))
    return dict(cx=cx, cy=cy, w=w, h=h, br=br, kiri=cx - w / 2, kanan=cx + w / 2,
                atas=top, bawah=cy - h / 2, baris=baris)


u = tabel(ax, 30, 82, "users", [("id", "PK"), ("name", ""), ("email", "unik"),
                                ("password", ""), ("role", "warga|pengurus|admin"),
                                ("rt_rw", ""), ("telepon", "")], w=34)
l = tabel(ax, 84, 64, "laporans", [("id", "PK"), ("user_id", "FK -> users"),
                                   ("kategori_masalah_id", "FK -> kategori"),
                                   ("judul", ""), ("deskripsi", ""), ("lokasi", "acuan teks"),
                                   ("foto", "opsional"), ("prioritas", "rendah|sedang|tinggi"),
                                   ("status", "dikirim..selesai|ditolak"),
                                   ("ditangani_oleh", "FK -> users"),
                                   ("ditangani_pada", "waktu respons")], w=42)
t = tabel(ax, 134, 86, "tanggapans", [("id", "PK"), ("laporan_id", "FK -> laporans"),
                                      ("user_id", "FK -> users"), ("isi", ""),
                                      ("status_baru", "opsional")], w=30, ec=HIJAU, fc=HIJAU_MUDA)
pm = tabel(ax, 30, 26, "pengumumans", [("id", "PK"), ("user_id", "FK -> users"),
                                       ("judul", ""), ("isi", ""),
                                       ("berlaku_sampai", "opsional")], w=34, ec=UNGU, fc=UNGU_MUDA)
k = tabel(ax, 134, 26, "kategori_masalahs", [("id", "PK"), ("nama", "unik"), ("deskripsi", "")],
          w=30, ec=KUNING, fc=KUNING_MUDA)

# 1) users 1:N laporans (pelapor) -> laporans.user_id
y = l["baris"]["user_id"]
rute(ax, [(u["kanan"], y), (l["kiri"], y)], warna="#64748b")
label_edge(ax, "1 : N  (pelapor)", 55, y, lebar=22)

# 2) users 1:N laporans (petugas penanganan) -> laporans.ditangani_oleh
y = l["baris"]["ditangani_oleh"]
rute(ax, [(u["kanan"], u["baris"]["telepon"]), (K_DITANGANI, u["baris"]["telepon"]),
          (K_DITANGANI, y), (l["kiri"], y)], warna="#64748b")
label_edge(ax, "1 : N  (ditangani_oleh)", K_DITANGANI, (y + u["baris"]["telepon"]) / 2, lebar=30)

# 3) kategori_masalahs 1:N laporans -> laporans.kategori_masalah_id
y = l["baris"]["kategori_masalah_id"]
rute(ax, [(k["kiri"], k["baris"]["nama"]), (K_KATEGORI, k["baris"]["nama"]),
          (K_KATEGORI, y), (l["kanan"], y)], warna="#64748b")
label_edge(ax, "1 : N  (kategori)", K_KATEGORI, k["baris"]["nama"], lebar=24)

# 4) laporans 1:N tanggapans -> tanggapans.laporan_id (masuk sisi kiri tepat di baris itu)
y1, y2 = l["baris"]["deskripsi"], t["baris"]["laporan_id"]
rute(ax, [(l["kanan"], y1), (K_TANGGAPAN, y1), (K_TANGGAPAN, y2), (t["kiri"], y2)], warna="#64748b")
label_edge(ax, "1 : N  (tanggapan)", K_TANGGAPAN, (y1 + y2) / 2, lebar=24)

# 5) users 1:N tanggapans (penulis) -> tanggapans.user_id, lewat koridor atas
y = t["baris"]["user_id"]
rute(ax, [(u["cx"] - 10, u["atas"]), (u["cx"] - 10, 110), (t["kanan"] + 9, 110),
          (t["kanan"] + 9, y), (t["kanan"], y)], warna="#64748b")
label_edge(ax, "1 : N  (penulis tanggapan)", 84, 106.5, lebar=30)

# 6) users 1:N pengumumans -> pengumumans.user_id, lewat koridor kiri
y1, y2 = u["baris"]["rt_rw"], pm["baris"]["user_id"]
rute(ax, [(u["kiri"], y1), (6, y1), (6, y2), (pm["kiri"], y2)], warna="#64748b")
label_edge(ax, "1 : N  (pengumuman)", 6, (y1 + y2) / 2, lebar=24)

ax.text(80, 4.5, "PK = primary key   -   FK = foreign key   -   relasi 1 : N = satu baris kiri memiliki banyak baris kanan",
        ha="center", fontsize=9, color=ABU)
simpan(f, "05-erd.png")

print("peta baris: users.telepon=%.1f laporans.user_id=%.1f ditangani_oleh=%.1f kategori=%.1f tanggapans.user_id=%.1f pm.user_id=%.1f"
      % (u["baris"]["telepon"], l["baris"]["user_id"], l["baris"]["ditangani_oleh"],
         l["baris"]["kategori_masalah_id"], t["baris"]["user_id"], pm["baris"]["user_id"]))
print("kotak: users x %.1f..%.1f y %.1f..%.1f | laporans x %.1f..%.1f y %.1f..%.1f | pm x %.1f..%.1f y %.1f..%.1f | t x %.1f..%.1f y %.1f..%.1f | k x %.1f..%.1f y %.1f..%.1f"
      % (u["kiri"], u["kanan"], u["bawah"], u["atas"], l["kiri"], l["kanan"], l["bawah"], l["atas"],
         pm["kiri"], pm["kanan"], pm["bawah"], pm["atas"], t["kiri"], t["kanan"], t["bawah"], t["atas"],
         k["kiri"], k["kanan"], k["bawah"], k["atas"]))

# ============================== 4. WIREFRAME ==============================
def telpon(ax, x=40, w=76, h=180, y=6):
    ax.add_patch(FancyBboxPatch((x - w / 2, y), w, h, boxstyle="round,pad=0,rounding_size=5",
                                linewidth=2.4, edgecolor="#334155", facecolor="white", zorder=1))
    ax.add_patch(FancyBboxPatch((x - w / 2 + 1.2, y + 1.2), w - 2.4, h - 2.4,
                                boxstyle="round,pad=0,rounding_size=4", linewidth=0,
                                facecolor="#f6f8fb", zorder=2))
    ax.add_patch(Rectangle((x - 7, y + h - 3.4), 14, 1.6, facecolor="#334155", zorder=3))
    return dict(kiri=x - w / 2, kanan=x + w / 2, bawah=y, atas=y + h)


f, ax = fig(180, 196, 9.5, 11)
ax.text(40, 193.5, "Wireframe Mobile - Dashboard Warga", ha="center", fontsize=14.5,
        fontweight="bold", color=BIRU_TUA)
ph = telpon(ax, h=178, y=8)
XL, XR = ph["kiri"], ph["kanan"]
LC, LT = XR + 22, XR + 26
X = XR + 1.2   # berhenti di tepi luar frame

top = ph["atas"] - 2
ax.add_patch(Rectangle((XL + 1.2, top - 9), 73.6, 9, facecolor="white", edgecolor="#e2e8f0", zorder=3))
ax.text(XL + 4, top - 4.5, "SiagaWarga", fontsize=9.5, color=BIRU_TUA, va="center", zorder=6, fontweight="bold")
ax.text(XR - 4, top - 4.5, "Warga", fontsize=7.6, color=ABU, va="center", ha="right", zorder=6)
anotasi(ax, LC, LT, top - 4.5, 1, "App bar: merek + identitas pengguna", X)

ax.text(XL + 4, top - 15, "Halo, Therta", fontsize=10.5, color=GELAP, va="center", zorder=6)
ax.text(XL + 4, top - 20, "Pantau laporan fasilitas umum Anda", fontsize=7.6, color=ABU, va="center", zorder=6)
box(ax, XL + 18, top - 27, 30, 6.4, "+ Buat Laporan", fc=BIRU, ec=BIRU, fs=7.8, tc="white", radius=2, z=6)
anotasi(ax, LC, LT, top - 27, 2, "Tombol utama: buat laporan baru", X)

for i, (angka, teks) in enumerate([("4", "Total laporan"), ("3", "Sedang ditangani"), ("1", "Selesai")]):
    x = XL + 1.2 + i * 24.4
    ax.add_patch(FancyBboxPatch((x, top - 48), 22.8, 15, boxstyle="round,pad=0,rounding_size=2",
                                facecolor="white", edgecolor="#e2e8f0", lw=1.3, zorder=4))
    ax.text(x + 2.4, top - 40, angka, fontsize=11, color=BIRU_TUA, va="center", zorder=6, fontweight="bold")
    ax.text(x + 2.4, top - 45, teks, fontsize=6.4, color=ABU, va="center", zorder=6)
anotasi(ax, LC, LT, top - 40, 3, "Ringkasan jumlah laporan warga", X)

ax.text(XL + 4, top - 55, "Laporan terbaru", fontsize=9.2, color=GELAP, va="center", zorder=6, fontweight="bold")
judul_lap = ["Lampu jalan mati depan Blok C", "Tumpukan sampah dekat pos ronda", "Selokan tersumbat sampah plastik"]
meta_lap = ["SW-0001 - Lampu Jalan", "SW-0002 - Sampah & Kebersihan", "SW-0003 - Saluran Air"]
badge = [("Diproses", KUNING, KUNING_MUDA), ("Diverifikasi", UNGU, UNGU_MUDA), ("Dikirim", BIRU, BIRU_MUDA)]
prio = [("P. Tinggi", MERAH), ("P. Sedang", KUNING), ("P. Tinggi", MERAH)]
for i in range(3):
    y = top - 71 - i * 17.5
    ax.add_patch(FancyBboxPatch((XL + 1.2, y), 73.6, 15, boxstyle="round,pad=0,rounding_size=2",
                                facecolor="white", edgecolor="#e2e8f0", lw=1.3, zorder=4))
    ax.text(XL + 4, y + 10.8, judul_lap[i], fontsize=7.4, color=GELAP, va="center", zorder=6)
    ax.text(XL + 4, y + 6.8, meta_lap[i], fontsize=6.2, color=ABU, va="center", zorder=6)
    box(ax, XL + 11, y + 2.8, 15, 3.6, badge[i][0], fc=badge[i][2], ec=badge[i][1], fs=5.8, radius=1.8, lw=1, z=6)
    box(ax, XL + 28.5, y + 2.8, 14, 3.6, prio[i][0], fc=MERAH_MUDA, ec=prio[i][1], fs=5.8, radius=1.8, lw=1, z=6)
anotasi(ax, LC, LT, top - 63, 4, "Judul + tiket + kategori laporan", X)
anotasi(ax, LC, LT, top - 76, 5, "Badge status laporan", X)
anotasi(ax, LC, LT, top - 88, 6, "Badge prioritas laporan", X)

ax.add_patch(FancyBboxPatch((XL + 1.2, top - 136), 73.6, 11, boxstyle="round,pad=0,rounding_size=2",
                            facecolor="white", edgecolor="#e2e8f0", lw=1.3, zorder=4))
ax.text(XL + 4, top - 130.5, "Pengumuman: Kerja Bakti Minggu Pagi", fontsize=7.4, color=GELAP,
        va="center", zorder=6)
anotasi(ax, LC, LT, top - 130.5, 7, "Pengumuman resmi dari pengurus", X)

ax.add_patch(Rectangle((XL + 1.2, ph["bawah"] + 1.2), 73.6, 8.6, facecolor="white",
                       edgecolor="#e2e8f0", zorder=4))
for i, nav in enumerate(["Dashboard", "Laporan", "Pengumuman"]):
    ax.text(XL + 13 + i * 24, ph["bawah"] + 5.5, nav, fontsize=6.8, zorder=6, ha="center",
            color=BIRU if i == 0 else ABU, fontweight="bold" if i == 0 else "normal")
anotasi(ax, LC, LT, ph["bawah"] + 5.5, 8, "Navigasi utama tiga menu warga", X)
simpan(f, "06-wireframe-mobile-dashboard.png")

f, ax = fig(180, 160, 9.5, 9)
ax.text(40, 157.5, "Wireframe Mobile - Formulir Buat Laporan", ha="center", fontsize=14.5,
        fontweight="bold", color=BIRU_TUA)
ph = telpon(ax, h=142, y=6)
XL, XR = ph["kiri"], ph["kanan"]
LC, LT = XR + 22, XR + 26
X = XR + 1.2   # berhenti di tepi luar frame
top = ph["atas"] - 2
ax.add_patch(Rectangle((XL + 1.2, top - 9), 73.6, 9, facecolor="white", edgecolor="#e2e8f0", zorder=3))
ax.text(XL + 4, top - 4.5, "<-  Buat Laporan Baru", fontsize=8.6, color=BIRU_TUA, va="center",
        zorder=6, fontweight="bold")
anotasi(ax, LC, LT, top - 4.5, 1, "Header halaman + tombol kembali", X)


def bidang(ax, y, label, placeholder, h=7.4, ada_bantuan=False):
    ax.text(XL + 3, y, label, fontsize=7.2, color=GELAP, va="center", zorder=6)
    ax.add_patch(FancyBboxPatch((XL + 3, y - h - 1), 70, h, boxstyle="round,pad=0,rounding_size=2",
                                facecolor="white", edgecolor="#cbd5e1", lw=1.2, zorder=4))
    ax.text(XL + 5, y - h / 2 - 1, placeholder, fontsize=6.6, color=ABU, va="center", zorder=6)
    return y - h - (7.5 if ada_bantuan else 5.5)


y = top - 15
y = bidang(ax, y, "Kategori masalah *", "- pilih kategori -")
anotasi(ax, LC, LT, y + 10, 2, "Kategori masalah (wajib)", X)
y = bidang(ax, y, "Perkiraan prioritas *", "Sedang - perlu segera")
anotasi(ax, LC, LT, y + 10, 3, "Perkiraan prioritas (wajib)", X)
y = bidang(ax, y, "Judul laporan *", "Lampu jalan mati depan Blok C")
anotasi(ax, LC, LT, y + 10, 4, "Judul laporan (wajib)", X)
y = bidang(ax, y, "Deskripsi masalah *", "Jelaskan kondisi, sejak kapan,\ndan dampaknya bagi warga", h=13)
anotasi(ax, LC, LT, y + 15, 5, "Deskripsi masalah (wajib)", X)
y = bidang(ax, y, "Lokasi acuan *", "Depan Blok C No. 12", ada_bantuan=True)
ax.text(XL + 3, y + 8.5, "Patokan yang mudah dikenali petugas", fontsize=6.2, color=ABU, va="center", zorder=6)
anotasi(ax, LC, LT, y + 10.5, 6, "Lokasi acuan teks + petunjuknya", X)
y = bidang(ax, y, "Foto bukti (opsional)", "Pilih berkas... JPG/PNG maks. 2 MB")
anotasi(ax, LC, LT, y + 10, 7, "Foto bukti (opsional)", X)
box(ax, XL + 22, y - 6.5, 38, 7, "Kirim Laporan", fc=BIRU, ec=BIRU, fs=7.6, tc="white", radius=2, z=6)
box(ax, XL + 56, y - 6.5, 20, 7, "Batal", fc="white", ec=BIRU, fs=7.6, radius=2, z=6)
anotasi(ax, LC, LT, y - 6.5, 8, "Tombol simpan dan batal", X)
simpan(f, "07-wireframe-mobile-form.png")

f, ax = fig(215, 112, 16, 9)
ax.text(88, 109, "Wireframe Desktop - Dashboard Pengurus RT/RW", ha="center", fontsize=15,
        fontweight="bold", color=BIRU_TUA)
FL, FR, FB, FT = 6, 170, 6, 100
ax.add_patch(FancyBboxPatch((FL, FB), FR - FL, FT - FB, boxstyle="round,pad=0,rounding_size=2.5",
                            linewidth=2, edgecolor="#334155", facecolor="white", zorder=1))
LC, LT = FR + 12, FR + 16
X = FR + 1.2   # berhenti di tepi luar frame

ax.add_patch(Rectangle((FL + 0.6, FT - 11), FR - FL - 1.2, 10.4, facecolor="white",
                       edgecolor="#e2e8f0", zorder=2))
ax.text(FL + 4, FT - 5.8, "SiagaWarga", fontsize=9.4, color=BIRU_TUA, va="center", zorder=6, fontweight="bold")
for i, nav in enumerate(["Dashboard", "Laporan Masuk", "Pengumuman", "Kelola Pengguna"]):
    x = FL + 28 + i * 24
    ax.add_patch(FancyBboxPatch((x, FT - 9.4), 22, 6.4, boxstyle="round,pad=0,rounding_size=2",
                                facecolor=BIRU if i == 0 else "#eef2ff", edgecolor="none", zorder=4))
    ax.text(x + 11, FT - 6.2, nav, fontsize=6.8, zorder=6, ha="center", va="center",
            color="white" if i == 0 else BIRU, fontweight="bold" if i == 0 else "normal")
ax.text(FR - 24, FT - 3.6, "Budi Santoso - Pengurus RT/RW", fontsize=6.8, color=ABU,
        va="center", ha="right", zorder=6)
box(ax, FR - 8, FT - 8.4, 12, 5.6, "Keluar", fc="white", ec=BIRU, fs=6.4, radius=1.6, lw=1.2, z=6)
anotasi(ax, LC, LT, FT - 4.6, 1, "Navigasi menyesuaikan peran pengguna", X)

ax.text(FL + 4, FT - 14, "Dashboard Pengurus RT/RW", fontsize=11, color=GELAP, va="center",
        zorder=6, fontweight="bold")
ax.text(FL + 4, FT - 18.5, "Ringkasan pengaduan warga berdasarkan prioritas dan status penanganan",
        fontsize=7.2, color=ABU, va="center", zorder=6)
box(ax, FR - 30, FT - 16, 44, 6.6, "Kelola Laporan Masuk", fc=BIRU, ec=BIRU, fs=7.4,
    tc="white", radius=2, z=6)
anotasi(ax, LC, LT, FT - 16, 2, "Aksi utama pengurus", X)

for i, (angka, teks) in enumerate([("5", "Total laporan"), ("2", "Prioritas tinggi"), ("1", "Menunggu"),
                                   ("1", "Diverifikasi"), ("2", "Diproses"), ("1", "Selesai")]):
    x = FL + 2 + i * 26.6
    ax.add_patch(FancyBboxPatch((x, FT - 34), 24, 10, boxstyle="round,pad=0,rounding_size=2",
                                facecolor="white", edgecolor="#e2e8f0", lw=1.3, zorder=4))
    ax.text(x + 3, FT - 29.5, angka, fontsize=10.5, color=BIRU_TUA, va="center", zorder=6, fontweight="bold")
    ax.text(x + 3, FT - 32.5, teks, fontsize=6.4, color=ABU, va="center", zorder=6)
anotasi(ax, LC, LT, FT - 29, 3, "Metrik jumlah laporan per status", X)

ax.text(FL + 4, FT - 38.5, "Laporan terbaru", fontsize=9.2, color=GELAP, va="center",
        zorder=6, fontweight="bold")
ax.add_patch(FancyBboxPatch((FL + 2, FB + 12), FR - FL - 8, 40, boxstyle="round,pad=0,rounding_size=2",
                            facecolor="white", edgecolor="#e2e8f0", lw=1.3, zorder=3))
ax.add_patch(Rectangle((FL + 2, FB + 46), FR - FL - 8, 6, facecolor="#f1f5f9", edgecolor="none", zorder=4))
for x, nama in [(FL + 5, "TIKET"), (FL + 15, "JUDUL & LOKASI"), (FL + 74, "PELAPOR"),
                (FL + 96, "STATUS"), (FL + 116, "PRIORITAS")]:
    ax.text(x, FB + 49, nama, fontsize=6.4, color=ABU, va="center", zorder=6)
baris = [("SW-0001", "Lampu jalan mati depan Blok C", "Depan Blok C No. 12", "Diproses", KUNING, "P. Tinggi", MERAH),
         ("SW-0002", "Tumpukan sampah dekat pos ronda", "Samping pos ronda RT 04", "Diverifikasi", UNGU, "P. Sedang", KUNING),
         ("SW-0003", "Selokan tersumbat sampah plastik", "Pertigaan Gang Melati", "Dikirim", BIRU, "P. Tinggi", MERAH),
         ("SW-0004", "Jalan berlubang di depan masjid", "Jl. Kenanga depan Masjid", "Selesai", HIJAU, "P. Sedang", KUNING)]
for i, (tiket, judul, lokasi, status, w_status, pr, w_pr) in enumerate(baris):
    y = FB + 40 - i * 7.6
    ax.text(FL + 5, y, tiket, fontsize=6.6, color=GELAP, va="center", zorder=6)
    ax.text(FL + 15, y + 1.2, judul, fontsize=6.6, color=GELAP, va="center", zorder=6)
    ax.text(FL + 15, y - 2.2, lokasi, fontsize=5.8, color=ABU, va="center", zorder=6)
    ax.text(FL + 74, y, "Therta R. S.", fontsize=6.2, color=ABU, va="center", zorder=6)
    ax.text(FL + 96, y, status, fontsize=6.2, color=w_status, va="center", zorder=6, fontweight="bold")
    ax.text(FL + 116, y, pr, fontsize=6.2, color=w_pr, va="center", zorder=6, fontweight="bold")
    box(ax, FL + 146, y, 13, 4.4, "Tangani", fc=BIRU, ec=BIRU, fs=5.8, tc="white", radius=1.6, z=6)
anotasi(ax, LC, LT, FT - 48, 4, "Antrean laporan: prioritas tinggi lebih dulu,\nlalu laporan yang paling lama menunggu", X)

box(ax, FL + (FR - FL) / 2, FB + 7, FR - FL - 10, 7.4,
    "Pengumuman aktif:  Kerja Bakti Minggu Pagi   -   Kerja Bakti Membersihkan Saluran Air",
    fc="white", ec="#e2e8f0", fs=6.8, radius=2, lw=1.3, z=6)
anotasi(ax, LC, LT, FB + 7, 5, "Modul pengumuman resmi pengurus", X)
simpan(f, "08-wireframe-desktop-pengurus.png")

print("selesai")
