<?php
require_once('../../../src/vendor/autoload.php');
session_start();

use \TCPDF as TCPDF;

// Ambil data transaksi dari session
if (isset($_SESSION['invoice'])) {
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

    // Buat PDF dengan ukuran struk (lebar kecil)
    $pdf = new TCPDF('P', 'mm', array(80, 200), true, 'UTF-8', false); 
    $pdf->SetMargins(5, 5, 5);
    $pdf->AddPage();
    $pdf->SetFont('courier', '', 10);

    // Header Toko
    $pdf->Cell(0, 5, 'Kasir 71', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Jl. Dr. KRT Radjiman Widyodiningrat', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Telp: 0882-9930-9375', 0, 1, 'C');
    $pdf->Ln(2);
    $pdf->Cell(0, 0, str_repeat('-', 32), 0, 1, 'C');
    $pdf->Ln(2);

    // Info transaksi
    $pdf->Cell(0, 5, "No: $transaksi_id", 0, 1, 'L');
    $pdf->Cell(0, 5, "Tanggal: $date", 0, 1, 'L');
    $pdf->Cell(0, 5, "Member: $member", 0, 1, 'L');
    $pdf->Ln(2);

    $pdf->Cell(0, 0, str_repeat('-', 32), 0, 1, 'C');
    $pdf->Ln(2);

    // Daftar produk
    foreach ($produk as $index => $item) {
        $nama = $item["f_nama_produk"];
        $qty = $quantity[$index];
        $harga = number_format($item["f_harga_jual"]);
        $total = number_format($item["f_harga_jual"] * $qty);

        // Baris nama produk
        $pdf->Cell(0, 5, $nama, 0, 1, 'L');
        // Baris qty x harga = total
        $pdf->Cell(0, 5, "$qty x Rp$harga  =  Rp$total", 0, 1, 'R');
    }

    $pdf->Ln(2);
    $pdf->Cell(0, 0, str_repeat('-', 32), 0, 1, 'C');
    $pdf->Ln(2);

    // Total
    $pdf->Cell(40, 5, 'Subtotal', 0, 0, 'L');
    $pdf->Cell(0, 5, "Rp" . number_format($totalHarga), 0, 1, 'R');

    $pdf->Cell(40, 5, 'Diskon', 0, 0, 'L');
    $pdf->Cell(0, 5, "Rp" . number_format($diskon), 0, 1, 'R');

    $pdf->Cell(40, 5, 'Total', 0, 0, 'L');
    $pdf->Cell(0, 5, "Rp" . number_format($totalHargaD), 0, 1, 'R');

    $pdf->Cell(40, 5, 'Bayar', 0, 0, 'L');
    $pdf->Cell(0, 5, "Rp" . number_format($totalBayar), 0, 1, 'R');

    $pdf->Cell(40, 5, 'Kembali', 0, 0, 'L');
    $pdf->Cell(0, 5, "Rp" . number_format($totalBayar - $totalHargaD), 0, 1, 'R');

    $pdf->Ln(5);

    // Footer
    $pdf->Cell(0, 5, '--- Terima Kasih ---', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Barang yang sudah dibeli', 0, 1, 'C');
    $pdf->Cell(0, 5, 'tidak dapat dikembalikan.', 0, 1, 'C');

    // Output PDF (langsung print/preview)
    $pdf->Output('struk.pdf', 'I');
}
