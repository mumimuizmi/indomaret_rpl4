<?php
/**
 * Proses CRUD Kasir (Insert, Update, Delete)
 * Indomaret RPL 4
 */

require_once __DIR__ . '/../config/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kasir = escape($conn, $_POST['nama_kasir'] ?? '');
    $no_telp    = escape($conn, $_POST['no_telp'] ?? '');
    $alamat     = escape($conn, $_POST['alamat'] ?? '');

    if (empty($nama_kasir)) {
        set_flash('danger', 'Nama kasir tidak boleh kosong!');
        header("Location: " . BASE_URL . "/pages/cashiers/add.php");
        exit;
    }

    $sql = "INSERT INTO tabel_kasir (nama_kasir, no_telp, alamat) VALUES ('$nama_kasir', '$no_telp', '$alamat')";
    if (mysqli_query($conn, $sql)) {
        set_flash('success', 'Data kasir "' . $nama_kasir . '" berhasil ditambahkan.');
    } else {
        set_flash('danger', 'Gagal menambahkan kasir: ' . mysqli_error($conn));
    }

    header("Location: " . BASE_URL . "/pages/cashiers/list.php");
    exit;
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kasir   = (int)($_POST['id_kasir'] ?? 0);
    $nama_kasir = escape($conn, $_POST['nama_kasir'] ?? '');
    $no_telp    = escape($conn, $_POST['no_telp'] ?? '');
    $alamat     = escape($conn, $_POST['alamat'] ?? '');

    if ($id_kasir <= 0 || empty($nama_kasir)) {
        set_flash('danger', 'ID kasir tidak valid atau nama kasir kosong!');
        header("Location: " . BASE_URL . "/pages/cashiers/list.php");
        exit;
    }

    $sql = "UPDATE tabel_kasir SET nama_kasir = '$nama_kasir', no_telp = '$no_telp', alamat = '$alamat' WHERE id_kasir = $id_kasir";
    if (mysqli_query($conn, $sql)) {
        set_flash('success', 'Data kasir "' . $nama_kasir . '" berhasil diperbarui.');
    } else {
        set_flash('danger', 'Gagal memperbarui kasir: ' . mysqli_error($conn));
    }

    header("Location: " . BASE_URL . "/pages/cashiers/list.php");
    exit;
}

if ($action === 'delete') {
    $id_kasir = (int)($_GET['id_kasir'] ?? $_POST['id_kasir'] ?? 0);

    if ($id_kasir > 0) {
        // Cek apakah kasir ini ada di riwayat detail transaksi
        $check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tabel_detail_transaksi WHERE id_kasir = $id_kasir");
        $row = mysqli_fetch_assoc($check);
        if ($row && $row['cnt'] > 0) {
            set_flash('warning', 'Kasir tidak dapat dihapus karena tercatat dalam ' . $row['cnt'] . ' data transaksi!');
        } else {
            $sql = "DELETE FROM tabel_kasir WHERE id_kasir = $id_kasir";
            if (mysqli_query($conn, $sql)) {
                set_flash('success', 'Data kasir berhasil dihapus.');
            } else {
                set_flash('danger', 'Gagal menghapus kasir: ' . mysqli_error($conn));
            }
        }
    } else {
        set_flash('danger', 'ID kasir tidak valid!');
    }

    header("Location: " . BASE_URL . "/pages/cashiers/list.php");
    exit;
}

// Default redirect jika akses tanpa aksi
header("Location: " . BASE_URL . "/pages/cashiers/list.php");
exit;
