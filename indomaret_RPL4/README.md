# Aplikasi POS Kasir Indomaret RPL 4

Aplikasi Point of Sales (POS) Kasir berbasis PHP Native & MySQL, dirancang sesuai dengan arsitektur folder dan skema basis data `indomaret_RPL4`.

---

## 📁 Struktur Direktori & File

```text
indomaret_RPL4/
├── assets/
│   ├── css/
│   │   ├── bootstrap.css       # Framework CSS Bootstrap 5
│   │   └── style.css           # Styling kustom bertema Indomaret
│   ├── js/
│   │   ├── bootstrap.js        # Script interaktif Bootstrap 5
│   │   └── script.js           # Logika realtime POS & kalkulasi kasir
│   └── img/
│       └── logo.svg            # Logo Indomaret
├── config/
│   └── config.php              # Konfigurasi database MySQL & helper URL
├── includes/
│   ├── header.php              # Navigasi & header halaman
│   └── footer.php              # Bagian footer & script loader
├── pages/
│   ├── dashboard.php           # Beranda & ringkasan metrik statistik
│   ├── cashiers/
│   │   ├── add.php             # Form tambah data kasir
│   │   ├── edit.php            # Form edit data kasir
│   │   └── list.php            # Daftar & tabel data kasir
│   ├── donations/
│   │   ├── add.php             # Form tambah program donasi
│   │   ├── edit.php            # Form edit data donasi
│   │   └── list.php            # Daftar & tabel donasi
│   ├── products/
│   │   ├── add.php             # Form tambah produk & harga diskon
│   │   ├── edit.php            # Form edit produk
│   │   └── list.php            # Daftar & tabel inventaris produk
│   └── transactions/
│       ├── add.php             # Kasir POS kasir (input transaksi baru)
│       ├── list.php            # Riwayat seluruh transaksi
│       └── transaction_details.php # Rincian item produk & cetak struk
├── process/
│   ├── cashiers_process.php    # Handler CRUD kasir (Insert, Update, Delete)
│   ├── donations_process.php   # Handler CRUD donasi (Insert, Update, Delete)
│   ├── products_process.php    # Handler CRUD produk (Insert, Update, Delete)
│   └── transactions_process.php # Handler checkout transaksi & ACID rollback
├── database.sql                # Skema DDL lengkap + dummy data awal
├── index.php                   # Entry point aplikasi (redirect ke dashboard)
└── README.md
```

---

## 🗄️ Skema Database (`indomaret_rpl4`)

Aplikasi menggunakan 5 tabel utama:

1. **`tabel_kasir`**: Menyimpan identitas kasir yang bertugas (`id_kasir`, `nama_kasir`, `no_telp`, `alamat`).
2. **`tabel_produk`**: Menyimpan master data produk (`id_produk`, `nama_produk`, `harga`, `diskon`).
3. **`tabel_donasi`**: Menyimpan opsi program donasi sosial Indomaret (`id_donasi`, `nama_donasi`, `harga_donasi`).
4. **`tabel_transaksi`**: Header transaksi belanja (`kode_transaksi`, `tanggal_transaksi`, `no_order`, `total`, `QRIS`, `tunai`, `kembalian`, `id_donasi`).
5. **`tabel_detail_transaksi`**: Rincian setiap item barang yang dibeli (`kode_transaksi`, `id_produk`, `id_kasir`, `harga`, `kuantitas`, `diskon`, `harga_jual`).

---

## 🚀 Panduan Menjalankan Aplikasi di XAMPP

1. **Nyalakan XAMPP**:
   - Buka **XAMPP Control Panel**.
   - Klik tombol **Start** pada modul **Apache** dan **MySQL**.

2. **Impor Database**:
   - Buka browser ke: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Buat database baru bernama: `indomaret_rpl4`
   - Klik tab **Import**, pilih file `database.sql` yang ada di folder ini, lalu klik **Go** / **Kirim**.

3. **Buka Aplikasi**:
   - Akses via browser: [http://localhost/indomaret_RPL4/](http://localhost/indomaret_RPL4/)
   - Atau jika menggunakan PHP built-in server di terminal:
     ```bash
     cd C:\xampp\htdocs\indomaret_RPL4
     php -S localhost:8000
     ```
     Lalu buka [http://localhost:8000](http://localhost:8000).

---

## ✨ Fitur Utama

- **Input Transaksi Sederhana**: Tambah item produk ke daftar belanja secara dinamis dalam satu form transaksi.
- **Kalkulasi Otomatis**: Diskon per item, subtotal, donasi, pembayaran tunai/QRIS, dan kembalian dihitung otomatis.
- **Cetak Struk Sederhana**: Pratinjau struk kasir format teks sederhana siap cetak (`window.print()`).
- **Keamanan Data**: Menggunakan sanitasi input, validasi sisi server, dan transaksi basis data (`BEGIN TRANSACTION ... COMMIT / ROLLBACK`).
