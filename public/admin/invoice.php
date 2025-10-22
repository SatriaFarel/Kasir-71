<?php
    include("../../src/cookie.php");

    // Periksa apakah data invoice tersedia dalam session
    if (!isset($_SESSION['invoice'])) {
        echo "Tidak ada data invoice.";
        exit;
    }


    // --- Simpan data invoice ke session agar bisa ditampilkan di invoice.php ---
    // $_SESSION['invoice'] = [
    //     'transaksi_id' => $transaksi_id,
    //     'date'         => $date,
    //     'totalHarga'   => $totalHarga,
    //     'totalProfit'  => $totalProfit,
    //     'member'       => $member,
    //     'produk'       => $Nproduk
    // ];

    // Ambil data invoice dari session dan hapus dari session jika sudah tidak diperlukan
    $invoice = $_SESSION['invoice'];
    $transaksi_id = $invoice["transaksi_id"];
    $date = $invoice["date"];
    $totalHarga = $invoice["totalHarga"];
    $totalHargaD = $invoice["totalHargaD"];
    $totalBayar = $invoice["totalBayar"];
    $idMember = $invoice["id_member"];
    $member = $invoice["member"];
    $produk = $invoice["produk"];
    $quantity = $invoice["quantity"];
    $diskon = $invoice["diskon"] ?? 0;



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Invoice</title>
  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <!-- Font Awesome untuk ikon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
        integrity="sha512-papNMtPOGJ3UmLds3s3NtE0w3bnXfEewBRRO5jF1Btk4TR5BfSM2yEEQK0+9L2DYGNLnPlJR5Y87lV+8+kox4g==" 
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-blue-500">
  <section class="flex items-center justify-center min-h-screen">
    <div class="bg-white w-full max-w-3xl p-8 rounded-lg shadow-lg">
      <!-- Header Invoice -->
      <header class="flex items-center justify-between mb-8">
        <div class="flex items-center">
          <i class="fas fa-file-invoice-dollar text-blue-500 text-4xl mr-4"></i>
          <h1 class="text-3xl font-bold text-gray-800">Invoice</h1>
        </div>
        <div class="text-right">
          <p class="text-gray-600">Invoice #<?= $transaksi_id;?></p>
          <p class="text-gray-600">Date: <?=$date?></p>
        </div>
      </header>
      <!-- Info Billing -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Bill To:</h2>
        <p class="text-gray-600"><?= $member?></p>
        <p class="text-gray-600">1234 Main Street</p>
        <p class="text-gray-600">City, State, ZIP</p>
      </div>
      <!-- Tabel Rincian Produk -->
      <div class="mb-8">
        <table class="w-full table-auto border-collapse">
          <thead>
            <tr>
              <th class="px-4 py-2 border-b text-left text-gray-700">Description</th>
              <th class="px-4 py-2 border-b text-right text-gray-700">Quantity</th>
              <th class="px-4 py-2 border-b text-right text-gray-700">Price</th>
              <th class="px-4 py-2 border-b text-right text-gray-700">Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($produk as $index => $item): ?>
                <tr>
                    <td class="px-4 py-2 border-b text-gray-600"><?= htmlspecialchars($item["f_nama_produk"]); ?></td>
                    <td class="px-4 py-2 border-b text-right text-gray-600"><?= htmlspecialchars($quantity[$index]); ?></td>
                    <td class="px-4 py-2 border-b text-right text-gray-600">Rp. <?= number_format($item["f_harga_jual"]); ?></td>
                    <td class="px-4 py-2 border-b text-right text-gray-600">
                        Rp. <?= number_format($item["f_harga_jual"] * $quantity[$index]); ?>
                    </td>
                </tr>
            <?php endforeach; ?>

          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="px-4 py-2 text-right font-semibold text-gray-800">Subtotal</td>
              <td class="px-4 py-2 text-right text-gray-800">Rp. <?= number_format($totalHarga);?></td>
            </tr>
            <tr>
              <td colspan="3" class="px-4 py-2 text-right font-semibold text-gray-800">Diskon</td>
              <td class="px-4 py-2 text-right text-gray-800">Rp. <?= number_format($diskon);?></td>
            </tr>
            <tr>
              <td colspan="3" class="px-4 py-2 text-right font-semibold text-gray-800">Total</td>
              <td class="px-4 py-2 text-right text-gray-800">Rp. <?= number_format($totalHargaD); ?></td>
            </tr>
            <tr>
              <td colspan="3" class="px-4 py-2 text-right font-semibold text-gray-800">Total Bayar</td>
              <td class="px-4 py-2 text-right text-gray-800">Rp. <?= number_format($totalBayar);?></td>
            </tr>
            <tr>
              <td colspan="3" class="px-4 py-2 text-right font-semibold text-gray-800">Kembalian</td>
              <td class="px-4 py-2 text-right text-gray-800">Rp. <?= number_format($totalBayar - $totalHargaD ); ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
      <!-- Footer -->
      <footer class="text-center text-gray-600">
        <p>Thank you for your business!</p>
        <p>If you have any questions, feel free to contact us.</p>
      </footer>
      <!-- Tombol Back dan Print -->
      <div class="flex justify-between mt-8">
        <a href="keranjang.php">
            <button class="bg-blue-500 text-white px-4 py-2 rounded flex items-center gap-2 hover:bg-blue-600">
            <i class="fas fa-arrow-left"></i> Back
            </button>
        </a>
        <a href="function/printLaporan.php">
          <button class="bg-green-500 text-white px-4 py-2 rounded flex items-center gap-2 hover:bg-green-600">
            <i class="fas fa-print"></i> Print
          </button>
        </a>
        <a href="function/wa.php?wa=1">
          <button  class="bg-green-500 text-white px-4 py-2 rounded flex items-center gap-2 hover:bg-green-600">
            <i class="fas fa-print"></i> WA
          </button>
        </a>
        
      </div>
    </div>
  </section>

</body>
</html>
