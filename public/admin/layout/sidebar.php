
<div class="w-1/3 h-fit ml-10 mr-9 my-4 bg-slate-300 rounded-md border">
    <div class="akun flex flex-col items-center py-4">
        <!-- Foto Profil -->
        <img src="<?= $user['f_gambar'];?>" alt="" class="w-30 rounded-full">
        <p class="mt-2 font-semibold text-gray-700 text-xl"><?= $user["f_username"];?></p>
    </div>
    
    <hr class="bg-black w-full">

    <!-- Menu Navigasi -->
    <div class="flex justify-center w-full mt-5">
        <ul class="flex flex-col items-center gap-3 mb-5 text-gray-700">
            
            <!-- Home -->
            <a href="home.php" class="flex items-center gap-2 bg-blue-400 border w-32 p-2 rounded-md hover:bg-blue-500 cursor-pointer">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M9 21V9h6v12"></path>
                </svg>
                <span class="text-white">Home</span>
            </a>

            <!-- Admin -->
            <a href="admin.php" class="flex items-center gap-2 bg-blue-400 border w-32 p-2 rounded-md hover:bg-blue-500 cursor-pointer">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zM12 14v7"></path>
                </svg>
                <span class="text-white">Admin</span>
            </a>

            <!-- Member -->
            <a href="member.php" class="flex items-center gap-2 bg-blue-400 border w-32 p-2 rounded-md hover:bg-blue-500 cursor-pointer">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-8 0v2m8-10a4 4 0 10-8 0 4 4 0 008 0z"></path>
                </svg>
                <span class="text-white">Member</span>
            </a>

            <hr class="bg-black w-full">

            <!-- Product -->
            <a href="product.php" class="flex items-center gap-2 bg-blue-400 border w-32 p-2 rounded-md hover:bg-blue-500 cursor-pointer">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4m16 0a8 8 0 11-16 0 8 8 0 0116 0z"></path>
                </svg>
                <span class="text-white">Product</span>
            </a>

            <!-- Keranjang -->
            <a href="keranjang.php" class="flex items-center gap-2 bg-blue-400 border w-32 p-2 rounded-md hover:bg-blue-500 cursor-pointer">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18l-2 13H5L3 3zm5 16a2 2 0 104 0M14 19a2 2 0 104 0"></path>
                </svg>
                <span class="text-white">Keranjang</span>
            </a>

            <!-- Laporan -->
            <a href="laporan.php" class="flex items-center gap-2 bg-blue-400 border w-32 p-2 rounded-md hover:bg-blue-500 cursor-pointer">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6m3 6v-6m3 6v-6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <span class="text-white">Laporan</span>
            </a>

            <hr class="bg-black w-full">

            <!-- Logout -->
            <a href="function/logout.php" class="flex items-center gap-2 bg-red-500 border w-32 p-2 rounded-md hover:bg-red-700 cursor-pointer">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7"></path>
                </svg>
                <span class="text-white">Log Out</span>
            </a>

        </ul>
    </div>
</div>
