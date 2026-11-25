<?php
$page_title = "Tambah Donasi";
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-heart-fill me-1 text-danger"></i> Form Tambah Donasi</span>
                <a href="<?= BASE_URL ?>/pages/donations/list.php" class="btn btn-sm btn-outline-secondary py-0 px-2">
                    Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/process/donations_process.php?action=add" method="POST">
                    <div class="mb-3">
                        <label for="nama_donasi" class="form-label fw-bold">Nama Donasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_donasi" name="nama_donasi" maxlength="50" placeholder="Contoh: Donasi Peduli Yatim" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="harga_donasi" class="form-label fw-bold">Nominal / Harga Donasi (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="harga_donasi" name="harga_donasi" placeholder="Contoh: 500" min="0" required>
                        <small class="text-muted">Nominal donasi yang akan ditambahkan ke struk jika dipilih konsumen.</small>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <a href="<?= BASE_URL ?>/pages/donations/list.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Donasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
