<?php
$page_title = "Edit Produk";
require_once __DIR__ . '/../../config/config.php';

$id_produk = (int)($_GET['id'] ?? 0);
$q = mysqli_query($conn, "SELECT * FROM tabel_produk WHERE id_produk = $id_produk");
$produk = mysqli_fetch_assoc($q);

if (!$produk) {
    set_flash('danger', 'Data produk tidak ditemukan!');
    header("Location: " . BASE_URL . "/pages/products/list.php");
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pencil-square me-1 text-warning"></i> Form Edit Produk</span>
                <span class="badge bg-secondary">ID #<?= $produk['id_produk'] ?></span>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/process/products_process.php?action=edit" method="POST">
                    <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">

                    <div class="mb-3">
                        <label for="nama_produk" class="form-label fw-bold">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" maxlength="50" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required autofocus>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="harga" class="form-label fw-bold">Harga Pokok (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="harga" name="harga" value="<?= (int)$produk['harga'] ?>" min="0" required>
                        </div>

                        <div class="col-md-6">
                            <label for="diskon" class="form-label fw-bold">Diskon (Rp)</label>
                            <input type="number" class="form-control" id="diskon" name="diskon" value="<?= (int)$produk['diskon'] ?>" min="0">
                            <small class="text-muted">Isi 0 jika tidak ada promo diskon.</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <a href="<?= BASE_URL ?>/pages/products/list.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
