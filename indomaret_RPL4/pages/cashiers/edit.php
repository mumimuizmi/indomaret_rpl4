<?php
$page_title = "Edit Data Kasir";
require_once __DIR__ . '/../../config/config.php';

$id_kasir = (int)($_GET['id'] ?? 0);
$q = mysqli_query($conn, "SELECT * FROM tabel_kasir WHERE id_kasir = $id_kasir");
$kasir = mysqli_fetch_assoc($q);

if (!$kasir) {
    set_flash('danger', 'Data kasir tidak ditemukan!');
    header("Location: " . BASE_URL . "/pages/cashiers/list.php");
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pencil-square me-1 text-warning"></i> Form Edit Kasir</span>
                <span class="badge bg-secondary">ID #<?= $kasir['id_kasir'] ?></span>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/process/cashiers_process.php?action=edit" method="POST">
                    <input type="hidden" name="id_kasir" value="<?= $kasir['id_kasir'] ?>">

                    <div class="mb-3">
                        <label for="nama_kasir" class="form-label fw-bold">Nama Kasir <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kasir" name="nama_kasir" maxlength="50" value="<?= htmlspecialchars($kasir['nama_kasir']) ?>" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="no_telp" class="form-label fw-bold">No. Telepon / WA</label>
                        <input type="text" class="form-control" id="no_telp" name="no_telp" maxlength="20" value="<?= htmlspecialchars($kasir['no_telp'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="alamat" class="form-label fw-bold">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($kasir['alamat'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <a href="<?= BASE_URL ?>/pages/cashiers/list.php" class="btn btn-secondary">Batal</a>
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
