<?php
include("../../src/cookie.php"); // Autentikasi dan koneksi ke DB

// Ambil ID transaksi
$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id) {
    header("Location: laporan.php");
    exit();
}

// Query detail transaksi + member
$query = "SELECT 
            t.f_id_transaksi, 
            t.f_tanggal_pembelian, 
            t.f_total_harga, 
            t.f_total_keuntungan, 
            m.f_nama_member, 
            m.f_no_telp, 
            GROUP_CONCAT(p.f_nama_produk ORDER BY d.f_id SEPARATOR ', ') AS produk_nama, 
            GROUP_CONCAT(d.f_quantity ORDER BY d.f_id SEPARATOR ', ') AS jumlah
          FROM t_transaksi t 
          INNER JOIN t_detail_transaksi d ON t.f_id_transaksi = d.f_id_transaksi 
          INNER JOIN t_produk p ON d.f_id_produk = p.f_id
          INNER JOIN t_member m ON t.f_id_member = m.f_id
          WHERE t.f_id_transaksi = '$id' 
          GROUP BY t.f_id_transaksi";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

// Format tanggal
$tanggal_pembelian = date("d F Y", strtotime($row['f_tanggal_pembelian']));

// Format pesan
$produk_list = "";
$products = explode(', ', $row['produk_nama']);
$quantities = explode(', ', $row['jumlah']);
foreach ($products as $idx => $name) {
    $produk_list .= "- $name (x" . $quantities[$idx] . ")\n";
}

$pesan = "🧾 *INVOICE PEMBAYARAN*\n\n";
$pesan .= "📅 Tanggal        : *$tanggal_pembelian*\n";
$pesan .= "👤 Nama Pelanggan : *{$row['f_nama_member']}*\n";
$pesan .= "🆔 ID Transaksi   : *{$row['f_id_transaksi']}*\n\n";
$pesan .= "📦 *Detail Pesanan:*\n$produk_list\n";
$pesan .= "━━━━━━━━━━━━━━━━━━━━━━━\n";
$pesan .= "💳 *Total Harga*     : Rp" . number_format($row['f_total_harga'], 0, ',', '.') . "\n";
$pesan .= "💰 *Total Keuntungan*: Rp" . number_format($row['f_total_keuntungan'], 0, ',', '.') . "\n";
$pesan .= "━━━━━━━━━━━━━━━━━━━━━━━\n";
$pesan .= "🙏 Terima kasih telah berbelanja di toko kami.\n📞 Jika ada pertanyaan, silakan hubungi layanan pelanggan.";

// Kirim WA via Fonnte jika tombol ditekan
if (isset($_POST['kirim_wa'])) {
    $token = "ybYhMzBRRJpyRZvgjR5w"; // ganti dengan token device Fonnte

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => [
            "target" => $row['f_no_telp'], 
            "message" => $pesan,
        ],
        CURLOPT_HTTPHEADER => ["Authorization: $token"],
    ]);
    $response = curl_exec($curl);
    curl_close($curl);

    // Bikin alert sesuai status
    if (isset($result_fonnte['status']) && $result_fonnte['status'] == true) {
        echo "<script>alert('Pesan berhasil dikirim ke WhatsApp {$row['f_nama_member']}!');</script>";
    } else {
        $reason = isset($result_fonnte['reason']) ? $result_fonnte['reason'] : 'Gagal tidak diketahui';
        echo "<script>alert('Gagal mengirim pesan: $reason');</script>";
    }

}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Transaksi #<?= $row['f_id_transaksi']; ?></title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen flex items-center justify-center p-6">
  <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-3xl">
      <h1 class="text-3xl font-bold mb-6 text-center text-indigo-600">📑 Invoice Transaksi</h1>

      <!-- Info transaksi + member sama kayak tadi -->
      <div class="grid gap-4">
          <p><b>Nomor Transaksi:</b> <?= $row['f_id_transaksi']; ?></p>
          <p><b>Tanggal:</b> <?= $tanggal_pembelian; ?></p>
          <p><b>Total Harga:</b> Rp <?= number_format($row['f_total_harga'], 0, ',', '.'); ?></p>
          <p><b>Keuntungan:</b> Rp <?= number_format($row['f_total_keuntungan'], 0, ',', '.'); ?></p>
          <p><b>Nama Member:</b> <?= $row['f_nama_member']; ?></p>
          <p><b>No. HP:</b> <?= $row['f_no_telp']; ?></p>
      </div>

      <!-- Daftar Produk -->
      <h2 class="text-xl font-semibold mt-6">Daftar Produk</h2>
      <ul class="list-disc list-inside text-gray-700 mt-2">
          <?php foreach ($products as $idx => $name): ?>
          <li><?= $name; ?> (x<?= $quantities[$idx]; ?>)</li>
          <?php endforeach; ?>
      </ul>

      <!-- Tombol -->
      <div class="mt-6 flex justify-between">
          <a href="laporan.php" class="px-4 py-2 bg-gray-500 text-white rounded-lg shadow hover:bg-gray-600 transition">&larr; Kembali</a>

          <form method="post">
              <button type="submit" name="kirim_wa" class="px-4 py-2 bg-green-500 text-white rounded-lg shadow hover:bg-green-600 transition">📲 Kirim via WhatsApp</button>
          </form>
      </div>

      <?php if (!empty($result_fonnte)): ?>
      <div class="mt-4 p-3 rounded bg-gray-100 text-sm text-gray-700">
          <b>Respon Fonnte:</b> <?= htmlspecialchars(json_encode($result_fonnte)); ?>
      </div>
      <?php endif; ?>
  </div>
</body>
</html>
