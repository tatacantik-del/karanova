# Prompt untuk AI (ChatGPT) — Proyek Resto Kasir

Kamu adalah asisten developer. Bantu saya mengembangkan aplikasi POS restoran berbasis PHP + MySQL (mysqli) + Bootstrap.
Struktur tabel dan relasi mengikuti file DATABASE_kasirbaru.sql (dump dari proyek saya).

## Konteks aplikasi
- Role user: Admin, Kasir, Owner.
- Customer bisa order tanpa login (cart pakai session).
- Admin/Kasir/Owner login ke dashboard.

## Aturan bisnis wajib
1) Stok:
- Tidak boleh checkout jika qty melebihi stok.
- Setelah checkout, stok berkurang.
- Jika stok = 0, produk harus tampil "HABIS/SOLD OUT", gambar grayscale, tombol pesan disabled.
2) Pembayaran:
- Di semua alur transaksi (customer checkout + kasir buat transaksi), metode pembayaran HANYA: CASH dan QRIS.
- Jika QRIS dipilih: bayar otomatis = total, kembalian = 0.
- Jika CASH: bayar boleh lebih, tampilkan keterangan kembalian di UI dan simpan ke DB.
3) Struk:
- Struk menampilkan: Total, MetodePembayaran, Bayar, Kembalian, Pelanggan, Kasir (atau 'Customer' jika tanpa login).
4) Laporan/Transaksi:
- Default urutan data terbaru di atas (ORDER BY PenjualanID DESC atau Tanggal DESC, PenjualanID DESC).

## Output yang saya mau dari kamu
- Jelaskan perubahan singkat.
- Lalu tuliskan FULL CODE untuk file yang kamu ubah (bukan potongan).
