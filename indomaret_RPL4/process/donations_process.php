<?php
/**
 * Proses CRUD Donasi (Insert, Update, Delete)
 * Indomaret RPL 4
 */

require_once __DIR__ . '/../config/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_donasi  = escape($conn, $_POST['nama_donasi'] ?? '');
    $harga_donasi = (int)($_POST['harga_donasi'] ?? 0);

    if (empty($nama_donasi) || $harga_donasi < 0) {
        set_flash('danger', 'Nama donasi dan nominal donasi harus diisi dengan benar!');
        header("Location: " . BASE_URL . "/pages/donations/add.php");
        exit;
    }

    $sql = "INSERT INTO tabel_donasi (nama_donasi, harga_donasi) VALUES ('$nama_donasi', $harga_donasi)";
    if (mysqli_query($conn, $sql)) {
        set_flash('success', 'Program donasi "' . $nama_donasi . '" berhasil ditambahkan.');
    } else {
        set_flash('danger', 'Gagal menambahkan donasi: ' . mysqli_error($conn));
    }

    header("Location: " . BASE_URL . "/pages/donations/list.php");
    exit;
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_donasi    = (int)($_POST['id_donasi'] ?? 0);
    $nama_donasi  = escape($conn, $_POST['nama_donasi'] ?? '');
    $harga_donasi = (int)($_POST['harga_donasi'] ?? 0);

    if ($id_donasi <= 0 || empty($nama_donasi) || $harga_donasi < 0) {
        set_flash('danger', 'Data donasi tidak valid!');
        header("Location: " . BASE_URL . "/pages/donations/list.php");
        exit;
    }

    $sql = "UPDATE tabel_donasi SET nama_donasi = '$nama_donasi', harga_donasi = $harga_donasi WHERE id_donasi = $id_donasi";
    if (mysqli_query($conn, $sql)) {
        set_flash('success', 'Program donasi "' . $nama_donasi . '" berhasil diperbarui.');
    } else {
        set_flash('danger', 'Gagal memperbarui donasi: ' . mysqli_error($conn));
    }

    header("Location: " . BASE_URL . "/pages/donations/list.php");
    exit;
}

if ($action === 'delete') {
    $id_donasi = (int)($_GET['id_donasi'] ?? $_POST['id_donasi'] ?? 0);

    if ($id_donasi > 0) {
        // Cek apakah donasi ini ada di riwayat transaksi
        $check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tabel_transaksi WHERE id_donasi = $id_donasi");
        $row = mysqli_fetch_assoc($check);
        if ($row && $row['cnt'] > 0) {
            set_flash('warning', 'Donasi tidak dapat dihapus karena tercatat dalam ' . $row['cnt'] . ' transaksi!');
        } else {
            $sql = "DELETE FROM tabel_donasi WHERE id_donasi = $id_donasi";
            if (mysqli_query($conn, $sql)) {
                set_flash('success', 'Program donasi berhasil dihapus.');
            } else {
                set_flash('danger', 'Gagal menghapus donasi: ' . mysqli_error($conn));
            }
        }
    } else {
        set_flash('danger', 'ID donasi tidak valid!');
    }

    header("Location: " . BASE_URL . "/pages/donations/list.php");
    exit;
}

header("Location: " . BASE_URL . "/pages/donations/list.php");
exit;
