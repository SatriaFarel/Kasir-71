<?php
$barcode = isset($_GET['barcode']) ? trim($_GET['barcode']) : null;

if ($barcode) {
    // koneksi ke database
    include '../../src/config.php'; // pastikan $conn sudah tersedia

    // ambil data produk berdasarkan barcode
    $query = mysqli_query($conn, "SELECT * FROM t_produk WHERE f_id = '$barcode'");
    $produk = mysqli_fetch_assoc($query);

    if (!$produk) {
        echo "<script>alert('Produk tidak ditemukan!');window.location.href='keranjang.php';</script>";
        exit();
    }

    // cek stok
    if ($produk['f_stok'] <= 0) {
        echo "<script>alert('Stok produk habis!');window.location.href='keranjang.php';</script>";
        exit();
    }

    // cek kadaluarsa
    $today = date('Y-m-d');
    if (!empty($produk['f_kadaluarsa']) && $produk['f_kadaluarsa'] < $today) {
        echo "<script>alert('Produk sudah kadaluarsa!');window.location.href='keranjang.php';</script>";
        exit();
    }

    // minta jumlah produk dan cek apakah melebihi stok
    echo "<script>
        let stok = {$produk['f_stok']};
        let namaProduk = '" . addslashes($produk['f_nama_produk']) . "';
        let jumlah = prompt('Masukkan jumlah produk untuk: ' + namaProduk + ' (stok tersedia: ' + stok + ')');

        if (jumlah !== null) {
            jumlah = parseInt(jumlah);
            if (isNaN(jumlah) || jumlah <= 0) {
                alert('Jumlah tidak valid!');
                window.location.href = 'keranjang.php';
            } else if (jumlah > stok) {
                alert('Jumlah melebihi stok!');
                window.location.href = 'keranjang.php';
            } else {
                window.location.href = 'keranjang.php?id={$barcode}&quantity=' + jumlah;
            }
        } else {
            window.location.href = 'keranjang.php';
        }
    </script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Scan Barcode</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f8ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            text-align: center;
            width: 420px;
        }
        h2 {
            color: #1a73e8;
            margin-bottom: 20px;
        }
        .scan-box {
            border: 2px dashed #1a73e8;
            border-radius: 10px;
            padding: 25px;
            color: #555;
            font-size: 18px;
            background: #f9fcff;
            margin-bottom: 20px;
        }
        .result {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            background: #f0f6ff;
            border: 2px solid #1a73e8;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #888;
        }
    </style>
    <script>
        let buffer = "";
        let timeout = null;

        document.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                return;
            }

            buffer += e.key;
            if (timeout) clearTimeout(timeout);

            timeout = setTimeout(() => {
                if (buffer.length > 0) {
                    window.location.href = "?barcode=" + buffer.trim();
                    buffer = "";
                }
            }, 300);
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>📦 Sistem Scan Barcode</h2>
        <div class="scan-box">
            Arahkan scanner ke barcode<br>
            Hasil scan otomatis terbaca di sini.
        </div>

        <?php if ($barcode): ?>
            <div class="result">
                Barcode terbaca: <?= htmlspecialchars($barcode) ?>
            </div>
        <?php endif; ?>

        <div class="footer">
            Dibuat dengan 💙 warna biru
        </div>
    </div>
</body>
</html>
