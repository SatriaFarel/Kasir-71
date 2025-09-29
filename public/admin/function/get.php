<?php
include("../../../src/cookie.php");

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'tahun';
$whereClause = "";

switch ($filter) {
    case 'bulan':
        $whereClause = "AND MONTH(f_tanggal_pembelian) = MONTH(CURRENT_DATE()) AND YEAR(f_tanggal_pembelian) = YEAR(CURRENT_DATE())";
        break;
    case 'minggu':
        $whereClause = "AND WEEK(f_tanggal_pembelian) = WEEK(CURRENT_DATE()) AND YEAR(f_tanggal_pembelian) = YEAR(CURRENT_DATE())";
        break;
    default: // tahun
        $whereClause = "AND YEAR(f_tanggal_pembelian) = YEAR(CURRENT_DATE())";
        break;
}

$query = "SELECT f_id_transaksi, SUM(f_total_keuntungan) AS total_keuntungan FROM t_transaksi WHERE 1 $whereClause GROUP BY f_id_transaksi";
$result = mysqli_query($conn, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        'id_transaksi' => $row['f_id_transaksi'],
        'keuntungan' => (float) $row['total_keuntungan']
    ];
}

echo json_encode($data);
