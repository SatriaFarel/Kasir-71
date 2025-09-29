<?php
include("../../../src/cookie.php");

// --- Hapus semua cookie dengan prefix "cart_" ---
foreach ($_COOKIE as $key => $value) {
    if (strpos($key, 'cart_') === 0) {
        // Hapus cookie dengan mengeset waktu kadaluarsa di masa lalu
        setcookie($key, '', time() - 3600, '/');
        unset($_COOKIE[$key]);
    }
}

// Fungsi untuk menampilkan pesan error menggunakan alert dan kembali ke halaman sebelumnya
function debugAlert($message) {
    echo "<script>alert('" . addslashes($message) . "'); document.location.href='../keranjang.php'; </script>";
    exit;
}

//window.location.href='../keranjang.php';

// --- Fungsi untuk mendapatkan ID transaksi berikutnya ---
// Cek nilai tertinggi di tabel t_transaksi, jika tidak ada maka default 1
function getNextTransactionId($conn) {
    $cekid = mysqli_query($conn, "SELECT f_id_transaksi FROM t_transaksi ORDER BY f_id_transaksi DESC LIMIT 1");
    $row = mysqli_fetch_assoc($cekid);
    return $row ? $row["f_id_transaksi"] + 1 : 1;
}

// --- Fungsi untuk memasukkan header transaksi ---
// Di sini kita menyisipkan ID transaksi secara manual sesuai perhitungan di atas
function insertTransactionHeader($conn, $transId, $date, $totalHarga, $memberId, $id) {
    $sqlHeader = "INSERT INTO t_transaksi (f_id_transaksi, f_tanggal_pembelian, f_total_harga, f_id_admin, f_id_member, f_total_keuntungan) 
                  VALUES ('$transId', '$date', '$totalHarga', $id, '$memberId', '0')";
    if (!mysqli_query($conn, $sqlHeader)) {
        debugAlert("Error inserting transaction header: " . mysqli_error($conn));
    }
    return $transId;
}

// --- Fungsi untuk mendapatkan ID detail transaksi berikutnya ---
// Cek nilai tertinggi di tabel t_detail_transaksi, default 1 jika belum ada data
function getNextDetailId($conn) {
    $cekid = mysqli_query($conn, "SELECT f_id FROM t_detail_transaksi ORDER BY f_id DESC LIMIT 1");
    $row = mysqli_fetch_assoc($cekid);
    return $row ? $row["f_id"] + 1 : 1;
}

// --- Fungsi untuk memasukkan detail transaksi ---
function insertTransactionDetail($conn, $detailId, $transaksi_id, $productId, $qty, $subtotal) {
    $sqlDetail = "INSERT INTO t_detail_transaksi (f_id, f_id_transaksi, f_id_produk, f_quantity, f_subtotal)
                  VALUES ('$detailId', '$transaksi_id', '$productId', '$qty', '$subtotal')";
    if (!mysqli_query($conn, $sqlDetail)) {
        debugAlert("Error inserting transaction detail for product $productId: " . mysqli_error($conn));
    }
    return $detailId;
}

// --- Fungsi untuk mengupdate header transaksi dengan total keuntungan dan id detail transaksi ---
function updateTransactionHeader($conn, $transaksi_id, $totalProfit, $detailId) {
    $sqlUpdate = "UPDATE t_transaksi 
                  SET f_total_keuntungan = '$totalProfit', f_id_detail = '$detailId'
                  WHERE f_id_transaksi = '$transaksi_id'";
    if (!mysqli_query($conn, $sqlUpdate)) {
        debugAlert("Error updating transaction header: " . mysqli_error($conn));
    }
}


// --- Fungsi untuk mengupdate stok produk ---
function updateProductStock($conn, $produk, $quantity) {
    foreach ($produk as $index => $productId) {
        $qty = (int) $quantity[$index];

        // Ambil stok produk berdasarkan ID
        $cek = mysqli_query($conn, "SELECT f_stok FROM t_produk WHERE f_id = '$productId'");
        if ($cek && mysqli_num_rows($cek) > 0) {
            $stok = mysqli_fetch_assoc($cek)['f_stok'];

            if ($stok >= $qty) {
                $sql = "UPDATE t_produk SET f_stok = f_stok - $qty WHERE f_id = '$productId'";
                if (!mysqli_query($conn, $sql)) {
                    debugAlert("Error updating product stock for product $productId: " . mysqli_error($conn));
                }
            } else {
                debugAlert("Error updating product stock for product $productId: stok tidak tersedia ");
            }
        } else {
            debugAlert("Produk dengan ID $productId tidak ditemukan.");
        }
    }
}


