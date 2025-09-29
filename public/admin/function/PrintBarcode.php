<?php
require_once '../../../src/vendor/autoload.php';
use TCPDF as TCPDF;

// Koneksi ke database
$db = new mysqli('localhost', 'root', '', 'kasir');

// Cek koneksi
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Ambil parameter ID dari URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId > 0) {
    // Ambil data produk dari database
    $stmt = $db->prepare("SELECT f_nama_produk, f_qr FROM t_produk WHERE f_id = ?");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        // Buat PDF baru dengan ukuran seperti struk (80mm x 120mm)
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array(80, 120), true, 'UTF-8', false);

        // Set dokumen
        $pdf->SetCreator('Your Company');
        $pdf->SetAuthor('Satria Farel Cipta Permata');
        $pdf->SetTitle('Barcode Produk - ' . $product['f_nama_produk']);
        $pdf->SetSubject('Barcode Produk');

        // Margin kecil
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);

        // Tambah halaman
        $pdf->AddPage();

        
        // Konten PDF
        $html = '
        <style>
            .center { text-align: center; }
            .title { font-size: 16px; font-weight: bold; margin-bottom: 10px; }
            .barcode { margin: 20px 0; }
            .product-name { font-size: 14px; margin-top: 10px; }
        </style>
        <div class="center">
            <div class="title">Barcode Produk</div>';
        
        // Jika ada QR code
        if (!empty($product['f_qr'])) {
                
            $img = $product["f_qr"];
            $imgPath = realpath('../../../asset/barcodes/' . $img);
            if ($imgPath && file_exists($imgPath)) {
                $html .= '<div class="barcode"><img src="' . $imgPath . '" width="200" /></div>';
            } else {
                $html .= '<div class="barcode">QR Code tidak tersedia</div>';
            }
            
        } else {
            $html .= '<div class="barcode">QR Code tidak tersedia</div>';
        }
        
        $html .= '<div class="product-name">' . htmlspecialchars($product['f_nama_produk']) . '</div>';
        $html .= '</div>';
        
        // Output HTML ke PDF
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $pdf->Output('barcode_produk_' . $productId . '.pdf', 'I');
        exit;
    }
}

// Jika data tidak ditemukan
echo '<h1>Produk tidak ditemukan</h1>';
?>