<?php
require_once('../../../src/vendor/autoload.php');
session_start();
use \TCPDF as TCPDF;

// ======================================================
// 1️⃣ STRUK TRANSAKSI (SESSION)
// ======================================================
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

    // PDF ukuran struk
    $pdf = new TCPDF('P', 'mm', array(80, 200), true, 'UTF-8', false);
    $pdf->SetMargins(5, 5, 5);
    $pdf->AddPage();
    $pdf->SetFont('courier', '', 10);

    // Header toko
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

    // Produk
    foreach ($produk as $index => $item) {
        $nama = $item["f_nama_produk"];
        $qty = $quantity[$index];
        $harga = number_format($item["f_harga_jual"]);
        $total = number_format($item["f_harga_jual"] * $qty);

        $pdf->Cell(0, 5, $nama, 0, 1, 'L');
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
    $pdf->Cell(0, 5, '--- Terima Kasih ---', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Barang yang sudah dibeli', 0, 1, 'C');
    $pdf->Cell(0, 5, 'tidak dapat dikembalikan.', 0, 1, 'C');

    $pdf->Output('struk.pdf', 'I');
    exit;
}

// ======================================================
// 2️⃣ LAPORAN BULANAN (GET bulan & tahun)
// ======================================================
elseif (isset($_GET["bulan"])) {
    // Ambil data bulan & tahun, default ke bulan & tahun sekarang
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');

    // Koneksi database
    $koneksi = new mysqli("localhost", "root", "", "kasir");
    if ($koneksi->connect_error) {
        die("Koneksi gagal: " . $koneksi->connect_error);
    }

    // Amankan input
    $bulan = mysqli_real_escape_string($koneksi, $bulan);
    $tahun = mysqli_real_escape_string($koneksi, $tahun);

    // Query data transaksi (disesuaikan dengan struktur tabel lo)
    $query = $koneksi->query("
        SELECT 
            t.f_id_transaksi,
            DATE_FORMAT(t.f_tanggal_pembelian, '%d-%m-%Y') AS tanggal_pembelian,
            GROUP_CONCAT(p.f_nama_produk ORDER BY d.f_id SEPARATOR ', ') AS produk_nama,
            GROUP_CONCAT(d.f_quantity ORDER BY d.f_id SEPARATOR ', ') AS jumlah,
            t.f_total_harga
        FROM t_transaksi t
        INNER JOIN t_detail_transaksi d ON t.f_id_transaksi = d.f_id_transaksi
        INNER JOIN t_produk p ON d.f_id_produk = p.f_id
        WHERE MONTH(t.f_tanggal_pembelian) = '$bulan' 
          AND YEAR(t.f_tanggal_pembelian) = '$tahun'
        GROUP BY t.f_id_transaksi
        ORDER BY t.f_tanggal_pembelian DESC
    ");

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Farel System');
    $pdf->SetAuthor('Farel');
    $pdf->SetTitle('Laporan Bulanan');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    // Judul laporan
    $html = '
    <h2 style="text-align:center;">Laporan Bulan ' . date('F', mktime(0, 0, 0, $bulan, 10)) . ' ' . $tahun . '</h2>
    <table border="1" cellspacing="0" cellpadding="5" width="100%">
        <thead>
        <tr style="background-color:#f2f2f2; font-weight:bold; text-align:center;">
            <th width="8%">No</th>
            <th width="20%">Tanggal</th>
            <th width="32%">Produk</th>
            <th width="20%">Jumlah</th>
            <th width="20%">Total (Rp)</th>
        </tr>
        </thead>
        <tbody>
    ';

    // Isi tabel
    $no = 1;
    $grandTotal = 0;
    while ($row = $query->fetch_assoc()) {
        $html .= '
        <tr>
            <td align="center">' . $no++ . '</td>
            <td>' . htmlspecialchars($row['tanggal_pembelian']) . '</td>
            <td>' . htmlspecialchars($row['produk_nama']) . '</td>
            <td align="center">' . htmlspecialchars($row['jumlah']) . '</td>
            <td align="right">' . number_format($row['f_total_harga'], 0, ',', '.') . '</td>
        </tr>';
        $grandTotal += $row['f_total_harga'];
    }

    // Tambah baris total
    $html .= '
        <tr style="background-color:#f2f2f2; font-weight:bold;">
            <td colspan="4" align="right">Total Keseluruhan</td>
            <td align="right">' . number_format($grandTotal, 0, ',', '.') . '</td>
        </tr>
    </tbody>
    </table>
    <p style="text-align:right; margin-top:20px;">Dicetak pada: ' . date('d-m-Y H:i') . '</p>
    ';

    // Tulis ke PDF
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('Laporan_' . $bulan . '_' . $tahun . '.pdf', 'I');
    exit;
}


// ======================================================
// 3️⃣ LAPORAN CHART (POST JSON base64)
// ======================================================
else {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['chart'])) {
        http_response_code(400);
        echo "Data chart tidak ditemukan!";
        exit;
    }

    $chartImage = $data['chart'];
    $chartImage = str_replace('data:image/png;base64,', '', $chartImage);
    $chartImage = str_replace(' ', '+', $chartImage);

    $chartFile = tempnam(sys_get_temp_dir(), 'chart_') . '.png';
    file_put_contents($chartFile, base64_decode($chartImage));

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Farel System');
    $pdf->SetAuthor('Farel');
    $pdf->SetTitle('Laporan Chart');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    $html = '
    <h2 style="text-align:center;">Laporan Chart</h2>
    <p style="text-align:center;">Dicetak pada: ' . date('d-m-Y H:i') . '</p><br>';
    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Image($chartFile, 25, 60, 160, 90, 'PNG');
    $pdf->Ln(100);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Visualisasi data hasil analisis bulan ini.', 0, 1, 'C');

    unlink($chartFile);
    $pdf->Output('laporan-chart.pdf', 'I');
    exit;
}
?>
