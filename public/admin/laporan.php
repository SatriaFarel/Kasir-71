<?php
include("../../src/cookie.php");

// Ambil bulan dan tahun saat ini
$currentMonth = date('m');
$currentYear = date('Y');

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'bulan';
$metric = isset($_GET['metric']) ? $_GET['metric'] : 'keuntungan';
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : $currentMonth;
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : $currentYear;

// ================== QUERY UNTUK CHART ==================
if ($filter == 'minggu') {
    $query = "SELECT 
                YEAR(f_tanggal_pembelian) AS tahun,
                WEEK(f_tanggal_pembelian, 1) - WEEK(DATE_SUB(f_tanggal_pembelian, INTERVAL DAYOFMONTH(f_tanggal_pembelian)-1 DAY), 1) + 1 AS minggu_ke,
                DATE_FORMAT(f_tanggal_pembelian, '%M %Y') AS bulan_tahun,
                SUM(f_total_keuntungan) AS total_keuntungan,
                SUM(d.f_quantity) AS total_produk
              FROM t_transaksi 
              INNER JOIN t_detail_transaksi d ON t_transaksi.f_id_transaksi = d.f_id_transaksi
              WHERE MONTH(f_tanggal_pembelian) = '$bulan' AND YEAR(f_tanggal_pembelian) = '$tahun'
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
              WHERE YEAR(f_tanggal_pembelian) = '$tahun'
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

if(mysqli_num_rows($result) > 0){
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
        } else {
            $values[] = $row['total_produk'];
        }
    }
}

$chartData = ["labels" => $labels, "values" => $values];

if (isset($_GET['ajax'])) {
    echo json_encode($chartData);
    exit();
}

