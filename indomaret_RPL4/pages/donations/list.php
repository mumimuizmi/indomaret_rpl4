<?php
$page_title = "Data Donasi";
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/header.php';

$query = "
    SELECT d.*, (SELECT COUNT(*) FROM tabel_transaksi t WHERE t.id_donasi = d.id_donasi) as total_digunakan
    FROM tabel_donasi d
    ORDER BY d.id_donasi ASC
";
$result = mysqli_query($conn, $query);
$total_donasi = mysqli_num_rows($result);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1">Program Donasi</h4>
        <p class="text-muted small mb-0">Total: <?= $total_donasi ?> program donasi terdaftar.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/pages/donations/add.php" class="btn btn-primary btn-sm">
            <i class="bi bi-heart-fill me-1"></i> Tambah Donasi
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
                        <th>Nama Donasi</th>
                        <th>Harga Donasi</th>
                        <th>Digunakan</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_donasi > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center font-monospace">#<?= $row['id_donasi'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['nama_donasi']) ?></td>
                                <td class="fw-bold text-success"><?= format_rupiah($row['harga_donasi']) ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?= $row['total_digunakan'] ?> kali</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/pages/donations/edit.php?id=<?= $row['id_donasi'] ?>" class="btn btn-warning text-dark" title="Edit">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="<?= BASE_URL ?>/process/donations_process.php?action=delete&id_donasi=<?= $row['id_donasi'] ?>" 
                                           class="btn btn-danger confirm-delete" 
                                           data-message="Hapus donasi '<?= htmlspecialchars($row['nama_donasi']) ?>'?" title="Hapus">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Belum ada program donasi. Silakan klik tombol "Tambah Donasi".
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
