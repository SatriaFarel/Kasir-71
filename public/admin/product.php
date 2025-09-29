<?php

include("../../src/cookie.php");

// Pagination setup
$limit  = 4; // Jumlah data per halaman
$page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if (isset($_GET["ket"])) {
  $idK = $_GET["ket"];

  // Hitung total data produk berdasarkan kategori
  $countQuery  = "SELECT COUNT(*) AS total FROM t_produk WHERE f_id_kategori = '$idK'";
  $countResult = mysqli_query($conn, $countQuery);
  if ($countResult) {
    $totalData = mysqli_fetch_assoc($countResult)['total'];
  } else {
    die("Error counting data: " . mysqli_error($conn));
  }

  // Ambil data produk berdasarkan kategori dengan limit dan offset
  $query = "SELECT 
                p.f_id, 
                p.f_harga_jual, 
                k.f_kategori, 
                p.f_nama_produk, 
                p.f_stok, 
                p.f_gambar,
                p.f_qr, 
                p.f_modal, 
                p.f_keuntungan, 
                p.f_tanggal_expired, 
                p.f_deskripsi,
                p.f_kodep
              FROM t_produk p 
              INNER JOIN t_kategori k ON p.f_id_kategori = k.f_id 
              WHERE p.f_id_kategori = '$idK'
              LIMIT $limit OFFSET $offset";
  $data = mysqli_query($conn, $query);
  if (!$data) {
    die("Error fetching data: " . mysqli_error($conn));
  }
} else {
  // Hitung total data kategori
  $countQuery  = "SELECT COUNT(*) AS total FROM t_kategori";
  $countResult = mysqli_query($conn, $countQuery);
  if ($countResult) {
    $totalData = mysqli_fetch_assoc($countResult)['total'];
  } else {
    die("Error counting data: " . mysqli_error($conn));
  }

  // Ambil data kategori dengan limit dan offset
  $query = "SELECT * FROM t_kategori LIMIT $limit OFFSET $offset";
  $data = mysqli_query($conn, $query);
  if (!$data) {
    die("Error fetching data: " . mysqli_error($conn));
  }
}

if (isset($_GET["kodep"]) && isset($_GET["btn_ker"])) {
  $kodep = $_GET["kodep"];
  echo "<script>
        let qty = prompt('Masukkan jumlah produk untuk barcode: $kodep');
        if (qty !== null) {
            // redirect ke keranjang.php dengan 2 parameter (kodep & quantity)
            window.location.href = 'keranjang.php?kodep=" . $kodep . "&quantity=' + encodeURIComponent(qty);
        } else {
            // kalau dibatalin, balik ke index
            window.location.href = 'index.php';
        }
    </script>";

  // Redirect ke keranjang.php dengan parameter kodep dan quantity
  header("Location: keranjang.php?kodep=$kodep&quantity=$quantity");
  exit();
}

