<?php
/**
 * File Utama Aplikasi (Entry Point)
 * Indomaret RPL 4
 */

require_once __DIR__ . '/config/config.php';

// Redirect langsung ke halaman dashboard utama
header("Location: " . BASE_URL . "/pages/dashboard.php");
exit;
