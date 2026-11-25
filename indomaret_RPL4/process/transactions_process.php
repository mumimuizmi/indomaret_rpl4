<?php
/**
 * Proses CRUD Transaksi & Detail Transaksi
 * Indomaret RPL 4
 */

require_once __DIR__ . '/../config/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

/**
 * Hitung ulang total & kembalian transaksi berdasarkan detail item + donasi
 */
function recalculate_transaction_totals($conn, $kode_transaksi) {
    $qSum = mysqli_query($conn, "SELECT COALESCE(SUM(harga_jual), 0) as sum_items FROM tabel_detail_transaksi WHERE kode_transaksi = '$kode_transaksi'");
    $rSum = mysqli_fetch_assoc($qSum);
    $sum_items = (float)($rSum['sum_items'] ?? 0);

    $qTrx = mysqli_query($conn, "SELECT id_donasi, tunai, QRIS FROM tabel_transaksi WHERE kode_transaksi = '$kode_transaksi'");
    $rTrx = mysqli_fetch_assoc($qTrx);
    if (!$rTrx) return;

    $donasi_val = 0;
    if (!empty($rTrx['id_donasi'])) {
        $qD = mysqli_query($conn, "SELECT harga_donasi FROM tabel_donasi WHERE id_donasi = " . (int)$rTrx['id_donasi']);
        if ($rD = mysqli_fetch_assoc($qD)) {
            $donasi_val = (float)$rD['harga_donasi'];
        }
    }

    $new_total = $sum_items + $donasi_val;
    $total_bayar = (float)($rTrx['tunai'] + $rTrx['QRIS']);
    $new_kembalian = (int)max(0, $total_bayar - $new_total);

    if (!mysqli_query($conn, "UPDATE tabel_transaksi SET total = $new_total, kembalian = $new_kembalian WHERE kode_transaksi = '$kode_transaksi'")) {
        throw new Exception("Gagal memperbarui total transaksi: " . mysqli_error($conn));
    }
}

