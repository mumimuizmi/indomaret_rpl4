<?php
$page_title = "Data Kasir";
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/header.php';

$query = "SELECT * FROM tabel_kasir ORDER BY id_kasir ASC";
$result = mysqli_query($conn, $query);
$total_kasir = mysqli_num_rows($result);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1">Data Kasir</h4>
        <p class="text-muted small mb-0">Total: <?= $total_kasir ?> petugas kasir terdaftar.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/pages/cashiers/add.php" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus me-1"></i> Tambah Kasir
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
                        <th>Nama Kasir</th>
                        <th>No. Telepon / WA</th>
                        <th>Alamat</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_kasir > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center font-monospace">#<?= $row['id_kasir'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['nama_kasir']) ?></td>
                                <td><?= htmlspecialchars($row['no_telp'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['alamat'] ?? '-') ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/pages/cashiers/edit.php?id=<?= $row['id_kasir'] ?>" class="btn btn-warning text-dark" title="Edit">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="<?= BASE_URL ?>/process/cashiers_process.php?action=delete&id_kasir=<?= $row['id_kasir'] ?>" 
                                           class="btn btn-danger confirm-delete" 
                                           data-message="Hapus data kasir '<?= htmlspecialchars($row['nama_kasir']) ?>'?" title="Hapus">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Belum ada data kasir. Silakan klik tombol "Tambah Kasir".
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
