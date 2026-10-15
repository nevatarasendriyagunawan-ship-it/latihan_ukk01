<?php
session_start();
include 'koneksi.php';

// Inisialisasi Keranjang
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Flag untuk membuka modal keranjang secara otomatis setelah aksi di keranjang
$open_cart_modal = false;

// 1. TAMBAH KE KERANJANG (DARI KATALOG ATAU DARI DALAM MODAL)
if (isset($_POST['add_to_cart'])) {
    $id_produk = $_POST['id_produk'];
    $jumlah    = (int)$_POST['jumlah'];
    $from_modal = isset($_POST['from_modal']) ? true : false;

    $res = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk='$id_produk'");
    $prod = mysqli_fetch_assoc($res);

    if ($prod && $jumlah > 0) {
        $stok_saat_ini = isset($_SESSION['cart'][$id_produk]) ? $_SESSION['cart'][$id_produk] : 0;
        if (($stok_saat_ini + $jumlah) <= $prod['stok']) {
            if (isset($_SESSION['cart'][$id_produk])) {
                $_SESSION['cart'][$id_produk] += $jumlah;
            } else {
                $_SESSION['cart'][$id_produk] = $jumlah;
            }
            $msg_success = "Produk '" . htmlspecialchars($prod['nama_produk']) . "' berhasil ditambahkan ke keranjang!";
        } else {
            $msg_error = "Jumlah total melebihi sisa stok (" . $prod['stok'] . ")!";
        }
    } else {
        $msg_error = "Pilih produk dan masukkan jumlah yang valid!";
    }

    if ($from_modal) {
        $open_cart_modal = true;
    }
}

// 2. UBAH JUMLAH (+ / -) LANGSUNG DI KERANJANG
if (isset($_GET['action_cart'])) {
    $id_act = $_GET['id_produk'];
    $act    = $_GET['action_cart'];

    if (isset($_SESSION['cart'][$id_act])) {
        $res = mysqli_query($conn, "SELECT stok FROM produk WHERE id_produk='$id_act'");
        $p = mysqli_fetch_assoc($res);

        if ($act === 'plus') {
            if ($_SESSION['cart'][$id_act] < $p['stok']) {
                $_SESSION['cart'][$id_act]++;
            } else {
                $msg_error = "Stok produk tidak mencukupi untuk ditambah lagi!";
            }
        } elseif ($act === 'minus') {
            $_SESSION['cart'][$id_act]--;
            if ($_SESSION['cart'][$id_act] <= 0) {
                unset($_SESSION['cart'][$id_act]);
            }
        }
    }
    $open_cart_modal = true;
}

// 3. HAPUS DARI KERANJANG
if (isset($_GET['hapus_cart'])) {
    $id_del = $_GET['hapus_cart'];
    unset($_SESSION['cart'][$id_del]);
    $open_cart_modal = true;
}

// 4. PROSES CHECKOUT / PEMBELIAN
if (isset($_POST['checkout'])) {
    $id_pelanggan      = $_POST['id_pelanggan'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $tanggal           = date('Y-m-d H:i:s');
    $total_harga       = 0;

    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $id => $qty) {
            $r = mysqli_query($conn, "SELECT harga FROM produk WHERE id_produk='$id'");
            $p = mysqli_fetch_assoc($r);
            $total_harga += ($p['harga'] * $qty);
        }

        // Insert ke Penjualan
        $q_penjualan = "INSERT INTO penjualan (tanggal_penjualan, total_harga, metode_pembayaran, id_pelanggan) 
                        VALUES ('$tanggal', '$total_harga', '$metode_pembayaran', '$id_pelanggan')";
        if (mysqli_query($conn, $q_penjualan)) {
            $id_penjualan = mysqli_insert_id($conn);

            // Insert Detail & Kurangi Stok
            foreach ($_SESSION['cart'] as $id => $qty) {
                $r = mysqli_query($conn, "SELECT harga, stok FROM produk WHERE id_produk='$id'");
                $p = mysqli_fetch_assoc($r);
                $subtotal = $p['harga'] * $qty;

                mysqli_query($conn, "INSERT INTO detail_penjualan (id_penjualan, id_produk, jumlah, harga_satuan, subtotal) 
                                     VALUES ('$id_penjualan', '$id', '$qty', '{$p['harga']}', '$subtotal')");

                $stok_baru = $p['stok'] - $qty;
                mysqli_query($conn, "UPDATE produk SET stok='$stok_baru' WHERE id_produk='$id'");
            }

            $_SESSION['cart'] = [];
            $_SESSION['last_transaksi_id'] = $id_penjualan;
            header("Location: index.php?struk=1");
            exit;
        } else {
            $msg_error = "Gagal memproses transaksi!";
        }
    }
}

// Ambil Data Produk & Pelanggan
$produk_query = mysqli_query($conn, "SELECT * FROM produk WHERE stok > 0");
$pelanggan_query = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama_pelanggan ASC");

