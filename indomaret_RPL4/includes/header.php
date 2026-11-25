<?php
if (!defined('BASE_URL')) {
    $config_path = __DIR__ . '/../config/config.php';
    if (file_exists($config_path)) {
        require_once $config_path;
    }
}
$flash = get_flash();
$current_page = $_SERVER['SCRIPT_NAME'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>Indomaret RPL 4</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Simple CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

    <!-- Garis Warna Khas Indomaret -->
    <div class="indo-top-bar no-print">
        <span class="bar-blue"></span>
        <span class="bar-red"></span>
        <span class="bar-yellow"></span>
    </div>

    <!-- Navbar Sederhana -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-indo-blue sticky-top no-print">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="<?= BASE_URL ?>/pages/dashboard.php">
                <img src="<?= BASE_URL ?>/assets/img/logo_indomaret.png" alt="Logo Indomaret" class="navbar-logo">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($current_page, 'dashboard.php') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/pages/dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($current_page, '/cashiers/') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/pages/cashiers/list.php">
                            <i class="bi bi-people me-1"></i> Data Kasir
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($current_page, '/products/') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/pages/products/list.php">
                            <i class="bi bi-box me-1"></i> Data Produk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($current_page, '/donations/') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/pages/donations/list.php">
                            <i class="bi bi-heart me-1"></i> Data Donasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($current_page, '/transactions/list.php') !== false || strpos($current_page, '/transactions/transaction_details.php') !== false) ? 'active' : '' ?>" href="<?= BASE_URL ?>/pages/transactions/list.php">
                            <i class="bi bi-receipt me-1"></i> Riwayat Transaksi
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <a href="<?= BASE_URL ?>/pages/transactions/add.php" class="btn btn-warning fw-bold text-dark btn-sm px-3">
                        <i class="bi bi-cart4 me-1"></i> Transaksi Baru
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <main class="main-content py-4">
        <div class="container">

            <!-- Flash Message Alert Sederhana -->
            <?php if ($flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show no-print mb-4" role="alert">
                    <strong><?= $flash['type'] === 'success' ? 'Sukses!' : 'Perhatian!' ?></strong> <?= htmlspecialchars($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
