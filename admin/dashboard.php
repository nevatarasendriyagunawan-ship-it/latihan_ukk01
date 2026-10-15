<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}
include '../koneksi.php';

// Hitung Ringkasan Data
$tgl_hari_ini = date('Y-m-d');

// Pendapatan Hari Ini
$q_today = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM penjualan WHERE DATE(tanggal_penjualan) = '$tgl_hari_ini'");
$row_today = mysqli_fetch_assoc($q_today);
$pendapatan_hari_ini = $row_today['total'] ?? 0;

// Total Pendapatan Keseluruhan
$q_all = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM penjualan");
$row_all = mysqli_fetch_assoc($q_all);
$total_pendapatan = $row_all['total'] ?? 0;

// Ambil Transaksi Penjualan Hari Ini
$query_transaksi_hari_ini = mysqli_query($conn, "SELECT p.*, pl.nama_pelanggan, pl.no_telepon 
                                                 FROM penjualan p 
                                                 JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan 
                                                 WHERE DATE(p.tanggal_penjualan) = '$tgl_hari_ini' 
                                                 ORDER BY p.tanggal_penjualan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Minimarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <!-- Navbar Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Admin Panel Minimarket</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link active" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="produk.php">Data Produk</a>
                <a class="nav-link" href="pelanggan.php">Data Pelanggan</a>
                <a class="nav-link" href="penjualan.php">Laporan Penjualan</a>
                <a class="nav-link text-danger fw-bold ms-3" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <!-- Welcoming Section -->
        <div class="p-4 bg-white rounded-3 shadow-sm mb-4">
            <h3 class="fw-bold">Halo, <?= htmlspecialchars($_SESSION['nama_admin']); ?>!</h3>
            <p class="text-muted mb-0">Selamat datang di Panel Pengelolaan Data dan Rekap Pendapatan Minimarket.</p>
        </div>

        <!-- TOMBOL NAVIGASI CEPAT (DILETAKKAN DI ATAS PENDAPATAN HARI INI) -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a href="produk.php" class="card border-0 bg-white shadow-sm text-decoration-none h-100 hover-shadow transition">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-primary text-white p-3 rounded-3 me-3">
                            <i class="bi bi-box-seam fs-3"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Data Produk</h6>
                            <span class="text-muted small">Kelola stok & harga produk &rarr;</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="pelanggan.php" class="card border-0 bg-white shadow-sm text-decoration-none h-100 hover-shadow transition">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-info text-dark p-3 rounded-3 me-3">
                            <i class="bi bi-people fs-3"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Data Pelanggan</h6>
                            <span class="text-muted small">Kelola data pembeli terdaftar &rarr;</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="penjualan.php" class="card border-0 bg-white shadow-sm text-decoration-none h-100 hover-shadow transition">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-warning text-dark p-3 rounded-3 me-3">
                            <i class="bi bi-file-earmark-bar-graph fs-3"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Laporan Penjualan</h6>
                            <span class="text-muted small">Lihat rincian riwayat transaksi &rarr;</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Metric Card Pendapatan -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 bg-success text-white shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase fw-semibold mb-1">Pendapatan Hari Ini (<?= date('d-m-Y'); ?>)</h6>
                                <h2 class="fw-bold mb-0">Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.'); ?></h2>
                            </div>
                            <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 bg-primary text-white shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase fw-semibold mb-1">Total Pendapatan Keseluruhan</h6>
                                <h2 class="fw-bold mb-0">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></h2>
                            </div>
                            <i class="bi bi-wallet2 fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Transaksi Pembelian Hari Ini -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i>Pelanggan Checkout Hari Ini</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>No. Nota</th>
                                <th>Waktu / Tanggal</th>
                                <th>Nama Pelanggan</th>
                                <th>No. Telp</th>
                                <th>Metode Bayar</th>
                                <th>Total Belanja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_transaksi_hari_ini) > 0) : ?>
                                <?php while ($tx = mysqli_fetch_assoc($query_transaksi_hari_ini)) : ?>
                                <tr>
                                    <td><strong class="text-primary">#TX-<?= $tx['id_penjualan']; ?></strong></td>
                                    <td><?= date('d-m-Y H:i', strtotime($tx['tanggal_penjualan'])); ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($tx['nama_pelanggan']); ?></td>
                                    <td><?= htmlspecialchars($tx['no_telepon']); ?></td>
                                    <td><span class="badge bg-info text-dark"><?= $tx['metode_pembayaran']; ?></span></td>
                                    <td class="fw-bold text-success">Rp <?= number_format($tx['total_harga'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada transaksi pembelian untuk hari ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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