// Proses Simpan Transaksi Lengkap
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_transaksi    = escape($conn, $_POST['kode_transaksi'] ?? '');
    $tanggal_transaksi = escape($conn, $_POST['tanggal_transaksi'] ?? date('Y-m-d H:i:s'));
    $no_order          = (int)($_POST['no_order'] ?? 1);
    $id_kasir          = (int)($_POST['id_kasir'] ?? 0);
    $id_donasi         = !empty($_POST['id_donasi']) ? (int)$_POST['id_donasi'] : null;
    $tunai             = max(0, (int)($_POST['tunai'] ?? 0));
    $qris              = max(0, (float)($_POST['QRIS'] ?? 0));
    $products          = $_POST['products'] ?? [];

    if (empty($kode_transaksi)) {
        $kode_transaksi = 'TRX' . date('YmdHis');
    }

    if ($id_kasir <= 0) {
        set_flash('danger', 'Silakan pilih kasir yang bertugas!');
        header("Location: " . BASE_URL . "/pages/transactions/add.php");
        exit;
    }

    if (empty($products) || !is_array($products)) {
        set_flash('danger', 'Daftar belanja tidak boleh kosong!');
        header("Location: " . BASE_URL . "/pages/transactions/add.php");
        exit;
    }

    // Ambil nominal donasi jika dipilih
    $harga_donasi = 0;
    if ($id_donasi !== null && $id_donasi > 0) {
        $qDonasi = mysqli_query($conn, "SELECT harga_donasi FROM tabel_donasi WHERE id_donasi = $id_donasi");
        if ($rDonasi = mysqli_fetch_assoc($qDonasi)) {
            $harga_donasi = (int)$rDonasi['harga_donasi'];
        } else {
            $id_donasi = null;
        }
    }

    // Hitung total harga item belanja dari data harga resmi DB
    $total_item_jual = 0;
    $items_to_save = [];

    foreach ($products as $prod) {
        $id_produk = (int)($prod['id_produk'] ?? 0);
        $qty       = (int)($prod['kuantitas'] ?? 1);
        if ($id_produk <= 0 || $qty <= 0) continue;

        $qProd = mysqli_query($conn, "SELECT harga, diskon FROM tabel_produk WHERE id_produk = $id_produk");
        if ($rProd = mysqli_fetch_assoc($qProd)) {
            $harga      = (int)$rProd['harga'];
            $diskon     = (int)$rProd['diskon'];
            $harga_jual = max(0, ($harga - $diskon) * $qty);

            $total_item_jual += $harga_jual;

            $items_to_save[] = [
                'id_produk'  => $id_produk,
                'harga'      => $harga,
                'kuantitas'  => $qty,
                'diskon'     => $diskon,
                'harga_jual' => $harga_jual
            ];
        }
    }

    if (empty($items_to_save)) {
        set_flash('danger', 'Tidak ada item produk valid yang dipilih!');
        header("Location: " . BASE_URL . "/pages/transactions/add.php");
        exit;
    }

    $grand_total = (float)($total_item_jual + $harga_donasi);
    $total_bayar = (float)($tunai + $qris);

    if ($total_bayar < $grand_total) {
        set_flash('danger', 'Jumlah pembayaran kurang dari total belanja! Total: ' . format_rupiah($grand_total) . ', Pembayaran: ' . format_rupiah($total_bayar));
        header("Location: " . BASE_URL . "/pages/transactions/add.php");
        exit;
    }

    $kembalian = (int)($total_bayar - $grand_total);

    mysqli_begin_transaction($conn);

    try {
        $id_donasi_sql = $id_donasi !== null ? $id_donasi : "NULL";
        $sqlTrx = "INSERT INTO tabel_transaksi
                   (kode_transaksi, tanggal_transaksi, no_order, total, QRIS, tunai, kembalian, id_donasi)
                   VALUES
                   ('$kode_transaksi', '$tanggal_transaksi', $no_order, $grand_total, $qris, $tunai, $kembalian, $id_donasi_sql)";

        if (!mysqli_query($conn, $sqlTrx)) {
            throw new Exception("Gagal menyimpan transaksi: " . mysqli_error($conn));
        }

        foreach ($items_to_save as $item) {
            $id_p   = $item['id_produk'];
            $hrg    = $item['harga'];
            $qty    = $item['kuantitas'];
            $dsk    = $item['diskon'];
            $hrg_jl = $item['harga_jual'];

            $sqlDetail = "INSERT INTO tabel_detail_transaksi
                          (kode_transaksi, id_produk, id_kasir, harga, kuantitas, diskon, harga_jual)
                          VALUES
                          ('$kode_transaksi', $id_p, $id_kasir, $hrg, $qty, $dsk, $hrg_jl)";

            if (!mysqli_query($conn, $sqlDetail)) {
                throw new Exception("Gagal menyimpan detail produk: " . mysqli_error($conn));
            }
        }

        mysqli_commit($conn);

        set_flash('success', "Transaksi $kode_transaksi berhasil disimpan!");
        header("Location: " . BASE_URL . "/pages/transactions/transaction_details.php?kode=" . urlencode($kode_transaksi));
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        set_flash('danger', 'Error saat memproses transaksi: ' . $e->getMessage());
        header("Location: " . BASE_URL . "/pages/transactions/add.php");
        exit;
    }
}

