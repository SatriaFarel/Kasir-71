<?php

require '../../../src/vendor/autoload.php'; // pastikan sudah install via composer

use Picqer\Barcode\BarcodeGeneratorPNG;

include("../../../src/config.php");

if(isset($_GET["product"])){
    $kategori = "SELECT * FROM t_kategori";
    $rows = mysqli_query($conn, $kategori);
}

if(isset($_POST["admin"])){
    $username = htmlspecialchars($_POST["username"]) ?? "";
    $email = htmlspecialchars($_POST["email"]) ?? "";
    $password = htmlspecialchars($_POST["password"]) ?? "";
    $phone = htmlspecialchars($_POST["phone"]) ?? "";

    if(empty($username) || empty($email) || empty($password) || empty($phone)){
        echo "<script>alert('Data yang dikirimkan belum lengkap!!');document.location.href='../admin.php'</script>";
        exit;
    }

    $cek = mysqli_query($conn, "SELECT * FROM t_admin WHERE f_username = '$username' OR f_email = '$email' OR f_phone = '$phone'");
    if($cek->num_rows > 0){
        echo "<script>alert('Admin sudah terdaftar!!');document.location.href='../admin.php'</script>";
        exit;
    }

    $gambar = upload();

    // Ambil ID terakhir dan tambah 1
    $cekid = mysqli_query($conn, "SELECT f_id FROM t_admin ORDER BY f_id DESC LIMIT 1");
    $row = $cekid->fetch_assoc();
    $id = $row ? $row["f_id"] + 1 : 1;

    $query = "INSERT INTO t_admin(f_id, f_email, f_username, f_password, f_phone, f_gambar) VALUES ('$id', '$email', '$username', '$password', '$phone', '$gambar')";
    if(mysqli_query($conn,$query)){
        echo "<script>alert('Admin berhasil ditambahkan!!'); document.location.href='../admin.php';</script>";
        exit;
    }else{
        echo "<script>alert('Admin gagal terdaftar!!');</script>";
    }
}

if(isset($_POST["member"])){
    $nama = htmlspecialchars($_POST["nama"]) ?? "";
    $telp = htmlspecialchars($_POST["no_telepon"]) ?? "";
    $status = "Aktif";
    $point = 0;
    $now = date("Y-m-d");

    if(empty($nama) || empty($telp)){
        echo "<script>alert('Data yang dikirimkan belum lengkap!!');document.location.href='../member.php'</script>";
        exit;
    }

    $cek = mysqli_query($conn, "SELECT * FROM t_member WHERE f_nama_member = '$nama' OR f_no_telp = '$telp'");
    if($cek->num_rows > 0){
        echo "<script>alert('Member sudah terdaftar!!');document.location.href='../member.php'</script>";
        exit;
    }

    $gambar = upload();

    // Ambil ID terakhir dan tambah 1
    $cekid = mysqli_query($conn, "SELECT f_id FROM t_member ORDER BY f_id DESC LIMIT 1");
    $row = $cekid->fetch_assoc();
    $id = $row ? $row["f_id"] + 1 : 1;

    $query = "INSERT INTO t_member(f_id, f_nama_member, f_no_telp, f_status, f_last_activity, f_point) VALUES ('$id', '$nama', '$telp', '$status', '$now', '$point')";
    if(mysqli_query($conn,$query)){
        echo "<script>alert('Member berhasil ditambahkan!!');document.location.href='../member.php';</script>";
        exit;
    }else{
        echo "<script>alert('Member gagal terdaftar!!');document.location.href='../member.php';</script>";
    }
}

