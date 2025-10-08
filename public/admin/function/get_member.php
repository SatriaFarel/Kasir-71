<?php
include("../../src/cookie.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$noTelp = $_GET['noTelp'] ?? '';

if($noTelp != '') {
    $stmt = $conn->prepare("SELECT f_id, f_nama_member, f_point FROM t_member WHERE f_no_telp = ? AND f_status = 'Aktif'");
    $stmt->bind_param("s", $noTelp);
    $stmt->execute();
    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        // Return null jika data tidak ditemukan
        echo json_encode(['f_id'=>null, 'message'=>'Member tidak ditemukan']);
    }

    $stmt->close();
} else {
    echo json_encode(['f_id'=>null, 'message'=>'Nomor telepon kosong']);
}
?>
