<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}
include '../koneksi.php';

// Filter Berdasarkan Tanggal
$where_clause = "";
$tgl_filter = "";

if (isset($_GET['tgl_pilih']) && !empty($_GET['tgl_pilih'])) {
    $tgl_filter = $_GET['tgl_pilih'];
    $where_clause = "WHERE DATE(p.tanggal_penjualan) = '$tgl_filter'";
}

// Ambil Data Penjualan
$q_penjualan = mysqli_query($conn, "SELECT p.*, pl.nama_pelanggan, pl.no_telepon 
                                     FROM penjualan p 
                                     JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan 
                                     $where_clause 
                                     ORDER BY p.tanggal_penjualan DESC");

// Simpan data ke array agar bisa di-loop 2 kali (Tabel & Modal)
$data_penjualan = [];
while ($row = mysqli_fetch_assoc($q_penjualan)) {
    $data_penjualan[] = $row;
}

// Hitung Total Pendapatan Sesuai Filter
$q_sum = mysqli_query($conn, "SELECT SUM(p.total_harga) AS total FROM penjualan p $where_clause");
$row_sum = mysqli_fetch_assoc($q_sum);
$total_pendapatan_filter = $row_sum['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan & Pendapatan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <!-- Navbar Admin -->
         <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Admin Panel Minimarket</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="produk.php">Data Produk</a>
                <a class="nav-link" href="pelanggan.php">Data Pelanggan</a>
                <a class="nav-link active" href="penjualan.php">Laporan Penjualan</a>
                <a class="nav-link text-danger fw-bold ms-3" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
            </div>
        </div>
    </nav>
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Laporan Pendapatan & Pembelian Pelanggan</h4>
        </div>

        <!-- Filter Tanggal & Ringkasan -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Pilih Tanggal Pembelian</label>
                        <input type="date" name="tgl_pilih" class="form-control" value="<?= $tgl_filter; ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-filter me-1"></i> Filter Data</button>
                        <a href="penjualan.php" class="btn btn-secondary fw-bold">Reset</a>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="text-muted small d-block">Total Pendapatan Terfilter:</span>
                        <h3 class="fw-bold text-success mb-0">Rp <?= number_format($total_pendapatan_filter, 0, ',', '.'); ?></h3>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABEL UTAMA LAPORAN -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID Nota</th>
                                <th>Tanggal & Waktu</th>
                                <th>Nama Pelanggan</th>
                                <th>Kontak</th>
                                <th>Metode Bayar</th>
                                <th>Total Pembelian</th>
                                <th class="text-center">Detail Item</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($data_penjualan) > 0) : ?>
                                <?php foreach ($data_penjualan as $row) : ?>
                                <tr>
                                    <td><strong class="text-primary">#TX-<?= $row['id_penjualan']; ?></strong></td>
                                    <td><?= date('d-m-Y H:i:s', strtotime($row['tanggal_penjualan'])); ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($row['nama_pelanggan']); ?></td>
                                    <td><?= htmlspecialchars($row['no_telepon']); ?></td>
                                    <td><span class="badge bg-secondary"><?= $row['metode_pembayaran']; ?></span></td>
                                    <td class="fw-bold text-success">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-info btn-sm text-white fw-bold" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id_penjualan']; ?>">
                                            <i class="bi bi-eye-fill me-1"></i> Rincian
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Tidak ada data transaksi pembelian.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL DIPISAHKAN DI LUAR TABLE KEDALAM FOREACH KEDUA -->
    <?php foreach ($data_penjualan as $row) : ?>
    <div class="modal fade" id="modalDetail<?= $row['id_penjualan']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">Detail Nota #TX-<?= $row['id_penjualan']; ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1"><strong>Pelanggan:</strong> <?= htmlspecialchars($row['nama_pelanggan']); ?></p>
                    <p class="mb-3"><strong>Tanggal:</strong> <?= date('d-m-Y H:i:s', strtotime($row['tanggal_penjualan'])); ?></p>
                    
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $id_penj = $row['id_penjualan'];
                            $q_dtl = mysqli_query($conn, "SELECT d.*, pr.nama_produk 
                                                          FROM detail_penjualan d 
                                                          JOIN produk pr ON d.id_produk = pr.id_produk 
                                                          WHERE d.id_penjualan='$id_penj'");
                            while ($dt = mysqli_fetch_assoc($q_dtl)) :
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($dt['nama_produk']); ?></td>
                                <td class="text-center"><?= $dt['jumlah']; ?></td>
                                <td class="text-end">Rp <?= number_format($dt['harga_satuan'], 0, ',', '.'); ?></td>
                                <td class="text-end">Rp <?= number_format($dt['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <tr class="fw-bold table-warning">
                                <td colspan="3" class="text-end">Total:</td>
                                <td class="text-end text-danger">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('a[href="logout.php"]').forEach(function(element) {
            element.addEventListener('click', function(event) {
                var setuju = confirm("Apakah Anda yakin ingin keluar dari sistem?");
                if (!setuju) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>