// ================== QUERY UNTUK TABEL ==================
$data = mysqli_query($conn, "
SELECT 
t.f_id_transaksi, 
t.f_tanggal_pembelian, 
t.f_total_harga, 
t.f_total_keuntungan,
GROUP_CONCAT(p.f_nama_produk ORDER BY d.f_id SEPARATOR ', ') AS produk_nama,
GROUP_CONCAT(d.f_quantity ORDER BY d.f_id SEPARATOR ', ') AS jumlah
FROM t_transaksi t
INNER JOIN t_detail_transaksi d ON t.f_id_transaksi = d.f_id_transaksi
INNER JOIN t_produk p ON d.f_id_produk = p.f_id
WHERE MONTH(t.f_tanggal_pembelian) = '$bulan' AND YEAR(t.f_tanggal_pembelian) = '$tahun'
GROUP BY t.f_id_transaksi
ORDER BY t.f_tanggal_pembelian DESC
");

// ================== RINGKASAN DATA ==================
$summaryQuery = mysqli_query($conn, "
SELECT 
    SUM(f_total_keuntungan) AS total_keuntungan,
    COUNT(DISTINCT t.f_id_transaksi) AS total_transaksi,
    SUM(d.f_quantity) AS total_produk
FROM t_transaksi t
INNER JOIN t_detail_transaksi d ON t.f_id_transaksi = d.f_id_transaksi
WHERE MONTH(t.f_tanggal_pembelian) = '$bulan' AND YEAR(t.f_tanggal_pembelian) = '$tahun'
");
$summary = mysqli_num_rows($summaryQuery) > 0 ? mysqli_fetch_assoc($summaryQuery) : [
    'total_keuntungan' => 0,
    'total_transaksi' => 0,
    'total_produk' => 0
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="h-screen bg-blue-400">
    <section class="container flex h-screen">
        <?php include("layout/sidebar.php"); ?>
        <div class="w-4/5 mt-4 flex flex-col">
            <div class="flex justify-between items-center bg-slate-300 border p-2">
                <h1 class="text-xl font-semibold">Laporan Transaksi</h1>
                <img src="../../asset/img/profil.png" alt="" class="w-11">
            </div>

            <!-- Tombol navigasi antar page -->
            <div class="flex justify-center gap-3 mt-4">
                <button id="btnPage1" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Tabel Transaksi</button>
                <button id="btnPage2" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Grafik Laporan</button>
            </div>

            <!-- ============ PAGE 1: TABEL TRANSAKSI ============ -->
            <div id="page1" class="bg-slate-300 border p-3 mt-5 overflow-scroll">
                <!-- =================== RINGKASAN =================== -->
                <div class="grid grid-cols-3 gap-4 my-6 text-center">
                    <div class="bg-green-500 text-white rounded-full w-40 h-40 flex flex-col items-center justify-center mx-auto shadow-lg">
                        <h2 class="text-lg font-semibold">Keuntungan</h2>
                        <p class="text-xl font-bold">Rp <?= number_format($summary['total_keuntungan'] ?? 0, 0, ',', '.'); ?></p>

                    </div>
                    <div class="bg-blue-500 text-white rounded-full w-40 h-40 flex flex-col items-center justify-center mx-auto shadow-lg">
                        <h2 class="text-lg font-semibold">Transaksi</h2>
                        <p class="text-xl font-bold"><?= $summary['total_transaksi']; ?></p>
                    </div>
                    <div class="bg-yellow-500 text-white rounded-full w-40 h-40 flex flex-col items-center justify-center mx-auto shadow-lg">
                        <h2 class="text-lg font-semibold">Produk Terjual</h2>
                        <p class="text-xl font-bold"><?= $summary['total_produk']; ?></p>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-3">
                    <form method="GET" class="flex gap-2">
                        <select name="bulan" class="p-2 border rounded">
                            <?php
                            for ($i = 1; $i <= 12; $i++) {
                                $selected = ($i == $bulan) ? 'selected' : '';
                                echo "<option value='$i' $selected>" . date('F', mktime(0, 0, 0, $i, 1)) . "</option>";
                            }
                            ?>
                        </select>
                        <select name="tahun" class="p-2 border rounded">
                            <?php
                            for ($y = date('Y') - 3; $y <= date('Y'); $y++) {
                                $selected = ($y == $tahun) ? 'selected' : '';
                                echo "<option value='$y' $selected>$y</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Filter</button>
                    </form>
                    <button onclick="printTablePDF()" class="bg-red-500 text-white px-4 py-2 rounded">Print PDF</button>
                </div>

                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">ID Transaksi</th>
                                <th class="px-6 py-3">Produk</th>
                                <th class="px-6 py-3">Total Qty</th>
                                <th class="px-6 py-3">Total Harga</th>
                                <th class="px-6 py-3">Keuntungan</th>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($data) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($data)): ?>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900"><?= $row["f_id_transaksi"]; ?></td>
                                        <td class="px-6 py-4"><?= $row["produk_nama"]; ?></td>
                                        <td class="px-6 py-4"><?= $row["jumlah"]; ?></td>
                                        <td class="px-6 py-4"><?= number_format($row["f_total_harga"], 0, ',', '.'); ?></td>
                                        <td class="px-6 py-4"><?= number_format($row["f_total_keuntungan"], 0, ',', '.'); ?></td>
                                        <td class="px-6 py-4"><?= $row["f_tanggal_pembelian"]; ?></td>
                                        <td class="px-3 py-2">
                                            <a href="detail_transaksi.php?id=<?= $row['f_id_transaksi']; ?>"
                                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr class="bg-white border-b">
                                    <td colspan="7" class="text-center py-4 text-gray-500">Tidak ada data transaksi</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============ PAGE 2: GRAFIK LAPORAN ============ -->
            <div id="page2" class="bg-slate-300 border p-3 mt-5 overflow-scroll hidden">
                <!-- =================== RINGKASAN =================== -->
                <div class="grid grid-cols-3 gap-4 my-6 text-center">
                    <div class="bg-green-500 text-white rounded-full w-40 h-40 flex flex-col items-center justify-center mx-auto shadow-lg">
                        <h2 class="text-lg font-semibold">Keuntungan</h2>
                        <p class="text-xl font-bold">Rp <?= number_format($summary['total_keuntungan']?? 0, 0, ',', '.'); ?></p>
                    </div>
                    <div class="bg-blue-500 text-white rounded-full w-40 h-40 flex flex-col items-center justify-center mx-auto shadow-lg">
                        <h2 class="text-lg font-semibold">Transaksi</h2>
                        <p class="text-xl font-bold"><?= $summary['total_transaksi']; ?></p>
                    </div>
                    <div class="bg-yellow-500 text-white rounded-full w-40 h-40 flex flex-col items-center justify-center mx-auto shadow-lg">
                        <h2 class="text-lg font-semibold">Produk Terjual</h2>
                        <p class="text-xl font-bold"><?= $summary['total_produk']; ?></p>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <div>
                        <label for="filter">Filter:</label>
                        <select id="filter" class="p-2 border rounded" onchange="updateChart()">
                            <option value="tahun">Per Tahun</option>
                            <option value="bulan" selected>Per Bulan</option>
                            <option value="minggu">Per Minggu</option>
                        </select>
                    </div>
                    <div>
                        <label for="metric">Metric:</label>
                        <select id="metric" class="p-2 border rounded" onchange="updateChart()">
                            <option value="keuntungan">Total Keuntungan</option>
                            <option value="penjualan">Jumlah Penjualan</option>
                        </select>
                    </div>
                </div>
                <canvas id="myChart" class="mt-5 bg-white p-4 rounded shadow"></canvas>
                <div class="mt-4">
                    <button onclick="printChartPDF()" class="bg-green-500 text-white px-4 py-2 rounded">Print PDF</button>
                </div>
            </div>
        </div>
    </section>
    <?php include("layout/footer.php"); ?>
    <script>
        let chart;
        const ctx = document.getElementById('myChart').getContext('2d');

        function fetchData(filter = 'bulan', metric = 'keuntungan') {
            fetch(`?ajax=1&filter=${filter}&metric=${metric}`)
                .then(res => res.json())
                .then(data => {
                    if (chart) chart.destroy();
                    chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels.length > 0 ? data.labels : ['Tidak ada data'],
                            datasets: [{
                                label: metric === 'keuntungan' ? 'Total Keuntungan' : 'Jumlah Penjualan',
                                data: data.values.length > 0 ? data.values : [0],
                                backgroundColor: '#36A2EB',
                                borderColor: '#2C6CBF',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
        }
        fetchData();

        function updateChart() {
            const filter = document.getElementById('filter').value;
            const metric = document.getElementById('metric').value === 'penjualan' ? 'penjualan' : 'keuntungan';
            fetchData(filter, metric);
        }

        // Print PDF Chart
        function printChartPDF() {
            const chartCanvas = document.getElementById('myChart');
            const chartImage = chartCanvas.toDataURL('image/png');
            fetch('function/printLaporan.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        chart: chartImage
                    }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'laporan-chart.pdf';
                    a.click();
                });
        }

        // Print PDF Table
        function printTablePDF() {
            window.open('function/printLaporan.php?bulan=<?= $bulan; ?>&tahun=<?= $tahun; ?>', '_blank');
        }

        // Navigasi antar page
        document.getElementById('btnPage1').addEventListener('click', () => {
            document.getElementById('page1').classList.remove('hidden');
            document.getElementById('page2').classList.add('hidden');
        });
        document.getElementById('btnPage2').addEventListener('click', () => {
            document.getElementById('page2').classList.remove('hidden');
            document.getElementById('page1').classList.add('hidden');
        });
    </script>
</body>

</html>
