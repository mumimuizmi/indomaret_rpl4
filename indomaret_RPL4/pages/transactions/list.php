<?php
$page_title = "Riwayat Transaksi";
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/header.php';

// Filter Pencarian
$search = escape($conn, $_GET['search'] ?? '');
$where = "";
if (!empty($search)) {
    $where = "WHERE t.kode_transaksi LIKE '%$search%' OR t.no_order LIKE '%$search%'";
}

// Ambil data transaksi
$query = "
    SELECT t.*, d.nama_donasi, d.harga_donasi,
           (SELECT COUNT(*) FROM tabel_detail_transaksi dt WHERE dt.kode_transaksi = t.kode_transaksi) as total_item
    FROM tabel_transaksi t
    LEFT JOIN tabel_donasi d ON t.id_donasi = d.id_donasi
    $where
    ORDER BY t.tanggal_transaksi DESC
";
$result = mysqli_query($conn, $query);
$total_trx = mysqli_num_rows($result);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1">Riwayat Transaksi</h4>
        <p class="text-muted small mb-0">Total: <?= $total_trx ?> transaksi tercatat.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/pages/transactions/add.php" class="btn btn-primary btn-sm">
            <i class="bi bi-cart-plus me-1"></i> Transaksi Baru
        </a>
    </div>
</div>

<!-- Filter Pencarian Sederhana -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-5 col-sm-8">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari kode transaksi atau no. order..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
                <?php if (!empty($search)): ?>
                    <a href="<?= BASE_URL ?>/pages/transactions/list.php" class="btn btn-outline-secondary btn-sm">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Transaksi -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th class="text-center">No. Order</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Kembalian</th>
                        <th>Donasi</th>
                        <th class="text-center" style="width: 210px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_trx > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/pages/transactions/transaction_details.php?kode=<?= urlencode($row['kode_transaksi']) ?>" class="fw-bold text-decoration-none">
                                        <?= htmlspecialchars($row['kode_transaksi']) ?>
                                    </a>
                                    <div class="small text-muted"><?= (int)$row['total_item'] ?> item produk</div>
                                </td>
                                <td class="small"><?= format_datetime($row['tanggal_transaksi']) ?></td>
                                <td class="text-center">
                                    <span class="badge-order-id">#<?= htmlspecialchars($row['no_order']) ?></span>
                                </td>
                                <td class="fw-bold"><?= format_rupiah($row['total']) ?></td>
                                <td class="small">
                                    <?php if ($row['tunai'] > 0): ?>
                                        <div><span class="badge bg-success">Tunai</span> <?= format_rupiah($row['tunai']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($row['QRIS'] > 0): ?>
                                        <div class="mt-1"><span class="badge bg-info text-dark">QRIS</span> <?= format_rupiah($row['QRIS']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($row['tunai'] <= 0 && $row['QRIS'] <= 0): ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $row['kembalian'] > 0 ? format_rupiah($row['kembalian']) : '<span class="text-muted small">Rp 0</span>' ?>
                                </td>
                                <td class="small">
                                    <?php if (!empty($row['nama_donasi'])): ?>
                                        <div><?= htmlspecialchars($row['nama_donasi']) ?></div>
                                        <span class="text-danger fw-semibold"><?= format_rupiah($row['harga_donasi']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/pages/transactions/transaction_details.php?kode=<?= urlencode($row['kode_transaksi']) ?>" class="btn btn-primary" title="Detail">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        <a href="<?= BASE_URL ?>/pages/transactions/transaction_details.php?kode=<?= urlencode($row['kode_transaksi']) ?>&print=1" class="btn btn-secondary" title="Cetak Struk">
                                            <i class="bi bi-printer"></i> Struk
                                        </a>
                                        <a href="<?= BASE_URL ?>/process/transactions_process.php?action=delete&kode_transaksi=<?= urlencode($row['kode_transaksi']) ?>"
                                           class="btn btn-danger confirm-delete"
                                           data-message="Hapus transaksi <?= htmlspecialchars($row['kode_transaksi']) ?>?"
                                           title="Hapus">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                Belum ada riwayat transaksi. Silakan klik tombol "Transaksi Baru".
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
