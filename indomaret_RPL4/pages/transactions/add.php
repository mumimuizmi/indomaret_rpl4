<?php
$page_title = "Transaksi Baru";
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/header.php';

// Auto-generate No Order berikutnya
$qOrder = mysqli_query($conn, "SELECT MAX(no_order) as max_order FROM tabel_transaksi");
$rOrder = mysqli_fetch_assoc($qOrder);
$next_order = ($rOrder && $rOrder['max_order'] > 0) ? ((int)$rOrder['max_order'] + 1) : 1001;

// Auto-generate Kode Transaksi unik
$auto_kode = "TRX" . date('Ymd') . str_pad((string)$next_order, 4, "0", STR_PAD_LEFT);

// Master Kasir, Donasi, Produk
$kasir_list = mysqli_query($conn, "SELECT * FROM tabel_kasir ORDER BY nama_kasir ASC");
$donasi_list = mysqli_query($conn, "SELECT * FROM tabel_donasi ORDER BY harga_donasi ASC");
$produk_list = mysqli_query($conn, "SELECT * FROM tabel_produk ORDER BY nama_produk ASC");

$cashiers_arr = [];
while ($k = mysqli_fetch_assoc($kasir_list)) {
    $cashiers_arr[] = $k;
}

$donations_arr = [];
while ($d = mysqli_fetch_assoc($donasi_list)) {
    $donations_arr[] = $d;
}

