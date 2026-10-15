<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}
include '../koneksi.php';

// Tambah Produk
if (isset($_POST['tambah'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $harga    = $_POST['harga'];
    $stok     = $_POST['stok'];
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);

    $query = "INSERT INTO produk (nama_produk, harga, stok, kategori) VALUES ('$nama', '$harga', '$stok', '$kategori')";
    mysqli_query($conn, $query);
    header("Location: produk.php");
    exit;
}

// Edit Produk
if (isset($_POST['edit'])) {
    $id       = $_POST['id_produk'];
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $harga    = $_POST['harga'];
    $stok     = $_POST['stok'];
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);

    $query = "UPDATE produk SET nama_produk='$nama', harga='$harga', stok='$stok', kategori='$kategori' WHERE id_produk='$id'";
    mysqli_query($conn, $query);
    header("Location: produk.php");
    exit;
}

// Hapus Produk
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM produk WHERE id_produk='$id'");
    header("Location: produk.php");
    exit;
}

$produk = mysqli_query($conn, "SELECT * FROM produk ORDER BY id_produk DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Produk - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <!-- NAVBAR ADMIN KONSISTEN DENGAN DASHBOARD -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Admin Panel Minimarket</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link active" href="produk.php">Data Produk</a>
                <a class="nav-link" href="pelanggan.php">Data Pelanggan</a>
                <a class="nav-link" href="penjualan.php">Laporan Penjualan</a>
                <a class="nav-link text-danger fw-bold ms-3" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Manajemen Data Produk</h4>
            <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="bi bi-plus-lg me-1"></i>Tambah Produk</button>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($produk)) : ?>
                        <tr>
                            <td><?= $row['id_produk']; ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['nama_produk']); ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['kategori']); ?></span></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                            <td><?= $row['stok']; ?></td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_produk']; ?>"><i class="bi bi-pencil-square"></i></button>
                                <a href="produk.php?hapus=<?= $row['id_produk']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data ini?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEdit<?= $row['id_produk']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="" method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Edit Produk</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_produk" value="<?= $row['id_produk']; ?>">
                                            <div class="mb-2">
                                                <label class="form-label">Nama Produk</label>
                                                <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($row['nama_produk']); ?>" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Kategori</label>
                                                <input type="text" name="kategori" class="form-control" value="<?= htmlspecialchars($row['kategori']); ?>" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Harga</label>
                                                <input type="number" name="harga" class="form-control" value="<?= $row['harga']; ?>" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Stok</label>
                                                <input type="number" name="stok" class="form-control" value="<?= $row['stok']; ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="edit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Tambah Produk Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" name="nama_produk" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="kategori" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Harga</label>
                            <input type="number" name="harga" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stok" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="tambah" class="btn btn-success btn-sm">Simpan</button>
                    </div>
                </form>
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