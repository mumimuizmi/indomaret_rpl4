<?php
$page_title = "Tambah Kasir";
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-person-plus me-1 text-primary"></i> Form Tambah Kasir</span>
                <a href="<?= BASE_URL ?>/pages/cashiers/list.php" class="btn btn-sm btn-outline-secondary py-0 px-2">
                    Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/process/cashiers_process.php?action=add" method="POST">
                    <div class="mb-3">
                        <label for="nama_kasir" class="form-label fw-bold">Nama Kasir <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kasir" name="nama_kasir" maxlength="50" placeholder="Contoh: Budi Santoso" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="no_telp" class="form-label fw-bold">No. Telepon / WA</label>
                        <input type="text" class="form-control" id="no_telp" name="no_telp" maxlength="20" placeholder="Contoh: 08123456789">
                    </div>

                    <div class="mb-3">
                        <label for="alamat" class="form-label fw-bold">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Alamat domisili kasir..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <a href="<?= BASE_URL ?>/pages/cashiers/list.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Kasir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