$products_arr = [];
while ($p = mysqli_fetch_assoc($produk_list)) {
    $products_arr[] = $p;
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1">Input Transaksi Baru</h4>
        <p class="text-muted small mb-0">Tambahkan produk belanja, pilih donasi (jika ada), lalu proses pembayaran.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/pages/transactions/list.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Riwayat Transaksi
        </a>
    </div>
</div>

<?php if (empty($cashiers_arr)): ?>
    <div class="alert alert-warning">
        Belum ada data kasir. Silakan <a href="<?= BASE_URL ?>/pages/cashiers/add.php" class="alert-link">tambah data kasir</a> terlebih dahulu.
    </div>
<?php endif; ?>

<?php if (empty($products_arr)): ?>
    <div class="alert alert-warning">
        Belum ada data produk. Silakan <a href="<?= BASE_URL ?>/pages/products/add.php" class="alert-link">tambah data produk</a> terlebih dahulu.
    </div>
<?php endif; ?>

<form id="pos-form" action="<?= BASE_URL ?>/process/transactions_process.php?action=add" method="POST">
    <!-- Data Header Transaksi -->
    <div class="card mb-3">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-file-earmark-text me-1 text-primary"></i> Data Transaksi
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <label for="kode_transaksi" class="form-label fw-bold small">Kode Transaksi <span class="text-danger">*</span></label>
                    <input type="text" id="kode_transaksi" name="kode_transaksi" class="form-control form-control-sm" maxlength="50" value="<?= htmlspecialchars($auto_kode) ?>" required>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label for="tanggal_transaksi" class="form-label fw-bold small">Tanggal Transaksi</label>
                    <input type="text" id="tanggal_transaksi" name="tanggal_transaksi" class="form-control form-control-sm bg-light" value="<?= date('Y-m-d H:i:s') ?>" readonly>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label for="no_order" class="form-label fw-bold small">No. Order <span class="text-danger">*</span></label>
                    <input type="number" id="no_order" name="no_order" class="form-control form-control-sm" value="<?= $next_order ?>" min="1" required>
                </div>
                <div class="col-md-4 col-sm-6">
                    <label for="cashier-select" class="form-label fw-bold small">Kasir Bertugas <span class="text-danger">*</span></label>
                    <select name="id_kasir" id="cashier-select" class="form-select form-select-sm" required>
                        <option value="">-- Pilih Kasir --</option>
                        <?php foreach ($cashiers_arr as $k): ?>
                            <option value="<?= $k['id_kasir'] ?>">
                                <?= htmlspecialchars($k['nama_kasir']) ?> (ID #<?= $k['id_kasir'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Kolom Kiri: Pilih Produk & Daftar Belanja -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-cart3 me-1 text-primary"></i> Daftar Produk Belanja</span>
                    <span id="item-count-badge" class="badge bg-secondary">0 item</span>
                </div>
                <div class="card-body">
                    <!-- Form Tambah Item ke Keranjang -->
                    <div class="row g-2 align-items-end mb-3 pb-3 border-bottom">
                        <div class="col-md-7">
                            <label for="product-select" class="form-label small fw-bold mb-1">Pilih Produk</label>
                            <select id="product-select" class="form-select form-select-sm">
                                <option value="">-- Pilih Produk --</option>
                                <?php foreach ($products_arr as $prod): 
                                    $net = max(0, $prod['harga'] - $prod['diskon']);
                                ?>
                                    <option value="<?= $prod['id_produk'] ?>"
                                            data-name="<?= htmlspecialchars($prod['nama_produk']) ?>"
                                            data-price="<?= (int)$prod['harga'] ?>"
                                            data-discount="<?= (int)$prod['diskon'] ?>">
                                        <?= htmlspecialchars($prod['nama_produk']) ?> - <?= format_rupiah($net) ?>
                                        <?= $prod['diskon'] > 0 ? ' (Diskon ' . format_rupiah($prod['diskon']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 col-5">
                            <label for="product-qty" class="form-label small fw-bold mb-1">Qty</label>
                            <input type="number" id="product-qty" class="form-control form-control-sm" value="1" min="1">
                        </div>
                        <div class="col-md-3 col-7">
                            <button type="button" id="btn-add-to-cart" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-plus-lg me-1"></i> Tambah
                            </button>
                        </div>
                    </div>

                    <!-- Tabel Item Belanja -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Diskon</th>
                                    <th class="text-center" style="width: 120px;">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-center" style="width: 60px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cart-table-body">
                                <tr id="empty-cart-row">
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Belum ada produk di daftar belanja. Pilih produk di atas lalu klik <strong>Tambah</strong>.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Donasi & Pembayaran -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-cash-coin me-1 text-success"></i> Donasi &amp; Pembayaran
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="donation-select" class="form-label small fw-bold">Program Donasi (Opsional)</label>
                        <select name="id_donasi" id="donation-select" class="form-select form-select-sm">
                            <option value="" data-price="0">-- Tidak Berdonasi --</option>
                            <?php foreach ($donations_arr as $d): ?>
                                <option value="<?= $d['id_donasi'] ?>" data-price="<?= (int)$d['harga_donasi'] ?>">
                                    <?= htmlspecialchars($d['nama_donasi']) ?> (+<?= format_rupiah($d['harga_donasi']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <table class="table table-sm table-borderless border-top border-bottom mb-3">
                        <tr>
                            <td class="text-muted">Subtotal Produk</td>
                            <td class="text-end fw-semibold" id="subtotal-display">Rp 0</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Donasi</td>
                            <td class="text-end fw-semibold text-danger" id="donation-display">Rp 0</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold fs-6">Total Bayar</td>
                            <td class="text-end fw-bold fs-5 text-primary" id="total-display">Rp 0</td>
                        </tr>
                    </table>

                    <input type="hidden" name="total" id="hidden-total" value="0">
                    <input type="hidden" name="kembalian" id="hidden-change" value="0">

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label for="cash-input" class="form-label small fw-bold mb-1">Bayar Tunai (Rp)</label>
                            <input type="number" name="tunai" id="cash-input" class="form-control" placeholder="0" min="0" value="0">
                        </div>
                        <div class="col-6">
                            <label for="qris-input" class="form-label small fw-bold mb-1">Bayar QRIS (Rp)</label>
                            <input type="number" name="QRIS" id="qris-input" class="form-control" placeholder="0" min="0" value="0">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <button type="button" id="btn-tunai-pas" class="btn btn-sm btn-outline-secondary">Tunai Uang Pas</button>
                        <button type="button" id="btn-qris-pas" class="btn btn-sm btn-outline-secondary">QRIS Pas</button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-2 bg-light border rounded mb-3">
                        <span class="small fw-bold text-muted">Kembalian:</span>
                        <span id="change-display" class="fw-bold text-muted">Rp 0</span>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" id="btn-submit-trx" class="btn btn-success fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Simpan Transaksi
                        </button>
                        <a href="<?= BASE_URL ?>/pages/transactions/list.php" class="btn btn-secondary btn-sm">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
