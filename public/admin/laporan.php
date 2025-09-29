<?php
    include("../../src/cookie.php");

    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'bulan';
    $metric = isset($_GET['metric']) ? $_GET['metric'] : 'keuntungan';

    if ($filter == 'minggu') {
        $query = "SELECT 
                    YEAR(f_tanggal_pembelian) AS tahun,
                    WEEK(f_tanggal_pembelian, 1) - WEEK(DATE_SUB(f_tanggal_pembelian, INTERVAL DAYOFMONTH(f_tanggal_pembelian)-1 DAY), 1) + 1 AS minggu_ke,
                    DATE_FORMAT(f_tanggal_pembelian, '%M %Y') AS bulan_tahun,
                    SUM(f_total_keuntungan) AS total_keuntungan,
                    SUM(d.f_quantity) AS total_produk
                  FROM t_transaksi 
                  INNER JOIN t_detail_transaksi d ON t_transaksi.f_id_transaksi = d.f_id_transaksi
                  GROUP BY tahun, bulan_tahun, minggu_ke 
                  ORDER BY tahun ASC, bulan_tahun ASC, minggu_ke ASC";
    } elseif ($filter == 'bulan') {
        $query = "SELECT 
                    DATE_FORMAT(f_tanggal_pembelian, '%Y-%m') AS periode, 
                    DATE_FORMAT(f_tanggal_pembelian, '%M %Y') AS periode_nama, 
                    SUM(f_total_keuntungan) AS total_keuntungan,
                    SUM(d.f_quantity) AS total_produk
                  FROM t_transaksi 
                  INNER JOIN t_detail_transaksi d ON t_transaksi.f_id_transaksi = d.f_id_transaksi
                  GROUP BY periode, periode_nama 
                  ORDER BY periode ASC";
    } else {
        $query = "SELECT 
                    YEAR(f_tanggal_pembelian) AS periode, 
                    SUM(f_total_keuntungan) AS total_keuntungan,
                    SUM(d.f_quantity) AS total_produk
                  FROM t_transaksi 
                  INNER JOIN t_detail_transaksi d ON t_transaksi.f_id_transaksi = d.f_id_transaksi
                  GROUP BY periode 
                  ORDER BY periode ASC";
    }

    $result = mysqli_query($conn, $query);
    $labels = [];
    $values = [];

    while ($row = mysqli_fetch_assoc($result)) {
        if ($filter == 'minggu') {
            $labels[] = "Minggu " . $row['minggu_ke'] . " - " . $row['bulan_tahun'];
        } elseif ($filter == 'bulan') {
            $labels[] = $row['periode_nama'];
        } else {
            $labels[] = $row['periode'];
        }
        
        if ($metric == 'keuntungan') {
            $values[] = $row['total_keuntungan'];
        } else { // penjualan -> jumlah produk terjual
            $values[] = $row['total_produk'];
        }
    }

    $chartData = ["labels" => $labels, "values" => $values];

    if (isset($_GET['ajax'])) {
        echo json_encode($chartData);
        exit();
    }

    $data = mysqli_query($conn, "SELECT 
    t.f_id_transaksi, 
    t.f_tanggal_pembelian, 
    t.f_total_harga, 
    t.f_total_keuntungan,
    GROUP_CONCAT(p.f_nama_produk ORDER BY d.f_id SEPARATOR ', ') AS produk_nama,
    GROUP_CONCAT(d.f_quantity ORDER BY d.f_id SEPARATOR ', ') AS jumlah
FROM t_transaksi t
INNER JOIN t_detail_transaksi d ON t.f_id_transaksi = d.f_id_transaksi
INNER JOIN t_produk p ON d.f_id_produk = p.f_id
GROUP BY t.f_id_transaksi;
");


if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Query untuk mengambil detail transaksi berdasarkan ID
    $query = "SELECT 
                t.f_id_transaksi, 
                t.f_tanggal_pembelian, 
                t.f_total_harga, 
                t.f_total_keuntungan,
                GROUP_CONCAT(p.f_nama_produk ORDER BY d.f_id SEPARATOR ', ') AS produk_nama,
                GROUP_CONCAT(d.f_quantity ORDER BY d.f_id SEPARATOR ', ') AS jumlah
              FROM t_transaksi t
              INNER JOIN t_detail_transaksi d ON t.f_id_transaksi = d.f_id_transaksi
              INNER JOIN t_produk p ON d.f_id_produk = p.f_id
              WHERE t.f_id_transaksi = '$id'
              GROUP BY t.f_id_transaksi";

    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    // Mengembalikan data dalam format JSON
    echo json_encode($row);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="h-screen bg-blue-400">
    <section class="container flex h-screen">
        <?php include("layout/sidebar.php");?>
        <div class=" w-4/5 mt-4 flex flex-col">
            <a href="">
                <div class="flex justify-between items-center bg-slate-300 border p-2">
                    <h1 class="text-xl">Laporan</h1>
                    <img src="../../asset/img/profil.png" alt="" class="w-11">
                </div>
            </a>
            <div class="bg-slate-300 border p-3 mt-5 overflow-scroll">
                <div class="card">
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">ID Transaksi</th>
                                    <th scope="col" class="px-6 py-3">Produk</th>
                                    <th scope="col" class="px-6 py-3">Total Qty</th>
                                    <th scope="col" class="px-6 py-3">Total Harga</th>
                                    <th scope="col" class="px-6 py-3">Keuntungan</th>
                                    <th scope="col" class="px-6 py-3">Tanggal Pembelian</th>
                                    <th  scope="col" class="px-6 py-3">Actionm</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($data)): ?>
                                <tr class="bg-white border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                        <?= $row["f_id_transaksi"]; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?= $row["produk_nama"]; ?> 
                                    </td>
                                    <td class="px-6 py-4">
                                        <?= $row["jumlah"]; ?> 
                                    </td>
                                    <td class="px-6 py-4">
                                        <?= number_format($row["f_total_harga"], 0, ',', '.'); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?= number_format($row["f_total_keuntungan"], 0, ',', '.'); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?= $row["f_tanggal_pembelian"]; ?>
                                    </td>
                                    <td class="px-3 py-2">
                                        <a href="detail_transaksi.php?id=<?= $row['f_id_transaksi']; ?>"
                                        class="whitespace-normal break-words px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                        Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <!-- Modal for Detail Transaksi -->
                        <div id="transactionModal" class="fixed inset-0 flex items-center justify-center bg-gray-600 bg-opacity-50 hidden">
                            <div class="bg-white p-6 rounded-lg shadow-lg w-3/4">
                                <h2 class="text-xl font-semibold mb-4">Detail Transaksi</h2>
                                <div id="transactionDetails"></div>
                                <button onclick="closeModal()" class="mt-4 bg-red-500 text-white px-4 py-2 rounded">Close</button>
                            </div>
                        </div>

                        <!-- Pagination Controls -->
                        <div id="pagination" class="flex justify-center items-center my-4 gap-2">
                            <button
                                id="prevPage"
                                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-300"
                            >
                                Prev
                            </button>
                            <span id="pageInfo" class="text-blue-700 font-medium">
                                Halaman 1
                            </span>
                            <button
                                id="nextPage"
                                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-300"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="flex justify-between items-center mt-5">
                        <label for="filter" >Filter:</label>
                        <select id="filter" class="p-2 border rounded" onchange="updateChart()">
                            <option value="tahun">Per Tahun</option>
                            <option value="bulan" selected>Per Bulan</option>
                            <option value="minggu">Per Minggu</option>
                        </select>
                        <label for="metric">Metric:</label>
                        <select id="metric" class="p-2 border rounded" onchange="updateChart()">
                            <option value="keuntungan">Total Keuntungan</option>
                            <option value="penjualan">Jumlah Penjualan</option>
                        </select>
                    </div>
                    <canvas id="myChart" class="mt-5 bg-white p-4 rounded shadow"></canvas>
                </div>
                <div class="mt-4">
                    <button onclick="printPDF()" class="bg-green-500 text-white px-4 py-2 rounded">Print PDF</button>
                </div>
            </div>
        </div>
    </section>
    <?php include("layout/footer.php");?>
    <script>
        let chart;
        const ctx = document.getElementById('myChart').getContext('2d');
        function fetchData(filter = 'bulan', metric = 'keuntungan') {
            fetch(`?ajax=1&filter=${filter}&metric=${metric}`)
                .then(response => response.json())
                .then(data => {
                    if (chart) chart.destroy();
                    chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: metric === 'keuntungan' ? 'Total Keuntungan' : 'Jumlah Penjualan',
                                data: data.values,
                                backgroundColor: '#36A2EB',
                                borderColor: '#2C6CBF',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                });
        }
        function updateChart() {
            const filter = document.getElementById('filter').value;
            const metric = document.getElementById('metric').value;
            fetchData(filter, metric);
        }
        fetchData();

        function printPDF() {
            const chartCanvas = document.getElementById('myChart');
            const chartImage = chartCanvas.toDataURL('image/png'); // ambil chart dalam base64
            

            // Kirim AJAX ke server
            fetch('function/printLaporan.php', {
                method: 'POST',
                body: JSON.stringify({
                    chart: chartImage
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'laporan-transaksi.pdf';
                a.click();
            });
        }

        // ============================================
        // Pagination untuk tabel transaksi
        // ============================================
        const rowsPerPage = 10;
        let currentPage = 1;
        const tbody = document.querySelector('table tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const totalPages = Math.ceil(rows.length / rowsPerPage);

        function showPage(page) {
            currentPage = page;
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            rows.forEach((row, idx) => {
                row.style.display = (idx >= start && idx < end) ? '' : 'none';
            });
            document.getElementById('pageInfo').textContent = `Halaman ${currentPage}`;
            document.getElementById('prevPage').disabled = currentPage === 1;
            document.getElementById('nextPage').disabled = currentPage === totalPages;
        }

        document.getElementById('prevPage').addEventListener('click', () => {
            if (currentPage > 1) showPage(currentPage - 1);
        });
        document.getElementById('nextPage').addEventListener('click', () => {
            if (currentPage < totalPages) showPage(currentPage + 1);
        });

        // Tampilkan halaman pertama saat load
        showPage(1);


        // Fungsi untuk membuka modal dan menampilkan detail transaksi
    function openTransactionModal(id) {
        fetch(`?id=${id}`)
            .then(response => response.json())
            .then(data => {
                // Menampilkan data transaksi di modal
                const transactionDetails = `
                    <p><strong>ID Transaksi:</strong> ${data.f_id_transaksi}</p>
                    <p><strong>Tanggal Pembelian:</strong> ${data.f_tanggal_pembelian}</p>
                    <p><strong>Total Harga:</strong> ${number_format(data.f_total_harga)}</p>
                    <p><strong>Total Keuntungan:</strong> ${number_format(data.f_total_keuntungan)}</p>
                    <p><strong>Produk:</strong> ${data.produk_nama}</p>
                    <p><strong>Jumlah Produk:</strong> ${data.jumlah}</p>
                    <p><strong>Invoice:</strong> <a href="path_to_invoice/${data.f_id_transaksi}" target="_blank" class="text-blue-500">Lihat Invoice</a></p>
                `;
                document.getElementById('transactionDetails').innerHTML = transactionDetails;
                document.getElementById('transactionModal').classList.remove('hidden');
            });
    }

    // Fungsi untuk menutup modal
    function closeModal() {
        document.getElementById('transactionModal').classList.add('hidden');
    }

    // Event listener untuk tombol Detail Transaksi
    document.querySelectorAll('.btn-detail').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id; // Ambil ID dari data-id
            openTransactionModal(id);
        });
    });

    </script>
</body>
</html>
