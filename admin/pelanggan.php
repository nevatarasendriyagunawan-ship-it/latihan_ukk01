<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header("Location: login.php");
    exit;
}
include '../koneksi.php';

// Tambah Pelanggan
if (isset($_POST['tambah'])) {
    $nama       = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $alamat     = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $tgl_daftar = $_POST['tgl_daftar'];

    $query = "INSERT INTO pelanggan (nama_pelanggan, alamat, no_telepon, email, tgl_daftar) 
              VALUES ('$nama', '$alamat', '$no_telepon', '$email', '$tgl_daftar')";
    mysqli_query($conn, $query);
    header("Location: pelanggan.php");
}

// Edit Pelanggan
if (isset($_POST['edit'])) {
    $id         = $_POST['id_pelanggan'];
    $nama       = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $alamat     = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $tgl_daftar = $_POST['tgl_daftar'];

    $query = "UPDATE pelanggan SET 
              nama_pelanggan='$nama', 
              alamat='$alamat', 
              no_telepon='$no_telepon', 
              email='$email', 
              tgl_daftar='$tgl_daftar' 
              WHERE id_pelanggan='$id'";
    mysqli_query($conn, $query);
    header("Location: pelanggan.php");
}

// Hapus Pelanggan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM pelanggan WHERE id_pelanggan='$id'");
    header("Location: pelanggan.php");
}

$pelanggan = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Pelanggan - Admin</title>
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
                <a class="nav-link active" href="pelanggan.php">Data Pelanggan</a>
                <a class="nav-link" href="penjualan.php">Laporan Penjualan</a>
                <a class="nav-link text-danger fw-bold ms-3" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold">Manajemen Data Pelanggan</h4>
            <button class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-person-plus-fill me-1"></i>Tambah Pelanggan
            </button>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nama Pelanggan</th>
                            <th>No. Telepon</th>
                            <th>Email</th>
                            <th>Alamat</th>
                            <th>Tgl Daftar</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($pelanggan) > 0) : ?>
                            <?php while ($row = mysqli_fetch_assoc($pelanggan)) : ?>
                            <tr>
                                <td><?= $row['id_pelanggan']; ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['nama_pelanggan']); ?></td>
                                <td><?= htmlspecialchars($row['no_telepon']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><?= htmlspecialchars($row['alamat']); ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tgl_daftar'])); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_pelanggan']; ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="pelanggan.php?hapus=<?= $row['id_pelanggan']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data pelanggan ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal Edit Pelanggan -->
                            <div class="modal fade" id="modalEdit<?= $row['id_pelanggan']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="" method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit Data Pelanggan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id_pelanggan" value="<?= $row['id_pelanggan']; ?>">
                                                <div class="mb-2">
                                                    <label class="form-label">Nama Pelanggan</label>
                                                    <input type="text" name="nama_pelanggan" class="form-control" value="<?= htmlspecialchars($row['nama_pelanggan']); ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">No. Telepon</label>
                                                    <input type="text" name="no_telepon" class="form-control" value="<?= htmlspecialchars($row['no_telepon']); ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']); ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Alamat</label>
                                                    <textarea name="alamat" class="form-control" rows="2" required><?= htmlspecialchars($row['alamat']); ?></textarea>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Tanggal Daftar</label>
                                                    <input type="date" name="tgl_daftar" class="form-control" value="<?= $row['tgl_daftar']; ?>" required>
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
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center py-3 text-muted">Belum ada data pelanggan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Pelanggan -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Tambah Pelanggan Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan" class="form-control" required placeholder="Contoh: Budi Santoso">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="no_telepon" class="form-control" required placeholder="Contoh: 08123456789">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="Contoh: budi@gmail.com">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2" required placeholder="Masukkan alamat lengkap"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Tanggal Daftar</label>
                            <input type="date" name="tgl_daftar" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="tambah" class="btn btn-success btn-sm">Simpan Data</button>
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