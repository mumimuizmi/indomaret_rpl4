<?php
$page_title = "Dashboard";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

// Query Ringkasan Data
$qTrx = mysqli_query($conn, "SELECT COUNT(*) as total_trx, COALESCE(SUM(total), 0) as total_pendapatan FROM tabel_transaksi");
$rTrx = mysqli_fetch_assoc($qTrx);

$qProd = mysqli_query($conn, "SELECT COUNT(*) as total_produk FROM tabel_produk");
$rProd = mysqli_fetch_assoc($qProd);

$qKasir = mysqli_query($conn, "SELECT COUNT(*) as total_kasir FROM tabel_kasir");
$rKasir = mysqli_fetch_assoc($qKasir);

$qDonasi = mysqli_query($conn, "SELECT COUNT(*) as total_donasi FROM tabel_donasi");
$rDonasi = mysqli_fetch_assoc($qDonasi);

// Query 5 Transaksi Terakhir
$qRecent = mysqli_query($conn, "
    SELECT t.*, d.nama_donasi, d.harga_donasi 
    FROM tabel_transaksi t 
    LEFT JOIN tabel_donasi d ON t.id_donasi = d.id_donasi 
    ORDER BY t.tanggal_transaksi DESC 
    LIMIT 5
");

// List program donasi aktif
$donasi_list = mysqli_query($conn, "SELECT * FROM tabel_donasi ORDER BY harga_donasi ASC LIMIT 3");
?>

<!-- Header / Banner Sederhana -->
<div class="card mb-4">
    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Sistem Kasir Indomaret RPL 4</h4>
            <p class="text-muted mb-0 small">Aplikasi Point of Sales (POS) sederhana untuk transaksi penjualan, katalog produk, dan program donasi.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/pages/transactions/add.php" class="btn btn-warning fw-bold text-dark px-3 py-2">
                <i class="bi bi-cart-plus me-1"></i> Buat Transaksi Baru
            </a>
        </div>
    </div>
</div>

<!-- 4 Kotak Ringkasan Statistik -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-icon-box metric-icon-blue">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <div class="metric-label">Total Transaksi</div>
                <div class="metric-value"><?= number_format($rTrx['total_trx'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-icon-box metric-icon-green">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div class="metric-label">Total Pendapatan</div>
                <div class="metric-value text-success" style="font-size: 1.15rem;"><?= format_rupiah($rTrx['total_pendapatan'] ?? 0) ?></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-icon-box metric-icon-yellow">
                <i class="bi bi-box-seam"></i>
            </div>
            <div>
                <div class="metric-label">Jumlah Produk</div>
                <div class="metric-value"><?= number_format($rProd['total_produk'] ?? 0) ?></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-icon-box metric-icon-red">
                <i class="bi bi-person-badge"></i>
            </div>
            <div>
                <div class="metric-label">Petugas Kasir</div>
                <div class="metric-value"><?= number_format($rKasir['total_kasir'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Konten Utama: Transaksi Terakhir & Menu Cepat -->
<div class="row g-4">
    <!-- Kolom Kiri: 5 Transaksi Terakhir -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold"><i class="bi bi-clock-history me-1 text-primary"></i> 5 Transaksi Terakhir</span>
                <a href="<?= BASE_URL ?>/pages/transactions/list.php" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 13px;">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Transaksi</th>
                                <th>Waktu</th>
                                <th>No. Order</th>
                                <th>Total</th>
                                <th>Bayar</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($qRecent) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($qRecent)): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASE_URL ?>/pages/transactions/transaction_details.php?kode=<?= urlencode($row['kode_transaksi']) ?>" class="fw-bold text-decoration-none">
                                                <?= htmlspecialchars($row['kode_transaksi']) ?>
                                            </a>
                                            <?php if (!empty($row['nama_donasi'])): ?>
                                                <div class="small text-danger">+ Donasi</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small">
                                            <?= date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])) ?>
                                        </td>
                                        <td>
                                            <span class="badge-order-id">#<?= htmlspecialchars($row['no_order']) ?></span>
                                        </td>
                                        <td class="fw-bold">
                                            <?= format_rupiah($row['total']) ?>
                                        </td>
                                        <td>
                                            <?php if ($row['tunai'] > 0 && $row['QRIS'] > 0): ?>
                                                <span class="badge bg-primary">Tunai + QRIS</span>
                                            <?php elseif ($row['QRIS'] > 0): ?>
                                                <span class="badge bg-info text-dark">QRIS</span>
                                            <?php elseif ($row['tunai'] > 0): ?>
                                                <span class="badge bg-success">Tunai</span>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= BASE_URL ?>/pages/transactions/transaction_details.php?kode=<?= urlencode($row['kode_transaksi']) ?>" class="btn btn-sm btn-primary py-0 px-2" title="Lihat Detail">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Belum ada transaksi tercatat.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Menu Cepat & Donasi -->
    <div class="col-lg-4">
        <!-- Menu Pintas Sederhana -->
        <div class="card mb-3">
            <div class="card-header bg-light py-2 fw-bold">
                <i class="bi bi-grid-fill me-1 text-primary"></i> Menu Pintas
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/pages/transactions/add.php" class="btn btn-warning fw-bold text-dark text-start">
                        <i class="bi bi-cart4 me-2"></i> Input Transaksi Baru
                    </a>
                    <a href="<?= BASE_URL ?>/pages/products/add.php" class="btn btn-primary text-start">
                        <i class="bi bi-plus-circle me-2"></i> Tambah Produk Baru
                    </a>
                    <a href="<?= BASE_URL ?>/pages/cashiers/add.php" class="btn btn-secondary text-start">
                        <i class="bi bi-person-plus me-2"></i> Tambah Data Kasir
                    </a>
                    <a href="<?= BASE_URL ?>/pages/donations/add.php" class="btn btn-danger text-start">
                        <i class="bi bi-heart me-2"></i> Tambah Program Donasi
                    </a>
                </div>
            </div>
        </div>

        <!-- Program Donasi -->
        <div class="card">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-heart-fill me-1 text-danger"></i> Program Donasi</span>
                <a href="<?= BASE_URL ?>/pages/donations/list.php" class="small text-decoration-none">Kelola</a>
            </div>
            <div class="card-body p-3">
                <?php if (mysqli_num_rows($donasi_list) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php while ($d = mysqli_fetch_assoc($donasi_list)): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span><?= htmlspecialchars($d['nama_donasi']) ?></span>
                                <span class="badge bg-success"><?= format_rupiah($d['harga_donasi']) ?></span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted small mb-0">Belum ada program donasi.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