if(isset($_POST["kategori"])){
    $nama = htmlspecialchars($_POST["nama_kategori"]) ?? "";

    if(empty($nama)){
        echo "<script>alert('Data yang dikirimkan belum lengkap!!');document.location.href='../product.php'</script>";
        exit;
    }

    $cek = mysqli_query($conn, "SELECT * FROM t_kategori WHERE f_kategori = '$nama'");
    if($cek->num_rows > 0){
        echo "<script>alert('Kategori sudah ada!!');document.location.href='../product.php'</script>";
        exit;
    }

    $gambar = upload();

    // Ambil ID terakhir dan tambah 1
    $cekid = mysqli_query($conn, "SELECT f_id FROM t_kategori ORDER BY f_id DESC LIMIT 1");
    $row = $cekid->fetch_assoc();
    $id = $row ? $row["f_id"] + 1 : 1;

    $query = "INSERT INTO t_kategori(f_id, f_kategori) VALUES ('$id', '$nama')";
    if(mysqli_query($conn,$query)){
        echo "<script>alert('Kategori berhasil ditambahkan!!');document.location.href='../product.php';</script>";
        exit;
    }else{
        echo "<script>alert('Kategori gagal terdaftar!!');document.location.href='../product.php';</script>";
    }
}

if(isset($_POST["product"])){
    $name = htmlspecialchars($_POST["name"]) ?? "";
    $kodeP = htmlspecialchars($_POST["kode_produk"]) ?? "";
    $price = htmlspecialchars($_POST["price"]) ?? "";
    $modal = htmlspecialchars($_POST["modal_price"]) ?? "";
    $stock = htmlspecialchars($_POST["stock"]) ?? "";
    $kategori = htmlspecialchars($_POST["category"]) ?? "";
    $tgl_exp = htmlspecialchars($_POST["expired_date"]) ?? "";
    $deskripsi = htmlspecialchars($_POST["description"]) ?? "";

    if(empty($name) || empty($kodeP) || empty($price) || empty($modal) || empty($stock) || empty($kategori) 
        || empty($tgl_exp) || empty($deskripsi)){
        echo "<script>alert('Data yang dikirimkan belum lengkap!!');document.location.href='../product.php'";
        exit;
    }

    $keuntungan = $price - $modal;

    $gambar = upload();

    // Cek apakah data sudah pernah ditambahkan
    $cekData = mysqli_query($conn, "SELECT * FROM t_produk WHERE f_nama_produk = '$name' OR f_kodep = '$kodeP'");
    if($cekData->num_rows > 0){
        echo "<script>alert('Data produk sudah ada!!');document.location.href='../product.php'";    
        exit;
    }

    // Ambil ID terakhir dan tambah 1 
    $cekid = mysqli_query($conn, "SELECT * FROM t_produk ORDER BY f_id DESC LIMIT 1");
    $row = $cekid->fetch_assoc();
    $id = $row ? $row["f_id"] + 1 : 1;

    // Inisialisasi barcode generator
    $generator = new BarcodeGeneratorPNG();

    // Menentukan nama file berdasarkan ID Produk (misalnya barcode_1.png)
    $file_name = 'barcode_' . $kodeP . '.png';
    $file_path = '../../../asset/barcodes/' . $file_name; // Direktori 'barcodes' harus ada

    // Generate barcode dan simpan sebagai file PNG
    file_put_contents($file_path, $generator->getBarcode($kodeP, $generator::TYPE_CODE_128));

    $query = "INSERT INTO t_produk VALUES('$id', '$kodeP','$name','$tgl_exp','$stock','$modal','$price','$keuntungan','$kategori','$gambar', '$file_name', '$deskripsi')";

    if(mysqli_query($conn, $query)){
        echo "<script>alert('Produk berhasil ditambahkan!!'); document.location.href='../product.php'</script>";
    }else{
        echo "<script>alert('Produk gagal ditambahkan!!');document.location.href='../product.php'</script>";
    }

}

