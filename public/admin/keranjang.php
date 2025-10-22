<?php
include("../../src/cookie.php");

// Jika ada input manual yang dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idProduk = htmlspecialchars($_POST["id"]);
    $qty = htmlspecialchars($_POST["qty"]);

    // Cek apakah produk dengan kode tersebut ada
    $checkQuery = "SELECT f_id FROM t_produk WHERE f_id = '$idProduk'";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult && $checkResult->num_rows > 0) {
        $product = $checkResult->fetch_assoc();
        $productId = $product['f_id'];
        $cookieKey = "cart_" . $productId;

        // Cek apakah produk sudah ada di cookie
        if (isset($_COOKIE[$cookieKey])) {
            $item = json_decode($_COOKIE[$cookieKey], true);
            $item['quantity'] += $qty; // tambah kuantitas
        } else {
            $item = array("id" => $productId, "quantity" => $qty);
        }

        // Simpan ulang ke cookie (5 menit)
        setcookie($cookieKey, urlencode(json_encode($item)), time() + 300, "/");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "<script>alert('Produk dengan kode produk tersebut tidak ditemukan.');</script>";
    }
}

// Jika produk dikirim lewat parameter GET
if (isset($_GET["id"])) {

    function tambahProduct($produkid, $quantity)
    {
        global $conn;
        $now = time();

        $stmt = $conn->prepare("SELECT f_id, f_stok, f_tanggal_expired FROM t_produk WHERE f_id = ?");
        $stmt->bind_param("s", $produkid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $idproduk = $row['f_id'];
            $stok = $row['f_stok'];
            $expiredDate = $row['f_tanggal_expired'];
            $expiredTimestamp = strtotime($expiredDate);

            // Cek stok
            if ($quantity > $stok) {
                echo "<script>
                        alert('Stok tidak mencukupi. Stok tersedia: $stok');
                        window.location.href = window.location.pathname;
                    </script>";
                return;
            }

            // Cek kadaluarsa
            if ($expiredTimestamp < $now) {
                echo "<script>
                        alert('Produk sudah kadaluarsa. Kadaluarsa pada: $expiredDate');
                        window.location.href = window.location.pathname;
                    </script>";
                return;
            }

            // Simpan ke cookie
            $cookieName = "cart_" . $idproduk;
            $cookieValue = json_encode([
                'id' => $idproduk,
                'quantity' => $quantity
            ]);
            setcookie($cookieName, $cookieValue, time() + 300, "/");

            echo "<script>
                    alert('Produk dengan ID $idproduk dan jumlah $quantity telah disimpan di cookie.');
                    window.location.href = window.location.pathname;
                </script>";
        } else {
            echo "<script>
                    alert('Produk dengan kode $produkid tidak ditemukan.');
                    window.location.href = window.location.pathname;
                </script>";
        }

        $stmt->close();
    }

    $produkid = $_GET["id"];
    $quantity = $_GET["quantity"] ?? 1;
    tambahProduct($produkid, $quantity);
}

// Ambil data cookie dengan prefix "cart_"
$cartItems = array();
foreach ($_COOKIE as $key => $value) {
    if (strpos($key, 'cart_') === 0) {
        $data = json_decode(urldecode($value), true);
        if ($data) {
            $cartItems[] = $data;
        }
    }
}