// Tambah produk ke transaksi yang sudah ada via transaction_details.php
if ($action === 'add_detail_item' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_transaksi = escape($conn, $_POST['kode_transaksi'] ?? '');
    $id_produk      = (int)($_POST['id_produk'] ?? 0);
    $id_kasir       = (int)($_POST['id_kasir'] ?? 0);
    $kuantitas      = (int)($_POST['kuantitas'] ?? 1);

    if (empty($kode_transaksi) || $id_produk <= 0 || $id_kasir <= 0 || $kuantitas <= 0) {
        set_flash('danger', 'Data input produk untuk transaksi tidak lengkap!');
        header("Location: " . BASE_URL . "/pages/transactions/transaction_details.php?kode=" . urlencode($kode_transaksi));
        exit;
    }

    // Pastikan transaksi ada
    $qTrxCheck = mysqli_query($conn, "SELECT kode_transaksi FROM tabel_transaksi WHERE kode_transaksi = '$kode_transaksi'");
    if (!mysqli_fetch_assoc($qTrxCheck)) {
        set_flash('danger', 'Transaksi tidak ditemukan!');
        header("Location: " . BASE_URL . "/pages/transactions/list.php");
        exit;
    }

    $qProd = mysqli_query($conn, "SELECT harga, diskon FROM tabel_produk WHERE id_produk = $id_produk");
    if (!$rProd = mysqli_fetch_assoc($qProd)) {
        set_flash('danger', 'Produk tidak ditemukan!');
        header("Location: " . BASE_URL . "/pages/transactions/transaction_details.php?kode=" . urlencode($kode_transaksi));
        exit;
    }

    $harga      = (int)$rProd['harga'];
    $diskon     = (int)$rProd['diskon'];
    $harga_jual = max(0, ($harga - $diskon) * $kuantitas);

    mysqli_begin_transaction($conn);
    try {
        $qCheck = mysqli_query($conn, "SELECT kuantitas FROM tabel_detail_transaksi WHERE kode_transaksi = '$kode_transaksi' AND id_produk = $id_produk");
        if ($rCheck = mysqli_fetch_assoc($qCheck)) {
            $new_qty = (int)$rCheck['kuantitas'] + $kuantitas;
            $new_harga_jual = max(0, ($harga - $diskon) * $new_qty);
            $sqlUp = "UPDATE tabel_detail_transaksi
                      SET kuantitas = $new_qty, harga = $harga, diskon = $diskon, harga_jual = $new_harga_jual, id_kasir = $id_kasir
                      WHERE kode_transaksi = '$kode_transaksi' AND id_produk = $id_produk";
            if (!mysqli_query($conn, $sqlUp)) {
                throw new Exception(mysqli_error($conn));
            }
        } else {
            $sqlIn = "INSERT INTO tabel_detail_transaksi (kode_transaksi, id_produk, id_kasir, harga, kuantitas, diskon, harga_jual)
                      VALUES ('$kode_transaksi', $id_produk, $id_kasir, $harga, $kuantitas, $diskon, $harga_jual)";
            if (!mysqli_query($conn, $sqlIn)) {
                throw new Exception(mysqli_error($conn));
            }
        }

        recalculate_transaction_totals($conn, $kode_transaksi);

        mysqli_commit($conn);
        set_flash('success', 'Produk berhasil ditambahkan ke transaksi.');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        set_flash('danger', 'Gagal menambahkan produk: ' . $e->getMessage());
    }

    header("Location: " . BASE_URL . "/pages/transactions/transaction_details.php?kode=" . urlencode($kode_transaksi));
    exit;
}

// Hapus 1 item produk dari detail transaksi
if ($action === 'delete_detail_item') {
    $kode_transaksi = escape($conn, $_GET['kode_transaksi'] ?? $_POST['kode_transaksi'] ?? '');
    $id_produk      = (int)($_GET['id_produk'] ?? $_POST['id_produk'] ?? 0);

    if (!empty($kode_transaksi) && $id_produk > 0) {
        mysqli_begin_transaction($conn);
        try {
            if (!mysqli_query($conn, "DELETE FROM tabel_detail_transaksi WHERE kode_transaksi = '$kode_transaksi' AND id_produk = $id_produk")) {
                throw new Exception(mysqli_error($conn));
            }
            recalculate_transaction_totals($conn, $kode_transaksi);
            mysqli_commit($conn);
            set_flash('success', 'Item produk berhasil dihapus dari transaksi.');
        } catch (Exception $e) {
            mysqli_rollback($conn);
            set_flash('danger', 'Gagal menghapus item: ' . $e->getMessage());
        }
    } else {
        set_flash('danger', 'Parameter item transaksi tidak valid!');
    }

    header("Location: " . BASE_URL . "/pages/transactions/transaction_details.php?kode=" . urlencode($kode_transaksi));
    exit;
}

// Hapus Transaksi
if ($action === 'delete') {
    $kode_transaksi = escape($conn, $_GET['kode_transaksi'] ?? $_POST['kode_transaksi'] ?? '');

    if (!empty($kode_transaksi)) {
        mysqli_begin_transaction($conn);
        try {
            mysqli_query($conn, "DELETE FROM tabel_detail_transaksi WHERE kode_transaksi = '$kode_transaksi'");
            mysqli_query($conn, "DELETE FROM tabel_transaksi WHERE kode_transaksi = '$kode_transaksi'");

            mysqli_commit($conn);
            set_flash('success', "Transaksi $kode_transaksi berhasil dihapus.");
        } catch (Exception $e) {
            mysqli_rollback($conn);
            set_flash('danger', "Gagal menghapus transaksi: " . $e->getMessage());
        }
    } else {
        set_flash('danger', "Kode transaksi tidak valid!");
    }

    header("Location: " . BASE_URL . "/pages/transactions/list.php");
    exit;
}

header("Location: " . BASE_URL . "/pages/transactions/list.php");
exit;