// Ambil List Produk untuk Form Tambah Produk di dalam Keranjang
$all_produk_query = mysqli_query($conn, "SELECT * FROM produk WHERE stok > 0 ORDER BY nama_produk ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimarket Sejahtera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @media print {
            body * { visibility: hidden; }
            #areaStruk, #areaStruk * { visibility: visible; }
            #areaStruk { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-shop me-2"></i>Warung Madura</a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <button class="btn btn-warning position-relative fw-bold" data-bs-toggle="modal" data-bs-target="#modalKeranjang">
                    <i class="bi bi-cart-fill me-1"></i> Keranjang
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= array_sum($_SESSION['cart']); ?>
                    </span>
                </button>
                <a href="admin/login.php" class="btn btn-danger btn-sm ms-2"><i class="bi bi-person-lock me-1"></i>Login Admin</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <!-- Alert Pesan -->
        <?php if (isset($msg_success)) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= $msg_success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($msg_error)) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $msg_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Hero -->
        <div class="bg-white p-4 rounded shadow-sm text-center mb-4">
            <h2 class="fw-bold text-">Katalog Produk Harian</h2>
            <p class="text-muted mb-0">Pilih produk favorit Anda dan selesaikan pembelian dengan cepat.</p>
        </div>

        <!-- Catalog Produk -->
        <div class="row row-cols-1 row-cols-md-4 g-4 mb-5">
            <?php while ($p = mysqli_fetch_assoc($produk_query)) : ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-dark text-white w-fit mb-2"><?= htmlspecialchars($p['kategori']); ?></span>
                        <h5 class="card-title fw-bold"><?= htmlspecialchars($p['nama_produk']); ?></h5>
                        <p class="text-danger fs-5 fw-bold mb-1">Rp <?= number_format($p['harga'], 0, ',', '.'); ?></p>
                        <small class="text-muted mb-3">Sisa Stok: <strong><?= $p['stok']; ?></strong></small>
                        
                        <form action="" method="POST" class="mt-auto">
                            <input type="hidden" name="id_produk" value="<?= $p['id_produk']; ?>">
                            <div class="input-group mb-2">
                                <span class="input-group-text">Qty</span>
                                <input type="number" name="jumlah" class="form-control" value="1" min="1" max="<?= $p['stok']; ?>" required>
                            </div>
                            <button type="submit" name="add_to_cart" class="btn btn-warning w-100 btn-sm fw-bold">
                                <i class="bi bi-cart-plus me-1"></i> + Keranjang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- MODAL KERANJANG -->
    <div class="modal fade" id="modalKeranjang" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-black text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-cart3 me-2"></i>Keranjang Belanja</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- FITUR BARU: FORM TAMBAH PRODUK LAIN DI DALAM MODAL KERANJANG -->
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-plus-circle-fill me-1"></i> Tambah Produk Lain ke Keranjang</h6>
                            <form action="" method="POST" class="row g-2 align-items-end">
                                <input type="hidden" name="from_modal" value="1">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Pilih Produk</label>
                                    <select name="id_produk" class="form-select form-select-sm" required>
                                        <option value="">-- Pilih Produk --</option>
                                        <?php while ($ap = mysqli_fetch_assoc($all_produk_query)) : ?>
                                            <option value="<?= $ap['id_produk']; ?>">
                                                <?= htmlspecialchars($ap['nama_produk']); ?> (Rp <?= number_format($ap['harga'], 0, ',', '.'); ?> - Stok: <?= $ap['stok']; ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Jumlah</label>
                                    <input type="number" name="jumlah" class="form-control form-control-sm" value="1" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" name="add_to_cart" class="btn btn-warning btn-sm w-100 fw-bold">
                                        <i class="bi bi-cart-plus me-1"></i> Tambahkan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if (!empty($_SESSION['cart'])) : ?>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th class="text-center">Jumlah</th>
                                        <th>Subtotal</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $grand_total = 0;
                                    foreach ($_SESSION['cart'] as $id => $qty) :
                                        $res_item = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk='$id'");
                                        $item = mysqli_fetch_assoc($res_item);
                                        $subtotal = $item['harga'] * $qty;
                                        $grand_total += $subtotal;
                                    ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($item['nama_produk']); ?></td>
                                        <td>Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                                        <td class="text-center" style="width: 130px;">
                                            <div class="btn-group btn-group-sm">
                                                <a href="index.php?action_cart=minus&id_produk=<?= $id; ?>" class="btn btn-outline-secondary fw-bold">-</a>
                                                <span class="btn btn-light disabled px-3 fw-bold text-dark"><?= $qty; ?></span>
                                                <a href="index.php?action_cart=plus&id_produk=<?= $id; ?>" class="btn btn-outline-secondary fw-bold">+</a>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-success">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                        <td class="text-center">
                                            <a href="index.php?hapus_cart=<?= $id; ?>" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-warning fs-5 fw-bold">
                                        <td colspan="3" class="text-end">Total Pembelian:</td>
                                        <td colspan="2" class="text-success">Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Form Checkout -->
                        <form action="" method="POST" id="formCheckout" class="mt-4 p-3 border rounded bg-light">
                            <h6 class="fw-bold mb-3"><i class="bi bi-card-checklist me-1"></i>Konfirmasi Data Pembelian</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Pelanggan</label>
                                <select name="id_pelanggan" class="form-select" required>
                                    <option value="">-- Pilih Data Pelanggan --</option>
                                    <?php while ($pel = mysqli_fetch_assoc($pelanggan_query)) : ?>
                                        <option value="<?= $pel['id_pelanggan']; ?>"><?= htmlspecialchars($pel['nama_pelanggan']); ?> (<?= $pel['no_telepon']; ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Metode Pembayaran</label>
                                <select name="metode_pembayaran" class="form-select" required>
                                    <option value="Tunai / Cash">Tunai / Cash</option>
                                    <option value="Transfer Bank">Transfer Bank</option>
                                    <option value="Qris / E-Wallet">Qris / E-Wallet</option>
                                </select>
                            </div>
                            <button type="submit" name="checkout" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan transaksi ini?');" class="btn btn-success w-100 fw-bold py-2">
                                <i class="bi bi-bag-check-fill me-1"></i> Selesaikan Pembelian (Rp <?= number_format($grand_total, 0, ',', '.'); ?>)
                            </button>
                        </form>
                    <?php else : ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                            <p class="mb-0">Keranjang belanja Anda masih kosong.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL STRUK / NOTA PEMBELIAN -->
    <?php
    if (isset($_GET['struk']) && isset($_SESSION['last_transaksi_id'])) :
        $id_tx = $_SESSION['last_transaksi_id'];
        $q_tx  = mysqli_query($conn, "SELECT p.*, pl.nama_pelanggan, pl.no_telepon FROM penjualan p 
                                      JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan 
                                      WHERE p.id_penjualan='$id_tx'");
        $tx = mysqli_fetch_assoc($q_tx);

        $q_dtl = mysqli_query($conn, "SELECT d.*, pr.nama_produk FROM detail_penjualan d 
                                      JOIN produk pr ON d.id_produk = pr.id_produk 
                                      WHERE d.id_penjualan='$id_tx'");
    ?>
    <div class="modal fade show" id="modalStruk" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-4" id="areaStruk">
                    <div class="text-center mb-3">
                        <h4 class="fw-bold mb-0">MINIMARKET SEJAHTERA</h4>
                        <small class="text-muted">Jl. Pemuda No. 5, Kendal | Telp: 0831-2421-2245</small>
                        <hr class="my-2 border-dashed">
                    </div>

                    <div class="small mb-3">
                        <div class="d-flex justify-content-between"><span>No. Nota:</span> <strong>#TX-<?= $tx['id_penjualan']; ?></strong></div>
                        <div class="d-flex justify-content-between"><span>Tanggal:</span> <span><?= $tx['tanggal_penjualan']; ?></span></div>
                        <div class="d-flex justify-content-between"><span>Pelanggan:</span> <span><?= htmlspecialchars($tx['nama_pelanggan']); ?></span></div>
                        <div class="d-flex justify-content-between"><span>Pembayaran:</span> <span><?= $tx['metode_pembayaran']; ?></span></div>
                    </div>

                    <table class="table table-sm border-top border-bottom small mb-3">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($d = mysqli_fetch_assoc($q_dtl)) : ?>
                            <tr>
                                <td><?= htmlspecialchars($d['nama_produk']); ?></td>
                                <td class="text-center"><?= $d['jumlah']; ?></td>
                                <td class="text-end"><?= number_format($d['harga_satuan'], 0, ',', '.'); ?></td>
                                <td class="text-end"><?= number_format($d['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between fs-5 fw-bold text-danger mb-3">
                        <span>TOTAL BAYAR:</span>
                        <span>Rp <?= number_format($tx['total_harga'], 0, ',', '.'); ?></span>
                    </div>

                    <div class="text-center small text-muted mb-3">
                        <p class="mb-0">Terima kasih telah berbelanja!</p>
                        <small>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</small>
                    </div>

                    <div class="d-flex gap-2 no-print">
                        <button onclick="window.print()" class="btn btn-primary btn-sm w-100 fw-bold"><i class="bi bi-printer me-1"></i> Cetak Struk</button>
                        <a href="index.php" class="btn btn-success btn-sm w-100 fw-bold">Selesai</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AUTOMATIC RE-OPEN MODAL KERANJANG SETELAH AKSI KELOLA DARI DALAM KERANJANG -->
    <?php if ($open_cart_modal) : ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var cartModal = new bootstrap.Modal(document.getElementById('modalKeranjang'));
            cartModal.show();
        });
    </script>
    <?php endif; ?>
</body>
</html>