<?php
$page_title = "Detail Transaksi & Struk";
require_once __DIR__ . '/../../config/config.php';

$kode_transaksi = escape($conn, $_GET['kode'] ?? '');
$is_print = isset($_GET['print']) && $_GET['print'] == '1';

$trx = null;
$detail_items = [];
$nama_kasir_utama = "-";

if (!empty($kode_transaksi)) {
    $qTrx = mysqli_query($conn, "
        SELECT t.*, d.nama_donasi, d.harga_donasi
        FROM tabel_transaksi t
        LEFT JOIN tabel_donasi d ON t.id_donasi = d.id_donasi
        WHERE t.kode_transaksi = '$kode_transaksi'
    ");
    $trx = mysqli_fetch_assoc($qTrx);

    if ($trx) {
        $qDetail = mysqli_query($conn, "
            SELECT dt.*, p.nama_produk, k.nama_kasir
            FROM tabel_detail_transaksi dt
            LEFT JOIN tabel_produk p ON dt.id_produk = p.id_produk
            LEFT JOIN tabel_kasir k ON dt.id_kasir = k.id_kasir
            WHERE dt.kode_transaksi = '$kode_transaksi'
        ");
        while ($d = mysqli_fetch_assoc($qDetail)) {
            $detail_items[] = $d;
            if ($nama_kasir_utama === "-" && !empty($d['nama_kasir'])) {
                $nama_kasir_utama = $d['nama_kasir'];
            }
        }
    }
}

$all_trx = mysqli_query($conn, "SELECT kode_transaksi, no_order, total, tanggal_transaksi FROM tabel_transaksi ORDER BY tanggal_transaksi DESC");
$products_opt = mysqli_query($conn, "SELECT * FROM tabel_produk ORDER BY nama_produk ASC");
$cashiers_opt = mysqli_query($conn, "SELECT * FROM tabel_kasir ORDER BY nama_kasir ASC");

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
        <h4 class="fw-bold mb-1">Detail Transaksi</h4>
        <p class="text-muted small mb-0">Rincian item belanja, form tambah produk transaksi, dan struk sederhana.</p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($trx): ?>
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="bi bi-printer me-1"></i> Cetak Struk
            </button>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/pages/transactions/list.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<!-- Pilih Transaksi jika kode belum dipilih atau tidak ditemukan -->
<?php if (!$trx): ?>
    <?php if (!empty($kode_transaksi)): ?>
        <div class="alert alert-danger no-print">
            Transaksi dengan kode <strong><?= htmlspecialchars($kode_transaksi) ?></strong> tidak ditemukan.
        </div>
    <?php endif; ?>

    <div class="card no-print">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-search me-1 text-primary"></i> Pilih Transaksi
        </div>
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/pages/transactions/transaction_details.php" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <select name="kode" class="form-select" required>
                        <option value="">-- Pilih Kode Transaksi --</option>
                        <?php while ($t = mysqli_fetch_assoc($all_trx)): ?>
                            <option value="<?= htmlspecialchars($t['kode_transaksi']) ?>">
                                <?= htmlspecialchars($t['kode_transaksi']) ?> | Order #<?= $t['no_order'] ?> | <?= format_rupiah($t['total']) ?> (<?= format_datetime($t['tanggal_transaksi']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Tampilkan Detail</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($trx): ?>
    <?php
    $subtotal_items = 0;
    $total_hemat = 0;
    foreach ($detail_items as $it) {
        $subtotal_items += (float)$it['harga_jual'];
        $total_hemat += ($it['diskon'] * $it['kuantitas']);
    }
    ?>
    <div class="row g-4">
        <!-- Kolom Kiri: Info Transaksi, Form Input Produk, Tabel Item -->
        <div class="col-lg-7 no-print">
            <!-- Ringkasan Transaksi -->
            <div class="card mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-info-circle me-1 text-primary"></i> Informasi Transaksi</span>
                    <span class="badge bg-primary"><?= htmlspecialchars($trx['kode_transaksi']) ?></span>
                </div>
                <div class="card-body">
                    <div class="row g-2 small">
                        <div class="col-sm-6">
                            <span class="text-muted">Tanggal:</span><br>
                            <strong><?= format_datetime($trx['tanggal_transaksi']) ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted">No. Order:</span><br>
                            <strong>#<?= htmlspecialchars($trx['no_order']) ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted">Pembayaran:</span><br>
                            <?php if ($trx['tunai'] > 0): ?>
                                <span class="badge bg-success">Tunai: <?= format_rupiah($trx['tunai']) ?></span>
                            <?php endif; ?>
                            <?php if ($trx['QRIS'] > 0): ?>
                                <span class="badge bg-info text-dark">QRIS: <?= format_rupiah($trx['QRIS']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted">Kembalian:</span><br>
                            <strong class="text-success"><?= format_rupiah($trx['kembalian']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Input Produk untuk Transaksi -->
            <div class="card mb-3">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-plus-circle me-1 text-primary"></i> Form Input Produk untuk Transaksi
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/process/transactions_process.php?action=add_detail_item" method="POST">
                        <input type="hidden" name="kode_transaksi" value="<?= htmlspecialchars($trx['kode_transaksi']) ?>">

                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold mb-1">Produk</label>
                                <select name="id_produk" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Produk --</option>
                                    <?php mysqli_data_seek($products_opt, 0); while ($p = mysqli_fetch_assoc($products_opt)): ?>
                                        <option value="<?= $p['id_produk'] ?>">
                                            <?= htmlspecialchars($p['nama_produk']) ?> (<?= format_rupiah($p['harga']) ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold mb-1">Kasir</label>
                                <select name="id_kasir" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Kasir --</option>
                                    <?php mysqli_data_seek($cashiers_opt, 0); while ($k = mysqli_fetch_assoc($cashiers_opt)): ?>
                                        <option value="<?= $k['id_kasir'] ?>">
                                            <?= htmlspecialchars($k['nama_kasir']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1">Qty</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="kuantitas" class="form-control" value="1" min="1" required>
                                    <button type="submit" class="btn btn-primary">Tambah</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Daftar Item Transaksi -->
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-list-ul me-1 text-primary"></i> Daftar Item Belanja</span>
                    <span class="badge bg-secondary"><?= count($detail_items) ?> item</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th>Kasir</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Diskon</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($detail_items)): ?>
                                    <?php foreach ($detail_items as $item): ?>
                                        <tr>
                                            <td class="fw-semibold"><?= htmlspecialchars($item['nama_produk'] ?? 'Produk #' . $item['id_produk']) ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($item['nama_kasir'] ?? 'Kasir #' . $item['id_kasir']) ?></td>
                                            <td class="text-end"><?= format_rupiah($item['harga']) ?></td>
                                            <td class="text-end"><?= $item['diskon'] > 0 ? format_rupiah($item['diskon']) : '-' ?></td>
                                            <td class="text-center fw-bold"><?= (int)$item['kuantitas'] ?></td>
                                            <td class="text-end fw-bold text-primary"><?= format_rupiah($item['harga_jual']) ?></td>
                                            <td class="text-center">
                                                <a href="<?= BASE_URL ?>/process/transactions_process.php?action=delete_detail_item&kode_transaksi=<?= urlencode($trx['kode_transaksi']) ?>&id_produk=<?= (int)$item['id_produk'] ?>"
                                                   class="btn btn-sm btn-outline-danger py-0 px-2 confirm-delete"
                                                   data-message="Hapus item '<?= htmlspecialchars($item['nama_produk'] ?? '') ?>' dari transaksi ini?"
                                                   title="Hapus Item">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Belum ada item produk dalam transaksi ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Struk Sederhana -->
        <div class="col-lg-5">
            <div class="struk-box">
                <div class="text-center mb-2">
                    <h5>INDOMARET RPL 4</h5>
                    <div style="font-size: 11px;">PT. BIAR GA BERANTEM</div>
                    <div style="font-size: 11px;">Sistem Kasir Sederhana</div>
                </div>

                <div class="struk-divider"></div>

                <div style="font-size: 12px;">
                    <div class="d-flex justify-content-between">
                        <span>TGL   : <?= date('d/m/Y H:i', strtotime($trx['tanggal_transaksi'])) ?></span>
                        <span>ORD #<?= htmlspecialchars($trx['no_order']) ?></span>
                    </div>
                    <div>KASIR : <?= htmlspecialchars($nama_kasir_utama) ?></div>
                    <div>KODE  : <?= htmlspecialchars($trx['kode_transaksi']) ?></div>
                </div>

                <div class="struk-divider"></div>

                <div style="font-size: 12px;">
                    <?php foreach ($detail_items as $it): ?>
                        <div class="fw-bold"><?= htmlspecialchars($it['nama_produk'] ?? 'Produk #' . $it['id_produk']) ?></div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>
                                <?= (int)$it['kuantitas'] ?> x <?= number_format($it['harga'], 0, ',', '.') ?>
                                <?php if ($it['diskon'] > 0): ?>
                                    (Disc -<?= number_format($it['diskon'], 0, ',', '.') ?>)
                                <?php endif; ?>
                            </span>
                            <span><?= number_format($it['harga_jual'], 0, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="struk-divider"></div>

                <div style="font-size: 12px;">
                    <div class="d-flex justify-content-between">
                        <span>SUBTOTAL (<?= count($detail_items) ?> item)</span>
                        <span><?= number_format($subtotal_items, 0, ',', '.') ?></span>
                    </div>

                    <?php if (!empty($trx['nama_donasi'])): ?>
                        <div class="d-flex justify-content-between">
                            <span>DONASI (<?= htmlspecialchars($trx['nama_donasi']) ?>)</span>
                            <span><?= number_format($trx['harga_donasi'], 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($total_hemat > 0): ?>
                        <div class="d-flex justify-content-between">
                            <span>ANDA HEMAT</span>
                            <span><?= number_format($total_hemat, 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="struk-divider"></div>

                    <div class="d-flex justify-content-between fw-bold" style="font-size: 14px;">
                        <span>TOTAL</span>
                        <span>Rp <?= number_format($trx['total'], 0, ',', '.') ?></span>
                    </div>

                    <?php if ($trx['tunai'] > 0): ?>
                        <div class="d-flex justify-content-between">
                            <span>TUNAI</span>
                            <span><?= number_format($trx['tunai'], 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($trx['QRIS'] > 0): ?>
                        <div class="d-flex justify-content-between">
                            <span>QRIS</span>
                            <span><?= number_format($trx['QRIS'], 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between fw-bold">
                        <span>KEMBALIAN</span>
                        <span><?= number_format($trx['kembalian'], 0, ',', '.') ?></span>
                    </div>
                </div>

                <div class="struk-divider"></div>

                <div class="text-center" style="font-size: 11px;">
                    <div>TERIMA KASIH</div>
                    <div>SELAMAT BELANJA KEMBALI</div>
                </div>
            </div>

            <div class="text-center mt-3 no-print">
                <button type="button" onclick="window.print()" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-printer me-1"></i> Cetak Struk
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($is_print && $trx): ?>
<script>
    window.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            window.print();
        }, 400);
    });
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
