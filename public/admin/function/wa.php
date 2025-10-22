<?php
include("../../../src/config.php");
session_start();

if(isset($_GET["otp"])) {
    $no_telp = $_GET["no_telp"];
    $rows = mysqli_query($conn, "SELECT * FROM t_admin WHERE f_phone = '$no_telp'");
    $otp = $_GET["otp"];
    $row = mysqli_fetch_assoc($rows);

    $instance_id = "instance106703"; // ID dari Ultramsg
    $token = "8jk3g6fpft3lto66"; // Token dari Ultramsg
    $phone = $row["f_phone"]; // Nomor tujuan (format internasional tanpa +)
    $id = $row["f_id"];

    // Pesan teks OTP
    $message = "🔐 *Kode OTP Kunjungan SMKN 71 Jakarta*

    Halo, *" . $row["f_username"] . "* 👋🏻

    Kode OTP Anda adalah: *$otp*

    Silakan tunjukkan kode ini kepada petugas untuk verifikasi kunjungan Anda.

    Terima kasih 🙏";

    // Endpoint API untuk kirim teks
    $url_message = "https://api.ultramsg.com/$instance_id/messages/chat";

    $data_message = [
        "token" => $token,
        "to" => $phone,
        "body" => $message
    ];

    $options = [
        "http" => [
            "header"  => "Content-Type: application/json",
            "method"  => "POST",
            "content" => json_encode($data_message),
        ],
    ];

    $response = file_get_contents($url_message, false, stream_context_create($options));

    if($response == true){
         // Simpan OTP ke database kalau perlu (contoh aja)
        mysqli_query($conn, "UPDATE t_admin SET f_otp = null WHERE f_id = '$id'");

        echo "<script>alert('OTP berhasil dikirim!');document.location.href='../../../forgotpw.php?verify=1';</script>";
    }

    // Debug respon API (bisa dihapus kalau udah beres)
    echo $response;
}elseif (isset($_GET["wa"])) {
    // Data Fonnte
    $fonnteToken = "ybYhMzBRRJpyRZvgjR5w"; 
    $url = "https://api.fonnte.com/send";

    $invoice = $_SESSION['invoice'];
    $transaksi_id = $invoice["transaksi_id"];
    $date = $invoice["date"];
    $totalHarga = $invoice["totalHarga"];
    $totalHargaD = $invoice["totalHargaD"];
    $totalBayar = $invoice["totalBayar"];
    $member = $invoice["member"];
    $produk = $invoice["produk"];
    $quantity = $invoice["quantity"];
    $diskon = $invoice["diskon"] ?? 0;

    $notelp = mysqli_query($conn, "SELECT * FROM t_member WHERE f_nama_member = '$member'");
    $notelp = $notelp->fetch_assoc()["f_no_telp"];

    // Pastikan nomor format 628xxx
    $tujuan = preg_replace('/^0/', '62', $notelp);

   // Buat isi pesan
    $pesan = "🧾 *INVOICE PEMBAYARAN*\n\n";
    $pesan .= "📅 Tanggal        : *$date*\n";
    $pesan .= "👤 Nama Pelanggan : *$member*\n";
    $pesan .= "🆔 ID Transaksi   : *$transaksi_id*\n\n";
    $pesan .= "📦 *Detail Pesanan:*\n";

    foreach ($produk as $index => $item) {
        $nama = $item["f_nama_produk"];
        $qty = $quantity[$index];
        $harga = number_format($item["f_harga_jual"]);
        $total = number_format($item["f_harga_jual"] * $qty);
        $pesan .= "- $nama\n  Jumlah : $qty × Rp$harga\n  Total  : Rp$total\n";
    }

    $pesan .= "\n━━━━━━━━━━━━━━━━━━━━━━━\n";
    $pesan .= "💳 *Subtotal*  : Rp" . number_format($totalHarga) . "\n";
    $pesan .= "🏷️ *Diskon*    : Rp" . number_format($diskon) . "\n";
    $pesan .= "🧾 *Total Bayar*: Rp" . number_format($totalHargaD) . "\n";
    $pesan .= "💵 *Dibayarkan*: Rp" . number_format($totalBayar) . "\n";
    $pesan .= "🎁 *Kembalian* : Rp" . number_format($totalBayar - $totalHargaD) . "\n";
    $pesan .= "━━━━━━━━━━━━━━━━━━━━━━━\n";
    $pesan .= "🙏 Terima kasih telah melakukan transaksi di toko kami.\n";
    $pesan .= "📞 Jika ada pertanyaan atau kendala, silakan hubungi layanan pelanggan.";
    $pesan .= "\n\n*Kasir 71*";
    
    // Kirim via Fonnte
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'target' => $tujuan,
            'message' => $pesan
        ],
        CURLOPT_HTTPHEADER => [
            "Authorization: $fonnteToken"
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $responseData = json_decode($response, true);

    // Debug dulu kalau gagal
    if (isset($responseData["status"]) && $responseData["status"] == true) {
        echo "<script>
            alert('Pesan berhasil dikirim ke WhatsApp $member!');
            window.location.href = '../invoice.php';
        </script>";
    } else {
        echo "<pre>";
        print_r($responseData); // biar tau error detail
        echo "</pre>";
        echo "<script>alert('Gagal mengirim pesan. Lihat error di layar.');</script>";
    }
}




?>
