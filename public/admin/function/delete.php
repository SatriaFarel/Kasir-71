<?php
include("../../../src/config.php");

if (isset($_GET["IDA"])) {
    $id = $_GET["IDA"];

    // Ambil nama file gambar dari database
    $query_get = "SELECT f_gambar FROM t_admin WHERE f_id = '$id'";
    $result = mysqli_query($conn, $query_get);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $gambar = $row["f_gambar"];

        // Hapus file gambar jika ada
        if (!empty($gambar) && file_exists("../../asset/pfp/" . $gambar)) {
            unlink("../../asset/pfp/" . $gambar);
        }
    }

    // Hapus data admin dari database
    $query_delete = "DELETE FROM t_admin WHERE f_id = '$id'";
    if (mysqli_query($conn, $query_delete)) {
        echo "<script>alert('Data Berhasil Dihapus'); document.location.href='../admin.php';</script>";
    } else {
        echo "<script>alert('Data Gagal Dihapus'); document.location.href='../admin.php';</script>";
    }
}

if (isset($_GET["IDM"])) {
    $id = $_GET["IDM"];

    // Cek aktivitas terakhir
    $cekStatus = mysqli_query($conn, "SELECT * FROM t_member WHERE f_id = '$id'");
    $cekStatus2 = mysqli_fetch_assoc($cekStatus)["f_status"];
    if($cekStatus2 === "Aktif"){
        echo "<script>alert('Member masih aktif!!'); document.location.href='../member.php';</script>";
        exit;
    }

    // Hapus data admin dari database
    $query_delete = "DELETE FROM t_member WHERE f_id = '$id'";
    if (mysqli_query($conn, $query_delete)) {
        echo "<script>alert('Data Berhasil Dihapus'); document.location.href='../member.php';</script>";
    } else {
        echo "<script>alert('Data Gagal Dihapus'); document.location.href='../member.php';</script>";
    }
}

if (isset($_GET["IDP"])) {
    $id = $_GET["IDP"];

    // Cek stok
    $cekStok = mysqli_query($conn, "SELECT * FROM t_produk WHERE f_id = '$id'");
    $data = mysqli_fetch_assoc($cekStok);
    $stok = $data["f_stok"];

    // Kalau stok masih ada, batalkan penghapusan
    if ($stok > 0) {
        echo "<script>alert('Stok masih ada!!'); document.location.href='../product.php';</script>";
        exit;
    }

    // Ambil nama file gambar & barcode dari database
    $gambar = $data["f_gambar"]; // pastikan nama kolom gambar benar
    $barcode = $data["f_barcode"]; // pastikan nama kolom barcode benar

    // Hapus file gambar jika ada
    if (!empty($gambar)) {
        $pathGambar = $gambar; // sesuaikan folder upload gambar
        if (file_exists($pathGambar)) {
            unlink($pathGambar);

        }
    }

    // Hapus file barcode jika ada
    if (!empty($barcode)) {
        $pathBarcode = "../../../src/barcodes/". $barcode; // sesuaikan folder barcode
        if (file_exists($pathBarcode)) {
            unlink($pathBarcode);
        }
    }

    // Hapus data produk
    $query_delete = "DELETE FROM t_produk WHERE f_id = '$id'";
    if (mysqli_query($conn, $query_delete)) {
        echo "<script>alert('Data Berhasil Dihapus'); document.location.href='../product.php';</script>";
    } else {
        echo "<script>alert('Data Gagal Dihapus'); document.location.href='../product.php';</script>";
    }
}


if (isset($_GET["IDK"])) {
    $id = $_GET["IDK"];

    $cekProduk = mysqli_query($conn, "SELECT * FROM t_produk WHERE f_id_kategori = '$id'");
    if($cekProduk->num_rows > 0){
        echo "<script>alert('Produk masih ada!!'); document.location.href='../product.php';</script>";
        exit;
    }

    // Hapus data admin dari database
    $query_delete = "DELETE FROM t_kategori WHERE f_id = '$id'";
    if (mysqli_query($conn, $query_delete)) {
        echo "<script>alert('Data Berhasil Dihapus'); document.location.href='../product.php?ket=$id';</script>";
    } else {
        echo "<script>alert('Data Gagal Dihapus'); document.location.href='../product.php?ket=$id';</script>";
    }
}

// Cek apakah parameter 'key' ada
if (isset($_GET['key'])) {
    // Ambil nilai key dari URL
    $cookieKey = urldecode($_GET['key']);

    // Menghapus cookie dengan cara mengatur waktu kadaluarsa ke masa lalu
    setcookie($cookieKey, "", time() - 3600, "/"); // Hapus cookie

    // Redirect kembali ke halaman sebelumnya setelah penghapusan
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    echo "<script>alert('Produk tidak ditemukan!');</script>";
}

?>