// === Proses transaksi jika request method adalah POST ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Data dari form
    $produk     = $_POST['produk'];      // Array id produk
    $quantity   = $_POST['quantity'];    // Array quantity masing-masing produk
    $totalHarga = $_POST['totalHarga'];
    $totalBayar = $_POST['bayar'];

    $redeemPoint = (int) ($_POST["redeemPoints"] ?? 0);
    $memberId    = mysqli_real_escape_string($conn, $_POST['member'] ?? "");
    $date        = date("Y-m-d");

    if ($redeemPoint > 0 && !empty($memberId)) {
        // Cek apakah member ada dan punya cukup poin
        $cekPointQ = mysqli_query($conn, "SELECT f_point FROM t_member WHERE f_id = '$memberId'");
        $cekPoint = mysqli_fetch_assoc($cekPointQ);

        if ($cekPoint) {
            $cekPoint = (int) $cekPoint["f_point"];

            if ($cekPoint >= $redeemPoint) {
                // Proses pengurangan poin
                $pointQ = mysqli_query($conn, "UPDATE t_member SET f_point = f_point - $redeemPoint WHERE f_id = '$memberId'");

                if ($pointQ) {
                    $affectedRows = mysqli_affected_rows($conn);
                    $diskon = $redeemPoint * 100 ;
                    $totalHargaD = $totalHarga - $diskon;
                } else {
                    debugAlert("Query gagal: " . mysqli_error($conn));
                }
            } else {
                debugAlert("Member tidak memiliki cukup poin.");
            }
        } else {
            debugAlert("Member tidak ditemukan.");
        }
    }else{
        $totalHargaD = $totalHarga;
    }

    // --- Update stok produk ---
    updateProductStock($conn, $produk, $quantity);

    // --- Dapatkan ID transaksi baru ---
    $transId = getNextTransactionId($conn);
    
    // --- Insert header transaksi dengan ID yang sudah ditentukan ---
    $transaksi_id = insertTransactionHeader($conn, $transId, $date, $totalHarga, $memberId, $id);
    
    $totalProfit  = 0; // Total keuntungan dari transaksi
    $detailId     = getNextDetailId($conn);

    // --- Loop untuk setiap produk yang dibeli ---
    foreach ($produk as $index => $productId) {
        $qty = (int)$quantity[$index];

        // Ambil data produk (misalnya: harga jual dan modal)
        $sqlProduk   = "SELECT f_harga_jual, f_modal FROM t_produk WHERE f_id = '$productId'";
        $resultProduk = mysqli_query($conn, $sqlProduk);
        if ($resultProduk && mysqli_num_rows($resultProduk) > 0) {
            $data       = mysqli_fetch_assoc($resultProduk);
            $hargaJual  = $data['f_harga_jual'];
            $hargaBeli  = $data['f_modal'];

            // Hitung subtotal dan keuntungan untuk produk ini
            $subtotal   = $hargaJual * $qty;
            $profit     = ($hargaJual - $hargaBeli) * $qty;
            $totalProfit += $profit;

            // Insert detail transaksi
            insertTransactionDetail($conn, $detailId, $transaksi_id, $productId, $qty, $subtotal);
        } else {
            debugAlert("Produk dengan ID $productId tidak ditemukan.");
        }
    }

    // --- Update header transaksi ---
    updateTransactionHeader($conn, $transaksi_id, $totalProfit, $detailId);

    // --- Ambil data member untuk invoice ---
    $memberQuery = mysqli_query($conn, "SELECT * FROM t_member WHERE f_id = '$memberId'");
    $memberData  = mysqli_fetch_assoc($memberQuery);
    $member      = $memberData["f_nama_member"];

    // --- Tambah point member (misal: 1 poin untuk setiap Rp1.000 yang dibelanjakan) ---
    $point = $totalHarga / 1000;
    $sqlPoint = "UPDATE t_member SET f_point = f_point + $point WHERE f_nama_member = '$member'";
    if (!mysqli_query($conn, $sqlPoint)) {
        debugAlert("Error updating member points: " . mysqli_error($conn));
    }

    // --- Ambil data produk untuk invoice ---
    $Nproduk = [];
    foreach ($produk as $id) {
        $query = mysqli_query($conn, "SELECT * FROM t_produk WHERE f_id = '$id'");
        if ($query && mysqli_num_rows($query) > 0) {
            $data = mysqli_fetch_assoc($query);
            $Nproduk[] = $data;
        }
    }


    // --- Update last activity ---
    $now = date("Y-m-d");
    $sqlActivity = "UPDATE t_member SET f_last_activity = '$now' WHERE f_nama_member = '$member'";
    if (!mysqli_query($conn, $sqlActivity)) {
        debugAlert("Error updating member points: " . mysqli_error($conn));
    }

    // --- Simpan data invoice ke session agar bisa ditampilkan di invoice.php ---
    $_SESSION['invoice'] = [
        'transaksi_id' => $transaksi_id,
        'date'         => $date,
        'totalHarga'   => $totalHarga,      //total harga awal
        'totalHargaD'  => $totalHargaD,    //total harga setelah diskon
        'totalBayar'   => $totalBayar,
        'totalProfit'  => $totalProfit,
        'id_member'    => $id,
        'member'       => $member,
        'produk'       => $Nproduk,
        'quantity'     => $quantity,
        'diskon'       => $diskon ?? 0
    ];

    // Redirect ke invoice.php untuk menampilkan invoice
    header("Location: ../invoice.php");
    exit;
}
?>