<?php
    include("../../src/config.php");
    include("../../src/cookie.php");
    if (!isset($_SESSION["id"])) {
        header("Location: function/logout.php");
    } elseif (isset($_SESSION["id"]) || isset($_COOKIE["id"])) {
        $id = $_SESSION["id"];
    }

    // Pagination setup
    $limit = 3; // Jumlah data per halaman
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Hitung total data
    $countQuery = "SELECT COUNT(*) AS total FROM t_admin";
    $countResult = mysqli_query($conn, $countQuery);
    if ($countResult) {
        $totalData = mysqli_fetch_assoc($countResult)['total'];
    } else {
        die("Error counting data: " . mysqli_error($conn));
    }

    // Ambil data dengan limit dan offset
    $query = "SELECT * FROM t_admin LIMIT $limit OFFSET $offset";
    $data = mysqli_query($conn, $query);
    if (!$data) {
        die("Error fetching data: " . mysqli_error($conn));
    }

    // Hitung total halaman
    $totalPages = ceil($totalData / $limit);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="h-screen bg-blue-400">
    <section class="container flex h-screen">
        <?php include("layout/sidebar.php");?>
        <div class=" w-3/4 mt-4 flex flex-col ">
            <a href="">
                <div class="flex justify-between items-center bg-slate-300 border p-2">
                <h1 class="text-xl">Admin</h1>
                    <div class="flex items-center gap-3">
                        <!-- Tombol Tambah Data -->
                        <a href="function/create.php?admin=1" class="flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Tambah Data
                        </a>
                        
                        <img src="../../asset/img/profil.png" alt="" class="w-11">
                    </div>
                </div>
            </a>
            <div class="bg-slate-300 border p-3 my-4 h-screen gap-3 flex flex-wrap justify-around overflow-scroll overscroll-x-none">
                <?php foreach($data as $row):?>
                    <div class="max-w-xs bg-white h-64 p-4 border border-gray-200 rounded-lg shadow-sm">
                        <div class="flex flex-col items-center pb-5">
                            <!-- Foto Profil -->
                            <img class="w-24 h-24 mb-3 rounded-full shadow-lg" src="<?=$row['f_gambar'];?>" alt="Profile Image"/>

                            <!-- Nama & Email -->
                            <h5 class="mb-1 text-xl font-medium text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A9 9 0 1112 21"></path>
                                </svg>
                                <?= $row["f_username"]; ?>
                            </h5>

                            <span class="text-sm text-gray-500 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 12H8m0 0l4-4m-4 4l4 4"></path>
                                </svg>
                                <?= $row["f_email"]; ?>
                            </span>

                            <!-- Tombol Aksi -->
                            <div class="flex mt-4">
                                <a href="function/update.php?IDA=<?=$row['f_id'];?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2 2 0 112.828 2.828L11 14H7v-4z"></path>
                                    </svg>
                                    Update
                                </a>


                                <?php 
                                $isSameUser = $row['f_id'] == $id;
                                ?>

                                <a href="function/delete.php?IDA=<?= $row['f_id']; ?>" 
                                class="py-2 px-4 ms-2 text-sm font-medium text-white focus:outline-none bg-red-500 rounded-lg border border-gray-200 hover:bg-red-700 flex items-center <?= $isSameUser ? 'opacity-50 cursor-not-allowed' : '' ?>" 
                                <?= $isSameUser ? 'onclick="return false;"' : '' ?>>
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Delete
                                </a>

                            </div>
                        </div>
                    </div>

                <?php endforeach;?>
            </div>
            <?php include("layout/paggination.php");?>
        </div>
    </section>
     <?php include("layout/footer.php");?>
</body>
</html>