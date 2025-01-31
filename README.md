# 📂 SIMANTAP - Sistem Informasi Manajemen Data Terpadu

![simantapp](https://github.com/user-attachments/assets/a3b06e92-1349-472f-b606-6505ca8bf49f)


**SIMANTAP** adalah sistem arsip digital yang dikembangkan untuk instansi **Kecamatan Bintan Timur** dalam rangka mendukung manajemen data dan dokumen secara efisien. Sistem ini dibuat selama kegiatan kerja praktik oleh tim pengembang dengan tujuan meningkatkan aksesibilitas, keamanan, dan efisiensi pengarsipan dokumen di instansi.

## 📌 Fitur Utama

### 🔹 Divisi PMD (Pemberdayaan Masyarakat dan Desa)
- **Dashboard PMD** – Menampilkan visualisasi jumlah data yang telah diarsipkan.
- **Arsip Data**:
  - **Data OSS** – Penyimpanan dan pengelolaan data OSS.
  - **Data Pembangunan** – Arsip proyek pembangunan.
  - **Data KUBE** – Manajemen arsip program Kelompok Usaha Bersama (KUBE).
- **Operasi CRUD** – Tambah, ubah, hapus, dan baca data.
- **Manajemen Dokumen** – Upload, preview, dan download dokumen fisik terkait.

### 🔹 Divisi Umum dan Kepegawaian
- **Dashboard Umum** – Visualisasi data yang telah diarsipkan.
- **Arsip Data**:
  - **Data SPT** – Arsip Surat Perintah Tugas (SPT).
  - **Data Nota Dinas** – Pengelolaan nota dinas instansi.
  - **Data Surat Masuk** – Pengarsipan surat yang diterima.
  - **Data Surat Keluar** – Penyimpanan dan pengelolaan surat yang dikirim.
- **Operasi CRUD** – Tambah, ubah, hapus, dan baca data.
- **Manajemen Dokumen** – Upload, preview, dan download dokumen fisik terkait.

## 🔑 Hak Akses dan Sistem Login
- Setiap **staf divisi** memiliki akun masing-masing dengan hak akses sesuai divisi.
- **Staf PMD** hanya dapat mengakses dashboard dan arsip data milik divisi PMD.
- **Staf Umum dan Kepegawaian** hanya memiliki akses ke dashboard dan arsip data mereka sendiri.

## 🏗️ Teknologi yang Digunakan
- **Backend**: PHP
- **Frontend**: HTML, CSS
- **Database**: MySQL
- **Version Control**: Git & GitHub

## 🚀 Instalasi dan Penggunaan
1. Clone repository:
   ```bash
   git clone https://github.com/username/SIMANTAP.git
   ```
2. Konfigurasi database:
   - Buat database di MySQL.
   - Import file `.sql` yang tersedia di repository.
   - Ubah konfigurasi koneksi database di `config.php`
3. Jalankan di localhost menggunakan XAMPP atau server PHP lainnya.
   ```bash
   php -S localhost:8000
   ```
4. Akses sistem melalui browser:
   ```
   http://localhost/SIMANTAP
   ```

## 📄 Struktur Direktori
```
/SIMANTAP
│── assets/
│   ├── img/           # Folder untuk gambar
│── uploads/           # Folder penyimpanan file yang diunggah
│── about.php          # Halaman tentang sistem
│── buat_tabel.php     # Skrip untuk membuat tabel database
│── dashboard_pmd.php  # Dashboard untuk divisi PMD
│── dashboard_umum.php # Dashboard untuk divisi Umum
│── index.php          # Halaman utama
│── login_page.php     # Halaman login
│── logout.php         # Logout handler
│── nota_dinas_page.php  # Halaman arsip Nota Dinas
│── oss_page.php         # Halaman arsip Data OSS
│── pembangunan_page.php # Halaman arsip Data Pembangunan
│── spt_page.php         # Halaman arsip Data SPT
│── surat_keluar_page.php # Halaman arsip Surat Keluar
│── surat_masuk_page.php  # Halaman arsip Surat Masuk
└── README.md         # Dokumentasi proyek
```

## 📌 Pengembang
- **Vinandra Adam Saputra** - [GitHub Profile](https://github.com/Vinandra-Adam-Saputra)
- **Niken Dwi Setianingsih** - [GitHub Profile](https://github.com/username)

## 📜 Lisensi
Proyek ini dilisensikan di bawah **[MIT License](LICENSE)**.

