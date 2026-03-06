# Kasanova (PHP MySQL Kasir Sederhana)

Project hasil rombak UI/UX total (tema Neon Bento), **fitur & logika transaksi tetap**:
- produk, pelanggan, user, penjualan, detailpenjualan
- pembayaran **hanya CASH & QRIS**
- validasi stok: tidak bisa pesan melebihi stok, stok 0 jadi **HABIS** + tidak bisa dipesan
- struk menampilkan: **Total, Bayar, Kembalian, MetodePembayaran, Kasir, Pelanggan**

## Install (XAMPP)
1. Extract folder `kasanova/` ke `xampp/htdocs/kasanova/`
2. Import database: `kasanova/database/database_kasanova.sql`
3. Sesuaikan koneksi DB: `kasanova/config/db.php`
4. Akses:
   - Customer menu: `http://localhost/kasanova/`
   - Login dashboard: `http://localhost/kasanova/login.php`

## Catatan Role
- Kasir **tidak memiliki menu/tombol Produk** dan jika mencoba akses URL produk akan di-redirect ke dashboard kasir.
