# 🍽️ Panduan Singkat - Sistem Pemesanan QR Code

## Untuk Pelanggan

### Cara Memesan:

1. **Scan QR Code** di meja menggunakan kamera HP
2. **Pilih Menu** yang diinginkan
3. **Klik tombol "+ Tambah"** untuk menambahkan ke keranjang
4. **Klik icon keranjang** (🛒) di kanan bawah untuk melihat pesanan
5. **Klik "Pesan Sekarang"**
6. **Masukkan nama** Anda
7. **Selesai!** Pesanan akan diproses

**Link Langsung** (untuk testing):

```
http://localhost/Tugas_Interface_Cafe/menu.php?table=1
```

_(Ganti angka 1 dengan nomor meja lain)_

---

## Untuk Admin

### Login:

- **URL**: `http://localhost/Tugas_Interface_Cafe/Login.php`
- **Username**: `admin`
- **Password**: `admin123`

### Kelola QR Code:

**URL**: `http://localhost/Tugas_Interface_Cafe/admin/qr_codes.php`

### Membuat QR Code Baru:

1. Masukkan **Nomor Meja** (contoh: 11, VIP-1, dll)
2. Masukkan **Nama Meja** (contoh: Meja 11, Meja VIP)
3. Klik **"Generate QR"**
4. QR Code otomatis dibuat!

### Mencetak QR Code:

1. Klik tombol **"Lihat"** pada meja yang diinginkan
2. Klik **"Cetak QR Code"**
3. Print dan letakkan di meja

---

## Troubleshooting

### Tidak bisa login sebagai admin?

Jalankan: `http://localhost/Tugas_Interface_Cafe/fix_admin.php`

### QR Code tidak muncul?

Pastikan folder `qrcodes/` ada dan bisa ditulis

### Database error?

Cek koneksi di `config.php`

---

## File Penting

- **Menu Guest**: `menu.php`
- **Kelola QR**: `admin/qr_codes.php`
- **Display QR**: `qr_display.php`
- **Proses Order**: `process_guest_order.php`
- **Fix Admin**: `fix_admin.php`

---

Enjoy! 🎉
