<?php
$page_title = "Data Produk";
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/header.php';

// Ambil data produk
$query = "SELECT * FROM tabel_produk ORDER BY id_produk ASC";
$result = mysqli_query($conn, $query);
$total_produk = mysqli_num_rows($result);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1">Data Produk</h4>
        <p class="text-muted small mb-0">Total: <?= $total_produk ?> produk terdaftar di sistem.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/pages/products/add.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Produk
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th style="width: 80px;" class="text-center">ID</th>
                        <th>Nama Produk</th>
                        <th>Harga Pokok</th>
                        <th>Diskon</th>
                        <th>Harga Jual (Kasir)</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_produk > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): 
                            $harga_jual = max(0, $row['harga'] - $row['diskon']);
                        ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center font-monospace">#<?= $row['id_produk'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['nama_produk']) ?></td>
                                <td><?= format_rupiah($row['harga']) ?></td>
                                <td>
                                    <?php if ($row['diskon'] > 0): ?>
                                        <span class="badge bg-success">Hemat <?= format_rupiah($row['diskon']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-primary"><?= format_rupiah($harga_jual) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/pages/products/edit.php?id=<?= $row['id_produk'] ?>" class="btn btn-warning text-dark" title="Edit">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="<?= BASE_URL ?>/process/products_process.php?action=delete&id_produk=<?= $row['id_produk'] ?>" 
                                           class="btn btn-danger confirm-delete" 
                                           data-message="Hapus produk '<?= htmlspecialchars($row['nama_produk']) ?>'?" title="Hapus">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Belum ada data produk. Silakan klik tombol "Tambah Produk".
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
