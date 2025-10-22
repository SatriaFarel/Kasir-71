<!-- Pagination -->
<nav class="my-5">
    <ul class="flex items-center justify-center space-x-1 text-sm">
        <!-- Tombol Previous -->
        <?php if ($page > 1): ?>
            <li>
                <a href="?page=<?= $page - 1 ?>" class="flex items-center justify-center px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-blue-300 hover:text-gray-700">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4"/>
                    </svg>
                </a>
            </li>
        <?php endif; ?>

        <!-- Nomor Halaman -->
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li>
                <a href="?page=<?=$i?>" class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg
                    <?= $i === $page ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-blue-300 hover:text-gray-700' ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>

        <!-- Tombol Next -->
        <?php if ($page < $totalPages): ?>
            <li>
                <a href="?page=<?= $page + 1 ?>" class="flex items-center justify-center px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-blue-300 hover:text-gray-700">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                    </svg>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