// Hitung total halaman
$totalPages = ceil($totalData / $limit);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document</title>
  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body class="h-screen bg-blue-400">
  <section class="container flex h-screen">
    <?php include("layout/sidebar.php"); ?>

    <?php if (!isset($_GET["ket"])): ?>
      <div class="w-3/4 mt-4 flex flex-col">
        <div class="flex justify-between items-center bg-slate-300 border p-2">
          <h1 class="text-xl">Keranjang</h1>
          <div class="flex items-center gap-3">
            <!-- Tombol Tambah Data -->
            <a href="function/create.php?kategori=1" class="flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
              </svg>
              Tambah Data
            </a>
            <img src="../../asset/img/profil.png" alt="Profile" class="w-11" />
          </div>
        </div>

        <div class="bg-slate-300 border p-3 my-4 h-screen gap-3 flex flex-wrap justify-around overflow-scroll overscroll-x-none">
          <?php foreach ($data as $kategori): ?>
            <div class="bg-white shadow-xl rounded-lg p-6 max-w-sm h-40 flex flex-col justify-between">
              <h2 class="text-2xl font-bold text-center text-gray-800">
                <?= htmlspecialchars($kategori["f_kategori"]); ?>
              </h2>
              <div class="flex justify-around mt-4 gap-1 flex-wrap">
                <a href="function/update.php?IDK=<?= $kategori['f_id']; ?>">
                  <button class="flex items-center gap-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-lg shadow transform transition duration-300 hover:scale-105 hover:shadow-lg">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Edit</span>
                  </button>
                </a>
                <a href="function/delete.php?IDK=<?= $kategori['f_id']; ?>">
                  <button class="flex items-center gap-2 bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-2 rounded-lg shadow transform transition duration-300 hover:scale-105 hover:shadow-lg">
                    <i class="fa-solid fa-trash"></i>
                    <span>Hapus</span>
                  </button>
                </a>
                <a href="product.php?ket=<?= $kategori['f_id']; ?>" class="flex items-center gap-2">
                  <button class="flex items-center gap-2 bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 rounded-lg shadow transform transition duration-300 hover:scale-105 hover:shadow-lg">
                    <i class="fa-solid fa-eye"></i>
                    <span>Lihat Produk</span>
                  </button>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <!-- Include file pagination -->
        <?php include("layout/paggination.php"); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET["ket"])): ?>
      <div class="w-3/4 mt-4 flex flex-col">
        <div class="flex justify-between items-center bg-slate-300 border p-2">
          <h1 class="text-xl">Product</h1>
          <div class="flex items-center gap-3">
            <!-- Tombol Tambah Data -->
            <a href="function/create.php?product=1" class="flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
              </svg>
              Tambah Data
            </a>
            <img src="../../asset/img/profil.png" alt="Profile" class="w-11" />
          </div>
        </div>

        <div class="bg-slate-300 border p-3 my-4 h-screen gap-3 flex flex-wrap justify-around overflow-scroll overscroll-x-none">
          <?php foreach ($data as $row): ?>
            <div class="relative max-w-xs h-96 p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
              <!-- Tombol Update (pojok kiri atas) -->
              <a href="function/update.php?IDP=<?= $row['f_id']; ?>" title="Update" class="absolute top-2 left-2 flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full hover:bg-blue-800 focus:ring-2 focus:ring-blue-300">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L7 21H3v-4L16.232 3.232z" />
                </svg>
              </a>

              <!-- Tombol Delete (pojok kanan atas) -->
              <a href="function/delete.php?IDP=<?= $row['f_id']; ?>" title="Delete" class="absolute top-2 right-2 flex items-center justify-center w-8 h-8 bg-red-600 text-white rounded-full hover:bg-red-800 focus:ring-2 focus:ring-red-300">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0a2 2 0 012 2v0a2 2 0 01-2 2H7a2 2 0 01-2-2v0a2 2 0 012-2h10z" />
                </svg>
              </a>

              <!-- Konten Produk -->
              <div class="flex flex-col items-center">
                <!-- Gambar Produk -->
                <img class="w-24 h-24 mb-3 rounded-full shadow-lg" src="<?= $row['f_gambar']; ?>" alt="Product Image" />
                <!-- Nama Produk -->
                <h5 class="mb-1 text-xl font-medium text-gray-900"><?= htmlspecialchars($row["f_nama_produk"]); ?></h5>
                <!-- Informasi Produk -->
                <div class="text-sm text-gray-500 text-center">
                  <p>Kategori: <?= htmlspecialchars($row["f_kategori"]); ?></p>
                  <p>Stock: <?= htmlspecialchars($row["f_stok"]); ?></p>
                  <p>Price: Rp. <?= number_format($row["f_harga_jual"]); ?></p>
                </div>
                <!-- Gambar Barcode -->
                <img class="w-24 h-10 my-3 shadow-lg" src="../../asset/barcodes/<?= $row['f_qr']; ?>" alt="Product Image" />
              </div>

              <!-- Tombol Aksi di Baris Bawah -->
              <div class="mt-4 flex justify-around gap-2 flex-wrap">
                <!-- Tombol Detail -->
                <a href="javascript:void(0)" onclick="document.getElementById('modal-<?= $row['f_id']; ?>').classList.remove('hidden')" title="Detail" class="flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-green-600 rounded hover:bg-green-800 focus:ring-2 focus:ring-green-300">
                  <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <span class="hidden sm:inline ml-1">Detail</span>
                </a>

                <!-- Tombol Print -->
                <a title="Print" class="scanButton flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-blue-500 rounded hover:bg-blue-600 focus:ring-2 focus:ring-blue-300" href="function/PrintBarcode.php?id=<?= $row['f_id']; ?>">
                  <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                  </svg>
                  <span class="hidden sm:inline ml-1">Print</span>
                </a>

                <?php
                $expired = strtotime($row['f_tanggal_expired']);
                $now = time();
                $isExpired = $now > $expired;

                ?>

                <?php if ($row['f_stok'] > 0 && !$isExpired): ?>
                  <button onclick="tambahKeranjang('<?= $row['f_kodep']; ?>')"
                    title="Tambah ke Keranjang"
                    class="flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-yellow-500 rounded hover:bg-yellow-600 focus:ring-2 focus:ring-yellow-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l1.5 7h7l1.5-7M10 21a1 1 0 11-2 0 1 1 0 012 0zm10 0a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                    <span class="hidden sm:inline ml-1">Keranjang</span>
                  </button>

                <?php else: ?>
                  <button disabled
                    title="<?= $row['f_stok'] <= 0 ? 'Stok Habis' : 'Produk Expired'; ?>"
                    class="flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-gray-400 rounded cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l1.5 7h7l1.5-7M10 21a1 1 0 11-2 0 1 1 0 012 0zm10 0a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                    <span class="hidden sm:inline ml-1">
                      <?= $row['f_stok'] <= 0 ? 'Stok Habis' : 'Expired'; ?>
                    </span>
                  </button>
                <?php endif; ?>

              </div>
            </div>

            <!-- Modal Detail Produk -->
            <div id="modal-<?= $row['f_id']; ?>" class="fixed inset-0 flex items-center justify-center z-50 hidden">
              <!-- Background overlay -->
              <div class="absolute inset-0 bg-black opacity-50"></div>
              <!-- Modal content -->
              <div class="bg-white rounded-lg p-6 relative z-10 max-w-md w-full mx-4">
                <h2 class="text-xl font-bold mb-4">Detail Produk</h2>
                <p><strong>Nama:</strong> <?= htmlspecialchars($row['f_nama_produk']); ?></p>
                <p><strong>Kategori:</strong> <?= htmlspecialchars($row['f_kategori']); ?></p>
                <p><strong>Stock:</strong> <?= htmlspecialchars($row['f_stok']); ?></p>
                <p><strong>Modal:</strong> Rp. <?= number_format($row['f_modal']); ?></p>
                <p><strong>Price:</strong> Rp. <?= number_format($row['f_harga_jual']); ?></p>
                <p><strong>Keuntungan:</strong> Rp. <?= number_format($row['f_keuntungan']); ?></p>
                <p class="mt-2"><strong>Deskripsi:</strong> <?= htmlspecialchars($row['f_deskripsi']); ?></p>
                <button onclick="document.getElementById('modal-<?= $row['f_id']; ?>').classList.add('hidden')" class="mt-4 px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 focus:ring-2 focus:ring-gray-300">
                  Close
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <!-- Pagination -->
        <nav class="my-5">
          <ul class="flex items-center justify-center space-x-1 text-sm">
            <!-- Tombol Previous -->
            <?php if ($page > 1): ?>
              <li>
                <a href="?page=<?= $page - 1 ?>&ket=<?= $idK ?>" class="flex items-center justify-center px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-blue-300 hover:text-gray-700">
                  <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4" />
                  </svg>
                </a>
              </li>
            <?php endif; ?>

            <!-- Nomor Halaman -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li>
                <a href="?page=<?= $i ?>&ket=<?= $idK ?>" class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg
                            <?= $i === $page ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-blue-300 hover:text-gray-700' ?>">
                  <?= $i ?>
                </a>
              </li>
            <?php endfor; ?>

            <!-- Tombol Next -->
            <?php if ($page < $totalPages): ?>
              <li>
                <a href="?page=<?= $page + 1 ?>&ket=<?= $idK ?>" class="flex items-center justify-center px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-blue-300 hover:text-gray-700">
                  <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4" />
                  </svg>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>

      </div>
    <?php endif; ?>
  </section>
  <?php include("layout/footer.php"); ?>
  <script>
    function tambahKeranjang(kodep){
      let tambahan = prompt('Masukkan jumlah produk untuk barcode: '+ kodep );
        if (tambahan !== null) {
            // redirect ke keranjang.php dengan 2 parameter (kodep & quantity)
            window.location.href = 'keranjang.php?kodep=' + kodep + '&quantity=' + encodeURIComponent(tambahan);
        } else {
            // kalau dibatalin, balik ke index
            window.location.href = 'index.php';
        }
     }
  </script>
</body>

</html>