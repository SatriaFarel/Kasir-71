<?php
session_start();
include 'src/config.php';

// 1) HANDLE FORM KIRIM OTP
if (isset($_POST["btn_otp"])) {
    $email = mysqli_real_escape_string($conn, $_POST["email"]);

    // cek email terdaftar
    $res = mysqli_query($conn, "SELECT f_username, f_email FROM t_admin WHERE f_email = '$email'");
    if (!$res || mysqli_num_rows($res) === 0) {
        echo "<script>alert('Email belum terdaftar.'); history.back();</script>";
        exit;
    }
    $user = mysqli_fetch_assoc($res);
    $nama = $user['f_username'];

    // generate OTP & expired
    $otp         = rand(100000, 999999);
    $otp_expired = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // simpan ke DB
    $upd = mysqli_query($conn,
        "UPDATE t_admin SET f_otp = '$otp', f_otp_expired = '$otp_expired' WHERE f_email = '$email'"
    );
    if (!$upd) {
        echo "<script>alert('Gagal menyimpan OTP. Silakan coba lagi.'); history.back();</script>";
        exit;
    }

    if($upd){
      header("Location: email.php?nama=$user&email=$email&otp=$otp");
      $response = true;
    }
   

    // kirim OTP via email
    if ($response) {
        echo "<script>
                alert('OTP berhasil dikirim ke $no_telp!');
              </script>";
    } else {
        echo "<script>
                alert('Gagal kirim OTP ke no_telp. Coba lagi.');
                history.back();
              </script>";
    }
    exit;
}

// 2) Handle link verifikasi dari email
if (isset($_GET['step']) && $_GET['step'] === 'verify') {
    $email = htmlspecialchars($_GET['email'] ?? '');
    $otp   = htmlspecialchars($_GET['otp'] ?? '');

    // cek parameter
    if (empty($email) || empty($otp)) {
        echo "<script>alert('Parameter tidak lengkap.'); history.back();</script>";
        exit;
    }

    $query = "SELECT f_email, f_otp, f_otp_expired FROM t_admin WHERE f_email = '$email' AND f_otp = '$otp'";
    $result = mysqli_query($conn, $query);  
    if (!$result || mysqli_num_rows($result) === 0) {
        echo "<script>alert('Link verifikasi tidak valid.'); history.back();</script>";
        exit;
    }

    // tampilkan form verifikasi OTP

}

// 3) HANDLE FORM VERIFIKASI OTP & RESET PASS
if (isset($_POST['verify_otp'])) {
    $email        = mysqli_real_escape_string($conn, $_POST['email']);
    $otp_input    = mysqli_real_escape_string($conn, $_POST['otp']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);

    // ambil dari DB
    $res = mysqli_query($conn, "SELECT f_otp, f_otp_expired FROM t_admin WHERE f_email = '$email'");
    if (!$res || mysqli_num_rows($res) === 0) {
        echo "<script>alert('Email tidak terdaftar.'); history.back();</script>";
        exit;
    }
    $row        = mysqli_fetch_assoc($res);
    $db_otp     = $row['f_otp'];
    $db_expired = $row['f_otp_expired'];

    // cek validitas
    if ($otp_input === $db_otp && time() < strtotime($db_expired)) {
        // reset password (gunakan password_hash() di real project)
        $upd = mysqli_query($conn,
            "UPDATE t_admin SET f_password = '$new_password', f_otp = null, f_otp_expired = null WHERE f_email = '$email'"
        );
        if ($upd) {
            echo "<script>
                    alert('Password berhasil direset!');
                    location.href='index.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal mengupdate password.');
                    history.back();
                  </script>";
        }
    } else {
        echo "<script>
                alert('OTP salah atau kadaluarsa!');
                history.back();
              </script>";
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 h-screen flex justify-center items-center">
  <div class="bg-white p-8 rounded-lg shadow-lg w-96">
    <h2 class="text-2xl font-semibold text-blue-600 mb-6 text-center">
      <?= (isset($_GET['step']) && $_GET['step'] === 'verify') ? 'Verifikasi OTP' : 'Lupa Password'; ?>
    </h2>

    <?php if (!isset($_GET['step']) || $_GET['step'] !== 'verify'): ?>
      <form method="POST" class="space-y-4">
        <label class="block text-blue-600">Masukkan Email</label>
        <input type="email" name="email" required placeholder="Contoh: email@example.com"
               class="w-full p-3 border border-blue-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <button name="btn_otp" type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded w-full hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
          Kirim OTP
        </button>
      </form>
    <?php else: ?>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email']) ?>" />
        <input type="hidden" name="otp" value="<?= htmlspecialchars($_GET['otp']) ?>" />
        <label class="block text-blue-600">Password Baru</label>
        <input type="password" name="new_password" required placeholder="Password Baru"
               class="w-full p-3 border border-blue-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <button type="submit" name="verify_otp"
                class="bg-blue-600 text-white px-4 py-2 rounded w-full hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
          Reset Password
        </button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