$totalP = 0;
$pointM = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="h-screen bg-blue-400">
    <section class="container flex h-screen">
        <?php include("layout/sidebar.php"); ?>
        <div class="w-3/4 mt-4 flex flex-col">
            <a href="">
                <div class="flex justify-between items-center bg-slate-300 border p-2">
                    <h1 class="text-xl">Keranjang</h1>
                    <img src="../../asset/img/profil.png" alt="" class="w-11">
                </div>
            </a>
            <div class="bg-slate-300 border p-3 my-4 flex flex-col overflow-auto">
                <div class="my-6 text-center">
                    <button onclick="openScannerModal()"
                        class="bg-blue-600 hover:bg-blue-700 transition-colors text-white px-6 py-2 rounded-lg shadow-md font-semibold">
                        📷 Scan Barcode
                    </button>

                    <button onclick="document.location.href='scanner.php'"
                        class="bg-blue-600 hover:bg-blue-700 transition-colors text-white px-6 py-2 rounded-lg shadow-md font-semibold">
                        📷 Scanner Barcode
                    </button>

                    <div id="scannerModal"
                        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden p-4">
                        <div class="relative bg-white w-full max-w-md mx-auto rounded-2xl shadow-xl overflow-hidden animate-fade-in">
                            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
                                <h2 class="text-xl font-semibold text-gray-800">🔍 Scan Barcode</h2>
                                <button onclick="closeScannerModal()"
                                    class="text-gray-500 hover:text-red-500 transition text-2xl font-bold leading-none">&times;</button>
                            </div>
                            <div class="p-6 bg-gray-100">
                                <div id="scanner"
                                    class="w-full h-64 bg-white rounded-lg border border-gray-300 shadow-inner flex items-center justify-center text-gray-400">
                                    Menunggu kamera...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="get" class="flex gap-4 items-center mb-4">
                    <input type="text" name="id" placeholder="Masukkan Kode Produk / Barcode"
                        class="p-2 rounded border border-gray-300 w-full bg-white shadow" />
                    <input type="number" name="quantity" placeholder="Qty" value="1" min="1"
                        class="p-2 rounded border border-gray-300 w-24 bg-white shadow" />

                    <button name="input" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                        ➕ Tambah
                    </button>
                </form>

                <table class="min-w-full bg-white rounded-lg overflow-hidden">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($cartItems)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-gray-600">Tidak ada data keranjang.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cartItems as $produk): ?>
                                <?php
                                $productId = $produk['id'];
                                $quantity = $produk['quantity'];
                                $sql = "SELECT f_nama_produk, f_harga_jual FROM t_produk WHERE f_id = '$productId'";
                                $result = $conn->query($sql);
                                if ($result && $result->num_rows > 0) {
                                    $prod = $result->fetch_assoc();
                                    $nama = $prod['f_nama_produk'];
                                    $harga = $prod['f_harga_jual'];
                                } else {
                                    $nama = "Unknown";
                                    $harga = 0;
                                }
                                $totalHarga = $harga * $quantity;
                                $totalP += $totalHarga;
                                ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($productId); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($nama); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp. <?= number_format($harga); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($quantity); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp. <?= number_format($totalHarga); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <a href="function/delete.php?key=<?= urlencode("cart_" . $productId); ?>" class="text-red-600 hover:text-red-800">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="6" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Items: <?= count($cartItems); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <div class="bg-white p-4 rounded-lg shadow-md mt-4">
                    <form action="function/invoice.php" method="post">
                        <?php foreach ($cartItems as $produk): ?>
                            <input type="hidden" name="produk[]" value="<?= htmlspecialchars($produk["id"]); ?>">
                            <input type="hidden" name="quantity[]" value="<?= htmlspecialchars($produk["quantity"]); ?>">
                        <?php endforeach; ?>

                        <!-- 🔹 Input Member -->
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                            <div class="w-full md:w-1/2 flex items-center gap-2">
                                <div class="w-1/3">
                                    <label for="id_member" class="block text-sm font-medium text-gray-700">ID Member</label>
                                    <input type="text" id="memberId" name="memberId" readonly
                                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 bg-gray-100 text-gray-700 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div class="w-2/3">
                                    <label for="noTelp" class="block text-sm font-medium text-gray-700">No Telepon Member</label>
                                    <input type="text" id="noTelp" name="noTelp" placeholder="Masukkan No Telepon"
                                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
                                        oninput="cekMember(this.value)">
                                </div>
                            </div>

                            <div class="w-full md:w-1/2">
                                <label for="totalHarga" class="block text-sm font-medium text-gray-700">Total Harga</label>
                                <input type="text" id="totalHarga" name="totalHarga" required readonly
                                    value="<?= $totalP ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- 🔹 Nama & Point Member -->
                        <div id="memberPointSection" style="display:none;" class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="memberName" class="block text-sm font-medium text-gray-700">Nama Member</label>
                                    <input type="text" id="memberName" name="memberName" readonly
                                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 bg-gray-100 text-gray-700 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label for="memberPoints" class="block text-sm font-medium text-gray-700">Poin Tersedia</label>
                                    <input type="text" id="memberPoints" name="memberPoints" readonly
                                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 bg-gray-100 text-gray-700 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <!-- 🔹 Input Gunakan Poin -->
                                <div>
                                    <label for="redeemPoints" class="block text-sm font-medium text-gray-700">Gunakan Poin</label>
                                    <input type="number" id="redeemPoints" name="redeemPoints" min="0"
                                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
                                        oninput="cekPoinDigunakan()">
                                </div>
                            </div>
                        </div>

                        <!-- 🔹 Bayar -->
                        <div id="bayarSection" style="display:none;">
                            <h2 class="text-lg font-semibold mb-2">Bayar</h2>
                            <input type="text" id="bayar" name="bayar"
                                class="block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="mt-4 text-right">
                            <button name="checkout" type="submit"
                                class="bg-green-600 hover:bg-green-700 text-black font-medium py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-green-300"
                                <?= count($cartItems) <= 0 ? 'disabled' : ''; ?>>
                                Check Out
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php include("layout/footer.php"); ?>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        function cekPoinDigunakan() {
            const tersedia = parseInt(document.getElementById('memberPoints').value || 0);
            const input = document.getElementById('usePoints');
            let nilai = parseInt(input.value || 0);

            if (nilai > tersedia) {
                alert('❌ Poin yang digunakan tidak boleh lebih dari poin tersedia!');
                input.value = tersedia;
            } else if (nilai < 0) {
                input.value = 0;
            }
        }

        function cekMember(noTelp) {
            if (noTelp.length < 10) return;
            fetch('function/get_member.php?noTelp=' + encodeURIComponent(noTelp))
                .then(res => res.json())
                .then(data => {
                    const section = document.getElementById('memberPointSection');
                    const bayarSection = document.getElementById('bayarSection');
                    if (data && data.f_id) {
                        // tampilkan data member
                        section.style.display = 'block';
                        bayarSection.style.display = 'block';
                        document.getElementById('memberId').value = data.f_id;
                        document.getElementById('memberName').value = data.f_nama;
                        document.getElementById('memberPoints').value = data.f_point;
                    } else {
                        // sembunyikan kalau gak ada
                        section.style.display = 'none';
                        bayarSection.style.display = 'none';
                        document.getElementById('memberId').value = '';
                        document.getElementById('memberName').value = '';
                        document.getElementById('memberPoints').value = '';
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan saat mengecek member');
                });
        }

        function openScannerModal() {
            document.getElementById("scannerModal").classList.remove("hidden");
            startScanner();
        }

        function closeScannerModal() {
            document.getElementById("scannerModal").classList.add("hidden");
        }

        let isScanned = false;

        function startScanner() {
            const html5QrCode = new Html5Qrcode("scanner");
            html5QrCode.start({
                    facingMode: "environment"
                }, {
                    fps: 10,
                    qrbox: 250
                },
                (decodedText) => {
                    if (!isScanned) {
                        isScanned = true;
                        let quantity = prompt("Masukkan jumlah barang yang ingin dibeli:", "1");
                        if (quantity !== null) {
                            quantity = parseInt(quantity);
                            if (isNaN(quantity) || quantity <= 0) {
                                alert("Jumlah tidak valid.");
                            } else {
                                document.location.href = "keranjang.php?id=" + decodedText + "&quantity=" + quantity;
                            }
                        }
                        closeScannerModal();
                    }
                },
                (errorMessage) => {
                    console.log(errorMessage);
                }
            );
        }
    </script>
</body>

</html>