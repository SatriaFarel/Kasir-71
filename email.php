<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Setting SMTP (contoh: Gmail)
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'satriafarel40@gmail.com';

    $username = $_GET['nama'];
    $email = $_GET['email'];
    $otp = $_GET['otp'];
    $linkVerifikasi = "http://localhost/MKK/Satria%20Farel/Kasir%2071/forgotpw.php?email=$email&otp=$otp&step=verify";

    // --> Gunakan App Password tanpa spasi
    $mail->Password   = 'rrjntejtsazrhhib'; // <-- pakai string tanpa spasi

    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Penerima & isi
    $mail->setFrom('satriafarel40@gmail.com', 'Admin Kasir71'); // pengirim
    $mail->addAddress($email, $username); // penerima
    $mail->isHTML(true);
   
    $mail->Subject = 'Verifikasi Akun Kamu';
    $mail->Body    = '
        <p>Halo bro 👋</p>
        <p>Klik link di bawah ini untuk verifikasi akun kamu:</p>
        <p><a href="' . $linkVerifikasi . '">Verifikasi Sekarang</a></p>
        <br>
        <small>Kalau link nggak bisa diklik, copy dan paste URL berikut ke browser:</small><br>
        ' . $linkVerifikasi . '
    ';
    $mail->AltBody = "Halo bro, klik link ini untuk verifikasi akun: " . $linkVerifikasi;


    $mail->send();
    echo "<script>alert('✅ Email berhasil dikirim!');</script>";
    echo "<script>location.href='forgotpw.php';</script>";
} catch (Exception $e) {
    echo "<script>alert('❌ Email gagal dikirim. Error: {$mail->ErrorInfo}');</script>";
}
