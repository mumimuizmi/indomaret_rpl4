<?php
/**
 * Proses CRUD Produk (Insert, Update, Delete)
 * Indomaret RPL 4
 */

require_once __DIR__ . '/../config/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = escape($conn, $_POST['nama_produk'] ?? '');
    $harga       = (int)($_POST['harga'] ?? 0);
    $diskon      = (int)($_POST['diskon'] ?? 0);

    if (empty($nama_produk) || $harga < 0 || $diskon < 0) {
        set_flash('danger', 'Nama produk tidak boleh kosong, harga dan diskon harus valid!');
        header("Location: " . BASE_URL . "/pages/products/add.php");
        exit;
    }

    if ($diskon > $harga) {
        set_flash('danger', 'Diskon tidak boleh lebih besar dari harga pokok!');
        header("Location: " . BASE_URL . "/pages/products/add.php");
        exit;
    }

    $sql = "INSERT INTO tabel_produk (nama_produk, harga, diskon) VALUES ('$nama_produk', $harga, $diskon)";
    if (mysqli_query($conn, $sql)) {
        set_flash('success', 'Produk "' . $nama_produk . '" berhasil ditambahkan.');
    } else {
        set_flash('danger', 'Gagal menambahkan produk: ' . mysqli_error($conn));
    }

    header("Location: " . BASE_URL . "/pages/products/list.php");
    exit;
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk   = (int)($_POST['id_produk'] ?? 0);
    $nama_produk = escape($conn, $_POST['nama_produk'] ?? '');
    $harga       = (int)($_POST['harga'] ?? 0);
    $diskon      = (int)($_POST['diskon'] ?? 0);

    if ($id_produk <= 0 || empty($nama_produk) || $harga < 0 || $diskon < 0) {
        set_flash('danger', 'Data produk tidak valid!');
        header("Location: " . BASE_URL . "/pages/products/list.php");
        exit;
    }

    if ($diskon > $harga) {
        set_flash('danger', 'Diskon tidak boleh lebih besar dari harga pokok!');
        header("Location: " . BASE_URL . "/pages/products/edit.php?id=" . $id_produk);
        exit;
    }

    $sql = "UPDATE tabel_produk SET nama_produk = '$nama_produk', harga = $harga, diskon = $diskon WHERE id_produk = $id_produk";
    if (mysqli_query($conn, $sql)) {
        set_flash('success', 'Produk "' . $nama_produk . '" berhasil diperbarui.');
    } else {
        set_flash('danger', 'Gagal memperbarui produk: ' . mysqli_error($conn));
    }

    header("Location: " . BASE_URL . "/pages/products/list.php");
    exit;
}

if ($action === 'delete') {
    $id_produk = (int)($_GET['id_produk'] ?? $_POST['id_produk'] ?? 0);

    if ($id_produk > 0) {
        // Cek apakah produk ini ada di riwayat detail transaksi
        $check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tabel_detail_transaksi WHERE id_produk = $id_produk");
        $row = mysqli_fetch_assoc($check);
        if ($row && $row['cnt'] > 0) {
            set_flash('warning', 'Produk tidak dapat dihapus karena telah tercatat dalam riwayat ' . $row['cnt'] . ' transaksi!');
        } else {
            $sql = "DELETE FROM tabel_produk WHERE id_produk = $id_produk";
            if (mysqli_query($conn, $sql)) {
                set_flash('success', 'Produk berhasil dihapus.');
            } else {
                set_flash('danger', 'Gagal menghapus produk: ' . mysqli_error($conn));
            }
        }
    } else {
        set_flash('danger', 'ID produk tidak valid!');
    }

    header("Location: " . BASE_URL . "/pages/products/list.php");
    exit;
}

header("Location: " . BASE_URL . "/pages/products/list.php");
exit;
