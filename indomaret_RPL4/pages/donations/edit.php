<?php
$page_title = "Edit Donasi";
require_once __DIR__ . '/../../config/config.php';

$id_donasi = (int)($_GET['id'] ?? 0);
$q = mysqli_query($conn, "SELECT * FROM tabel_donasi WHERE id_donasi = $id_donasi");
$donasi = mysqli_fetch_assoc($q);

if (!$donasi) {
    set_flash('danger', 'Data program donasi tidak ditemukan!');
    header("Location: " . BASE_URL . "/pages/donations/list.php");
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-pencil-square me-1 text-warning"></i> Form Edit Donasi</span>
                <span class="badge bg-secondary">ID #<?= $donasi['id_donasi'] ?></span>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/process/donations_process.php?action=edit" method="POST">
                    <input type="hidden" name="id_donasi" value="<?= $donasi['id_donasi'] ?>">

                    <div class="mb-3">
                        <label for="nama_donasi" class="form-label fw-bold">Nama Donasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_donasi" name="nama_donasi" maxlength="50" value="<?= htmlspecialchars($donasi['nama_donasi']) ?>" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="harga_donasi" class="form-label fw-bold">Nominal / Harga Donasi (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="harga_donasi" name="harga_donasi" value="<?= (int)$donasi['harga_donasi'] ?>" min="0" required>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <a href="<?= BASE_URL ?>/pages/donations/list.php" class="btn btn-secondary">Batal</a>
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