function upload(){
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $gambar_tmp = $_FILES['gambar']['tmp_name'];
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION); // Ambil ekstensi file
        $random_name = bin2hex(random_bytes(5)) . '.' . $ext; // Nama file acak 8 huruf
        if(isset($_POST["product"])){ $upload_dir = '../../../asset/product/';}
        else{$upload_dir = '../../../asset/pfp/';}
        $gambar_path = $upload_dir . $random_name;

        if (move_uploaded_file($gambar_tmp, $gambar_path)) {
            if(isset($_POST["product"])){ $upload_dir = '../../asset/product/';}
            else{$upload_dir = '../../asset/pfp/';}
            $gambar_path = $upload_dir . $random_name;
            return $gambar_path;
        }
    }
    return ''; // Jika gagal upload, kembalikan string kosong
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

    <!-- Create Admin -->
    <?php if(isset($_GET["admin"])) :?>
    <section>
        <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
            <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900">
                <img class="w-8 h-8 mr-2" src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/logo.svg" alt="logo">
                Flowbite    
            </a>
            <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0">
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
                        Create an account
                    </h1>
                    <form class="space-y-4 md:space-y-6" method="post" enctype="multipart/form-data">
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Your email</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v16H4z M4 4l8 8 8-8"></path>
                                </svg>
                                <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-10" placeholder="name@company.com" required="">
                            </div>
                        </div>

                        <div>
                            <label for="username" class="block mb-2 text-sm font-medium text-gray-900">Your username</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0 2c-4.418 0-8 3.134-8 7h16c0-3.866-3.582-7-8-7z"></path>
                                </svg>
                                <input type="text" name="username" id="username" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-10" placeholder="yourusername" required="">
                            </div>
                        </div>

                        <div>
                            <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">Your phone number</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 16.4c-.2 1.4-1.6 2.6-3 2.6-1.5 0-2.7-1.1-3.1-2.5l-1.4-3.3c-.3-.7-.9-1.1-1.7-1.1-1.6 0-2.9 1.3-3.1 2.8l-1.3 3.6c-.2.6-.6 1.1-1.1 1.4l-2.8 1.8c-1.2.8-2.7.5-3.6-.6l-2.3-3.3c-.7-1-.5-2.3.5-3.1l2.7-2c.9-.7 1.3-1.8 1.3-2.8V8c0-1.5-1.1-2.7-2.5-3.1l-3.3-1.4c-.7-.3-1.1-.9-1.1-1.7 0-1.6 1.3-2.9 2.8-3.1l3.6-1.3c.6-.2 1.1-.6 1.4-1.1l1.8-2.8c.8-1.2.5-2.7-.6-3.6l-3.3-2.3c-1-.7-2.3-.5-3.1.5l-2 2.7c-.7.9-1.8 1.3-2.8 1.3H8c-1.5 0-2.7 1.1-3.1 2.5L3 7c-.3.7-.9 1.1-1.7 1.1-1.6 0-2.9 1.3-3.1 2.8l-1.3 3.6c-.2.6-.6 1.1-1.1 1.4l-2.8 1.8c-1.2.8-2.7.5-3.6-.6l-2.3-3.3c-.7-1-.5-2.3.5-3.1l2.7-2c.9-.7 1.3-1.8 1.3-2.8V8c0-1.5-1.1-2.7-2.5-3.1l-3.3-1.4c-.7-.3-1.1-.9-1.1-1.7 0-1.6 1.3-2.9 2.8-3.1l3.6-1.3c.6-.2 1.1-.6 1.4-1.1l1.8-2.8c.8-1.2.5-2.7-.6-3.6l-3.3-2.3c-1-.7-2.3-.5-3.1.5l-2 2.7c-.7.9-1.8 1.3-2.8 1.3h-8c-1.5 0-2.7 1.1-3.1 2.5l-3.6 1.3c-.6.2-1.1.6-1.4 1.1l-1.8 2.8c-.8 1.2-.5 2.7.6 3.6l2.3 3.3c1 .7 2.3.5 3.1-.5l2-2.7c.7-.9 1.8-1.3 2.8-1.3h8c1.5 0 2.7 1.1 3.1 2.5l3.6 1.3c.6.2 1.1.6 1.4 1.1l1.8 2.8c.8 1.2.5 2.7-.6 3.6l-3.3 2.3c-1.2.7-2.7.5-3.6-.6l-2.3-3.3c-.7-1-.5-2.3.5-3.1l2.7-2c.9-.7 1.3-1.8 1.3-2.8V8c0-1.5-1.1-2.7-2.5-3.1l-3.3-1.4c-.7-.3-1.1-.9-1.1-1.7 0-1.6 1.3-2.9 2.8-3.1l3.6-1.3c.6-.2 1.1-.6 1.4-1.1l1.8-2.8c.8-1.2-.5-2.7-1.6-3.6l-3.3-2.3c-1-.7-2.3-.5-3.1.5l-2.7 2c-.9.7-1.3 1.8-1.3 2.8V16c0 1.5 1.1 2.7 2.5 3.1l3.3 1.4c.7.3 1.1.9 1.1 1.7 0 1.6-1.3 2.9-2.8 3.1l-3.6 1.3c-.6.2-1.1.6-1.4 1.1l-1.8 2.8c-.8 1.2 1.6 2.7 3.6 3.6L10 3z"></path>
                                </svg>
                                </svg>
                                <input type="tel" name="phone" id="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-10" placeholder="123-456-7890" required="">
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
                                <input type="password" id="password" name="password" class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5" placeholder="Masukkan password">
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-2 flex items-center px-2 text-gray-500">
                                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 3C5.455 3 1.733 6.403.625 10c1.108 3.597 4.83 7 9.375 7s8.267-3.403 9.375-7C18.267 6.403 14.545 3 10 3zm0 12c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0-2a4 4 0 110-8 4 4 0 010 8z"/>
                                    </svg>
                                </button>
                            </div>    
                        </div>
                        <!-- <div class="flex relative">
                            <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17 10h-1V7a5 5 0 0 0-10 0v3H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2Zm-8-3a3 3 0 1 1 6 0v3H9Zm8 13H5v-8h12ZM12 14a1.5 1.5 0 0 1 1.5 1.5c0 .6-.34 1.1-.85 1.35v1.15a.65.65 0 1 1-1.3 0v-1.15a1.5 1.5 0 0 1 .65-2.85Z"/>
                            </svg>
                            </span>
                            <input type="password" id="password" name="konfirmasi-password" class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5" placeholder="Masukkan konfirmasi password">
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-2 flex items-center px-2 text-gray-500">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 3C5.455 3 1.733 6.403.625 10c1.108 3.597 4.83 7 9.375 7s8.267-3.403 9.375-7C18.267 6.403 14.545 3 10 3zm0 12c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0-2a4 4 0 110-8 4 4 0 010 8z"/>
                                </svg>
                            </button>
                        </div> -->
                        <!-- Upload Foto pfp -->
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
                                <input id="user_avatar" name="gambar" type="file" class="hidden">
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

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="terms" aria-describedby="terms" type="checkbox" class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 " required="">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="terms" class="font-light text-gray-500 ">I accept the <a class="font-medium text-primary-600 hover:underline" href="#">Terms and Conditions</a></label>
                            </div>
                        </div>

                        <button type="submit" name="admin" class="w-full text-white bg-blue-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center ">Create an account</button>
                        <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                            Already have an account? <a href="#" class="font-medium text-primary-600 hover:underline ">Login here</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php endif;?>

    <!-- Create Member -->
    <?php if(isset($_GET["member"])):?>
    <section>
        <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
            <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900">
                <img class="w-8 h-8 mr-2" src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/logo.svg" alt="logo">
                Flowbite    
            </a>
            <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0">
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
                        Create an member
                    </h1>
                    <form class="space-y-4 md:space-y-6" method="post" >
                        <div>
                            <label for="nama" class="block mb-2 text-sm font-medium text-gray-900">Nama Member</label>
                            <div class="relative">
                                <!-- Icon User -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <input type="text" name="nama" id="nama" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-10" placeholder="Nama Member" required="">
                            </div>
                        </div>

                        <div>
                            <label for="no_telepon" class="block mb-2 text-sm font-medium text-gray-900">No Telepon</label>
                            <div class="relative">
                                <!-- Icon Telepon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.003 5.884l3.197-.638a1 1 0 01.951.37l2.014 2.414a1 1 0 01-.21 1.376l-1.85 1.65a11.037 11.037 0 005.516 5.516l1.65-1.85a1 1 0 011.376-.21l2.414 2.014a1 1 0 01.37.951l-.638 3.197a1 1 0 01-.984.858 19.5 19.5 0 01-8.534-1.647 19.37 19.37 0 01-7.123-4.59A19.5 19.5 0 01.146 6.87a1 1 0 01.857-.984z" />
                                </svg>
                                <input type="text" name="no_telepon" id="no_telepon" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-10" placeholder="Nomor Telepon" required="">
                            </div>
                        </div>

                        <button name="member" type="submit" class="w-full text-white bg-blue-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center ">Create an member</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php endif;?>

    <!-- Create Kategori -->
    <?php if(isset($_GET["kategori"])):?>
    <section>
        <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
            <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900">
                <img class="w-8 h-8 mr-2" src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/logo.svg" alt="logo">
                Flowbite    
            </a>
            <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0">
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
                        Create an kategori
                    </h1>
                    <form class="space-y-4 md:space-y-6" method="post" >
                        <div>
                            <label for="nama_kategori" class="block mb-2 text-sm font-medium text-gray-900">Nama Kategori</label>
                            <div class="relative">
                                <!-- Icon User -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <input type="text" name="nama_kategori" id="nama_kategori" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-10" placeholder="Nama Kategori" required="">
                            </div>
                        </div>

                        <button name="kategori" type="submit" class="w-full text-white bg-blue-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center ">Create an kategori</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php endif;?>

    <!-- Create Product -->
    <?php if(isset($_GET["product"])):?>
    <section class="flex justify-center items-center min-h-screen">
        <div class="max-w-2xl w-full bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Create Product</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="grid gap-4 mb-6 sm:grid-cols-2">
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
                        <input type="text" id="name" name="name" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500" placeholder="Type product name" required>
                    </div>
                    </div>

                    <!-- Kode Produk -->
                    <div class="sm:col-span-2">
                    <label for="kode_produk" class="block text-sm font-medium text-gray-700">Kode Produk</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <!-- Barcode Icon -->
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h1m2 0h1m2 0h1m2 0h1m2 0h1m2 0h1M4 17h16M4 12h16" />
                        </svg>
                        </div>
                        <input type="text" id="kode_produk" name="kode_produk" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: PRD001" required>
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
                        <input type="number" id="price" name="price" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500" placeholder="$299" required>
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
                        <input type="number" id="modal_price" name="modal_price" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500" placeholder="Ex. 200" required>
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
                            <option selected>Select Category</option>
                            <?php foreach($rows as $row):?>
                            <option value="<?= $row['f_id'];?>"><?= $row["f_kategori"];?></option>
                            <?php endforeach;?>
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
                        <input type="number" id="stock" name="stock" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500" placeholder="Ex. 12" required>
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
                        <input type="date" id="expired_date" name="expired_date" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500" required>
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
                            <input id="user_avatar" name="gambar" type="file" class="hidden">
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
                        <textarea name="description" id="description" rows="4" class="mt-1 block w-full pl-10 border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-blue-500 focus:border-blue-500" placeholder="Write a product description here..."></textarea>
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
                        Create
                    </button>
                </div>
            </form>
        </div>
    </section>
    <?php endif;?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('user_avatar');
            const fileLabelText = document.getElementById('fileLabelText');
            const defaultMessage = document.getElementById('defaultMessage');
            const uploadedMessage = document.getElementById('uploadedMessage');
            const uploadedFileName = document.getElementById('uploadedFileName');

            fileInput.addEventListener('change', function() {
            if (fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                fileLabelText.textContent = fileName;
                defaultMessage.classList.add('hidden');
                uploadedMessage.classList.remove('hidden');
                uploadedFileName.textContent = fileName;
            } else {
                fileLabelText.textContent = 'Pilih Gambar';
                defaultMessage.classList.remove('hidden');
                uploadedMessage.classList.add('hidden');
                uploadedFileName.textContent = '';
            }
            });
        });

        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.setAttribute("d", "M10 3C5.455 3 1.733 6.403.625 10c1.108 3.597 4.83 7 9.375 7s8.267-3.403 9.375-7C18.267 6.403 14.545 3 10 3zm0 12c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0-2a4 4 0 110-8 4 4 0 010 8z");
            } else {
                passwordField.type = "password";
                eyeIcon.setAttribute("d", "M2.458 10c1.292-3.228 4.684-6 7.542-6s6.25 2.772 7.542 6c-1.292 3.228-4.684 6-7.542 6s-6.25-2.772-7.542-6zM10 8a2 2 0 110 4 2 2 0 010-4z");
            }
        });

    </script>
</body>
</html>