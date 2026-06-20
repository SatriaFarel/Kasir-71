<?php
    include("../../../src/config.php");
    session_start();

    // Fungsi untuk mengupload gambar
    function uploadGambar($file, $id = null, $type = null) {
        $uploadDir = $type == "t_admin" ? "../../../asset/pfp/" : "../../../asset/product/"; // Path tempat menyimpan gambar
        $dbPath = $type == "t_admin" ? "../../asset/pfp/" : "../../asset/product/"; // Path yang disimpan di database

        // Jika ada ID, cek dan hapus gambar lama
        if ($id && $type) {
            global $conn;
            $query = "SELECT f_gambar FROM $type WHERE f_id = '$id'";
            $result = mysqli_query($conn, $query);
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $oldFile = $uploadDir . basename($row["f_gambar"]);
                if (!empty($row["f_gambar"]) && file_exists($oldFile)) {
                    unlink($oldFile);$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $gambar = bin2hex(random_bytes(5)) . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], $uploadDir . $gambar);
                }
            }
        }

        // Proses upload gambar baru
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $gambar = bin2hex(random_bytes(5)) . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $uploadDir . $gambar);

        return $dbPath . $gambar; // Kembalikan path yang akan disimpan di database
    }

    // Ambil data admin berdasarkan ID
    if (isset($_GET["IDA"])) {
        $id = $_GET["IDA"];
        $query = "SELECT * FROM t_admin WHERE f_id = '$id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
    }elseif(isset($_GET["IDM"])){
        $id = $_GET["IDM"];
        $query = "SELECT * FROM t_member WHERE f_id = '$id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
    }elseif(isset($_GET["IDP"])){
        $id = $_GET["IDP"];
        $query = "SELECT * FROM t_produk WHERE f_id = '$id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        $rows = mysqli_query($conn, "SELECT * FROM t_kategori");
    }elseif(isset($_GET["IDK"])){
        $id = $_GET["IDK"];
        $query = "SELECT * FROM t_kategori WHERE f_id = '$id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
    }

    // Proses update admin
    if (isset($_POST["admin"])) {
        $id = $_POST["id"];
        $username = htmlspecialchars($_POST["username"]);
        $email = htmlspecialchars($_POST["email"]);
        $password = htmlspecialchars($_POST["password"]);
        $gambar = $_POST["gambar"];

        if (empty($username) || empty($email) || empty($password)) {
            echo "<script>alert('Data yang dikirimkan belum lengkap!');document.location.href='../admin.php';</script>";
            exit;
        }

        //Cek apakah username atau email sudah ada di database
        $cek = mysqli_query($conn, "SELECT * FROM t_admin WHERE (f_username = '$username' OR f_email = '$email') AND f_id != $id");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Admin sudah terdaftar!');document.location.href='../admin.php';</script>";
            exit;
        }

        // Cek apakah ada gambar baru yang diupload
        if (!empty($_FILES["gambarNow"]["name"])) {
            $gambar = uploadGambar($_FILES["gambarNow"], $id, "t_admin"); // Panggil fungsi upload gambar
        }

        // Update data admin
        $query = "UPDATE t_admin SET f_username = '$username', f_email = '$email', f_password = '$password', f_gambar = '$gambar' WHERE f_id = '$id'";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Data Admin Berhasil Diubah!');document.location.href='../admin.php';</script>";
            exit;
        } else {
            echo "<script>alert('Data Admin Gagal Diubah!');document.location.href='../admin.php';</script>";
            exit;
        }
    }

    // Proses update member
    if (isset($_POST["member"])) {
        $id = $_POST["id"];
        $nama = htmlspecialchars($_POST["nama"]);
        $telp = htmlspecialchars($_POST["telp"]);
        $status = htmlspecialchars($_POST["status"]);
        $lastactive = date("Y-m-d");

        if (empty($nama) || empty($telp)) {
            echo "<script>alert('Data yang dikirimkan belum lengkap!');document.location.href='../member.php';</script>";
            exit;
        }

        // Cek apakah username atau email sudah ada di database
        $cek = mysqli_query($conn, "SELECT * FROM t_member WHERE f_no_telp = '$telp' AND f_id != $id");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Member sudah terdaftar!');document.location.href='../member.php';</script>";
            exit;
        }

        // Update data member
        $query = "UPDATE t_member SET f_nama_member = '$nama', f_no_telp = '$telp', f_last_activity = '$lastactive', f_status = '$status' WHERE f_id = '$id'";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Data Member Berhasil Diubah!');document.location.href='../member.php';</script>";
        } else {
            echo "<script>alert('Data Member Gagal Diubah!');document.location.href='../member.php';</script>";
        }
    }

    // Proses update kategori
    if (isset($_POST["kategori"])) {
        $id = $_POST["id"];
        $nama = htmlspecialchars($_POST["nama"]);
        if (empty($nama)) {
            echo "<script>alert('Data yang dikirimkan belum lengkap!');document.location.href='../product.php';</script>";
            exit;
        }

        // Cek apakah username atau email sudah ada di database
        $cek = mysqli_query($conn, "SELECT * FROM t_kategori WHERE f_kategori = '$nama' AND f_id != '$id'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Kategori sudah terdaftar!');document.location.href='../product.php';</script>";
            exit;
        }

        // Update data kategori
        $query = "UPDATE t_kategori SET f_kategori = '$nama' WHERE f_id = '$id'";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Data Kategori Berhasil Diubah!');document.location.href='../product.php';</script>";
        } else {
            echo "<script>alert('Data Kategori Gagal Diubah!');document.location.href='../product.php';</script>";
        }
    }

    // Proses update product
    if(isset($_POST["product"])){
        $id = $_POST["id"];
        $gambar = $_POST["gambar"] ?? "";
        $name = htmlspecialchars($_POST["name"]) ?? "";
        $price = htmlspecialchars($_POST["price"]) ?? "";
        $modal = htmlspecialchars($_POST["modal_price"]) ?? "";
        $stock = htmlspecialchars($_POST["stock"]) ?? "";
        $kategori = htmlspecialchars($_POST["category"]) ?? "";
        $tgl_exp = htmlspecialchars($_POST["expired_date"]) ?? "";
        $deskripsi = htmlspecialchars($_POST["description"]) ?? "";
    
        if(empty($name) || empty($price) || empty($modal) || $stock < 0 || empty($kategori) 
            || empty($tgl_exp) || empty($deskripsi) ){
            echo "<script>alert('Data yang dikirimkan belum lengkap!!');</script>";
        }

        // Cek apakah username atau email sudah ada di database
        $cek = mysqli_query($conn, "SELECT * FROM t_produk WHERE f_nama_produk = '$name' AND f_id != '$id'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Produk sudah terdaftar!');</script>";
            exit;
        }
    
        $keuntungan = ($price - $modal) * $stock ;
    
        if (!empty($_FILES["gambarNow"]["name"])) {
            $gambar = uploadGambar($_FILES["gambarNow"], $id, "t_produk"); // Panggil fungsi upload gambar
        }
    
        $query = "UPDATE t_produk 
          SET f_nama_produk = '$name', 
              f_tanggal_expired = '$tgl_exp', 
              f_stok = '$stock', 
              f_modal = '$modal', 
              f_harga_jual = '$price', 
              f_keuntungan = '$keuntungan', 
              f_id_kategori = '$kategori', 
              f_gambar = '$gambar', 
              f_deskripsi = '$deskripsi'
          WHERE f_id = '$id'";

    
        if(mysqli_query($conn, $query)){
            echo "<script>alert('Produk berhasil diubah!!');</script>";
        }else{
            echo "<script>alert('Produk gagal diubah!!');</script>";
        }
    
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>
<body class="h-screen bg-blue-400">

    <!-- Update Admin -->
    <?php if(isset($_GET["IDA"])) :?>
        <section>
            <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
                <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900">
                    <img class="w-8 h-8 mr-2" src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/logo.svg" alt="logo">
                    Flowbite    
                </a>
                <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0">
                    <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                        <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
                            Update an account
                        </h1>
                        <form class="space-y-4 md:space-y-6" method="post" enctype="multipart/form-data">
                            <div>
                                <input name="gambar" class="hidden" type="text" value="<?= $row['f_gambar']; ?>">
                                <input name="id" class="hidden" type="text" value="<?= $row['f_id']; ?>">
                                <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Your email</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v16H4z M4 4l8 8 8-8"></path>
                                    </svg>
                                    <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-10" value="<?= $row['f_email']; ?>" required <?= $_SESSION["id"] != $row["f_id"] ? 'readonly' : ''; ?>>
                                </div>
                            </div>

                            <div>
                                <label for="username" class="block mb-2 text-sm font-medium text-gray-900">Your username</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0 2c-4.418 0-8 3.134-8 7h16c0-3.866-3.582-7-8-7z"></path>
                                    </svg>
                                    <input type="text" name="username" id="username" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-10" value="<?= $row['f_username']; ?>" required>
                                </div>
                            </div>

                            <div>
                                <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">Your phone number</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 16.4c-.2 1.4-1.6 2.6-3 2.6-1.5 0-2.7-1.1-3.1-2.5l-1.4-3.3c-.3-.7-.9-1.1-1.7-1.1-1.6 0-2.9 1.3-3.1 2.8l-1.3 3.6c-.2.6-.6 1.1-1.1 1.4l-2.8 1.8c-1.2.8-2.7.5-3.6-.6l-2.3-3.3c-.7-1-.5-2.3.5-3.1l2.7-2c.9-.7 1.3-1.8 1.3-2.8V8c0-1.5-1.1-2.7-2.5-3.1l-3.3-1.4c-.7-.3-1.1-.9-1.1-1.7 0-1.6 1.3-2.9 2.8-3.1l3.6-1.3c.6-.2 1.1-.6 1.4-1.1l1.8-2.8c.8-1.2.5-2.7-.6-3.6l-3.3-2.3c-1-.7-2.3-.5-3.1.5l-2 2.7c-.7.9-1.8 1.3-2.8 1.3H8c-1.5 0-2.7 1.1-3.1 2.5L3 7c-.3.7-.9 1.1-1.7 1.1-1.6 0-2.9 1.3-3.1 2.8l-1.3 3.6c-.2.6-.6 1.1-1.1 1.4l-2.8 1.8c-1.2.8-2.7.5-3.6-.6l-2.3-3.3c-.7-1-.5-2.3.5-3.1l2.7-2c.9-.7 1.3-1.8 1.3-2.8V8c0-1.5-1.1-2.7-2.5-3.1l-3.3-1.4c-.7-.3-1.1-.9-1.1-1.7 0-1.6 1.3-2.9 2.8-3.1l3.6-1.3c.6-.2 1.1-.6 1.4-1.1l1.8-2.8c.8-1.2.5-2.7-.6-3.6l-3.3-2.3c-1-.7-2.3-.5-3.1.5l-2 2.7c-.7.9-1.8 1.3-2.8 1.3h-8c-1.5 0-2.7 1.1-3.1 2.5l-3.6 1.3c-.6.2-1.1.6-1.4 1.1l-1.8 2.8c-.8 1.2-.5 2.7.6 3.6l2.3 3.3c1 .7 2.3.5 3.1-.5l2-2.7c.7-.9 1.8-1.3 2.8-1.3h8c1.5 0 2.7 1.1 3.1 2.5l3.6 1.3c.6.2 1.1.6 1.4 1.1l1.8 2.8c.8 1.2.5 2.7-.6 3.6l-3.3 2.3c-1.2.7-2.7.5-3.6-.6l-2.3-3.3c-.7-1-.5-2.3.5-3.1l2.7-2c.9-.7 1.3-1.8 1.3-2.8V8c0-1.5-1.1-2.7-2.5-3.1l-3.3-1.4c-.7-.3-1.1-.9-1.1-1.7 0-1.6 1.3-2.9 2.8-3.1l3.6-1.3c.6-.2 1.1-.6 1.4-1.1l1.8-2.8c.8-1.2-.5-2.7-1.6-3.6l-3.3-2.3c-1-.7-2.3-.5-3.1.5l-2.7 2c-.9.7-1.3 1.8-1.3 2.8V16c0 1.5 1.1 2.7 2.5 3.1l3.3 1.4c.7.3 1.1.9 1.1 1.7 0 1.6-1.3 2.9-2.8 3.1l-3.6 1.3c-.6.2-1.1.6-1.4 1.1l-1.8 2.8c-.8 1.2 1.6 2.7 3.6 3.6L10 3z"></path>
                                    </svg>
                                    </svg>
                                    <input type="tel" name="phone" id="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-10" value="<?= $row['f_phone'];?>" required <?= $_SESSION["id"] != $row["f_id"] ? 'readonly' : ''; ?>>
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Your password</label>
                                <div class="flex relative">
                                    <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M17 10h-1V7a5 5 0 0 0-10 0v3H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2Zm-8-3a3 3 0 1 1 6 0v3H9Zm8 13H5v-8h12ZM12 14a1.5 1.5 0 0 1 1.5 1.5c0 .6-.34 1.1-.85 1.35v1.15a.65.65 0 1 1-1.3 0v-1.15a1.5 1.5 0 0 1 .65-2.85Z"/>
                                        </svg>
                                    </span>
                                    <?php if( $_SESSION["id"] != $row["f_id"]):?>
                                    <input type="password" id="password" name="password" class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5" value="<?= $row['f_password']; ?>" readonly>
                                    <?php else: ?>
                                    <input type="password" id="password" name="password" class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5" value="<?= $row['f_password']; ?>">
                                    <?php endif;?>
                                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-2 flex items-center px-2 text-gray-500">
                                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 3C5.455 3 1.733 6.403.625 10c1.108 3.597 4.83 7 9.375 7s8.267-3.403 9.375-7C18.267 6.403 14.545 3 10 3zm0 12c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0-2a4 4 0 110-8 4 4 0 010 8z"/>
                                        </svg>
                                    </button>
                                </div>    
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="user_avatar" class="text-sm font-medium text-gray-900">Upload Foto Profil</label>
                                
                                <label for="user_avatar" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-900 bg-gray-100 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-200">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Pilih Gambar
                                </label>

                                <input id="user_avatar" name="gambarNow" type="file" class="hidden" <?= $_SESSION["id"] == $row["f_id"] ? 'readonly' : ''; ?>>

                                <p class="text-sm text-gray-500">Gambar profil membantu mengidentifikasi akun Anda.</p>
                            </div>

                            <button type="submit" name="admin" class="w-full text-white bg-blue-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                Update account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>


    <!-- Update Member -->
    <?php if(isset($_GET["IDM"])) :?>
    <section>
        <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
            <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900">
                <img class="w-8 h-8 mr-2" src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/logo.svg" alt="logo">
                Flowbite    
            </a>
            <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0">
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
                        Update an member
                    </h1>
                    <form class="space-y-4 md:space-y-6" method="post">
                        <div>
                            <input name="id" class="hidden" type="text" value="<?= $row['f_id'];?>">
                            <label for="nama" class="block mb-2 text-sm font-medium text-gray-900">Your nama</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v16H4z M4 4l8 8 8-8"></path>
                                </svg>
                                <input type="nama" name="nama" id="nama" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-10" value="<?= $row['f_nama_member'];?>" required="">
                            </div>
                        </div>

                        <div>
                            <label for="telp" class="block mb-2 text-sm font-medium text-gray-900">Your no telp</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0 2c-4.418 0-8 3.134-8 7h16c0-3.866-3.582-7-8-7z"></path>
                                </svg>
                                <input type="text" name="telp" id="telp" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-10" value="<?=$row['f_no_telp'];?>" required="">
                            </div>
                        </div>

                        <!-- <div>
                            <label for="point" class="block mb-2 text-sm font-medium text-gray-900">Your point</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L15 8l6 .5-4 4 1 6-5-3-5 3 1-6-4-4 6-.5z"></path>
                                </svg>
                                <input type="text" name="point" id="point" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-10" value="<?= $row['f_point']; ?>" required>
                            </div>
                        </div> -->


                        <div>
                            <label for="status" class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                            <div class="relative">
                                <!-- Icon (misalnya, icon garis horizontal seperti "menu" atau "adjustments") -->
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </div>
                                <select id="status" name="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 pl-10">
                                    <option value="Aktif" <?= ($row['f_status'] == 'Aktif' ? 'selected' : ''); ?>>Aktif</option>
                                    <option value="Tidak Aktif" <?= ($row['f_status'] == 'Tidak Aktif' ? 'selected' : ''); ?>>Non Aktif</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" name="member" class="w-full text-white bg-blue-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center ">Update an member</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php endif;?>

    <!-- Update Kategori -->
    <?php if(isset($_GET["IDK"])) :?>
    <section>
        <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
            <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900">
                <img class="w-8 h-8 mr-2" src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/logo.svg" alt="logo">
                Flowbite    
            </a>
            <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0">
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
                        Update an kategori
                    </h1>
                    <form class="space-y-4 md:space-y-6" method="post">
                        <div>
                            <input name="id" class="hidden" type="text" value="<?= $row['f_id'];?>">
                            <label for="nama" class="block mb-2 text-sm font-medium text-gray-900">Your nama</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v16H4z M4 4l8 8 8-8"></path>
                                </svg>
                                <input type="nama" name="nama" id="nama" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-10" value="<?= $row['f_kategori'];?>" required="">
                            </div>
                        </div>

                        <button type="submit" name="kategori" class="w-full text-white bg-blue-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center ">Update an kategori</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php endif;?>

    <!-- Update Product -->
    <?php if(isset($_GET["IDP"])):?>
    <section class="flex justify-center items-center min-h-screen">
        <div class="max-w-2xl w-full bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Update Product</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="grid gap-4 mb-6 sm:grid-cols-2">

                    <input type="text" name="id" value="<?= $_GET['IDP'];?>" hidden>
                    <input type="text" name="gambar" value="<?= $row['f_gambar'];?>" hidden>


                    <!-- Product Name -->
                    <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <!-- Pencil Icon -->
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M16.5 2.5l5 5L9 21H4v-5L16.5 2.5z" />
                        </svg>
                        </div>
                        <input type="text" id="name" name="name" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500" value="<?= $row['f_nama_produk'];?>" required>
                    </div>
                    </div>

                    <!-- Price -->
                    <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <!-- Dollar Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3m0 0c1.657 0 3-1.343 3-3s-1.343-3-3-3m0 0V4m0 4v4m0 4v4" />
                        </svg>
                        </div>
                        <input type="number" id="price" name="price" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500" value="<?= $row['f_harga_jual'];?>" required>
                    </div>
                    </div>

                    <!-- Harga Modal -->
                    <div>
                    <label for="modal_price" class="block text-sm font-medium text-gray-700">Harga Modal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <!-- Dollar Icon (sama dengan Price) -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3m0 0c1.657 0 3-1.343 3-3s-1.343-3-3-3m0 0V4m0 4v4m0 4v4" />
                        </svg>
                        </div>
                        <input type="number" id="modal_price" name="modal_price" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500" value="<?= $row['f_modal'];?>" required>
                    </div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <!-- List Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </div>
                            <select id="category" name="category" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach($rows as $cat): ?>
                                    <option value="<?= $cat['f_id']; ?>" <?= ($cat['f_id'] == $row['f_id_kategori'] ? 'selected' : ''); ?>>
                                        <?= $cat['f_kategori']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Stock Product -->
                    <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700">Stock Product</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <!-- Box Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 11h18M3 15h18" />
                        </svg>
                        </div>
                        <input type="number" id="stock" name="stock" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500"value="<?= $row['f_stok'];?>" required>
                    </div>
                    </div>

                    <!-- Tanggal Expired -->
                    <div class="sm:col-span-2">
                    <label for="expired_date" class="block text-sm font-medium text-gray-700">Tanggal Expired</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <!-- Calendar Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-10 4h10m-10 4h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        </div>
                        <input type="date" id="expired_date" name="expired_date" value="<?= $row['f_tanggal_expired'];?>" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    </div>

                    <!-- Upload Foto Product -->
                    <div class="sm:col-span-2" id="uploadContainer">
                        <label for="user_avatar" class="text-sm font-medium text-gray-900">
                            Upload Foto Product
                        </label>
                        <div class="flex flex-col gap-2">
                            <!-- Label yang akan menampilkan nama file jika sudah diupload -->
                            <label id="fileLabel" for="user_avatar" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-900 bg-gray-100 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-200">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span id="fileLabelText">Pilih Gambar</span>
                            </label>
                            <!-- Input file tersembunyi -->
                            <input id="user_avatar" name="gambarNow" type="file" class="hidden">
                            <!-- Pesan default -->
                            <p class="text-sm text-gray-500" id="defaultMessage">
                            Gambar product membantu mengidentifikasi product Anda.
                            </p>
                            <!-- Pesan setelah file diupload -->
                            <p class="text-sm text-green-600 hidden" id="uploadedMessage">
                            File uploaded: <span id="uploadedFileName"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 pointer-events-none">
                        <!-- Pencil Icon -->
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M16.5 2.5l5 5L9 21H4v-5L16.5 2.5z" />
                        </svg>
                        </div>
                        <textarea name="description" id="description" rows="4" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500"><?= $row["f_deskripsi"];?></textarea>
                    </div>
                    </div>
                </div>

                <!-- Button Section -->
                <div class="flex justify-between mt-4">
                    <a href="../product.php">
                        <button type="button" class="px-5 py-2.5 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium transition duration-300">Back</button>
                    </a>
                    <div class="flex space-x-4">
                    <button type="submit" name="product" class="flex items-center px-5 py-2.5 text-white bg-green-600 hover:bg-green-700 rounded-lg font-medium transition duration-300">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        Update
                    </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <?php endif;?>
    
</body>
</html>