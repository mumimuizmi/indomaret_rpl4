<?php
/**
 * Konfigurasi Database & Helper Sistem
 * Indomaret RPL 4
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Database
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "indomaret_rpl4";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("<div style='font-family: Arial; padding: 20px; background: #fee2e2; color: #991b1b; border: 1px solid #f87171; border-radius: 8px;'>
        <h3>Koneksi Database Gagal!</h3>
        <p>Pastikan MySQL di XAMPP sudah berjalan dan database <strong>indomaret_rpl4</strong> sudah dibuat.</p>
        <p><em>Pesan Error: " . htmlspecialchars(mysqli_connect_error()) . "</em></p>
    </div>");
}

// Set karakter encoding
mysqli_set_charset($conn, "utf8mb4");

// Deteksi dinamis Base URL & Root Path
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$targetFolder = '/indomaret_RPL4';
$pos = stripos($scriptName, $targetFolder);

if ($pos !== false) {
    $subDir = substr($scriptName, 0, $pos + strlen($targetFolder));
} else {
    // Jika diakses langsung via root server PHP bawaan (php -S localhost:8000)
    $subDir = '';
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_url = rtrim($protocol . "://" . $host . $subDir, '/');

if (!defined('BASE_URL')) {
    define('BASE_URL', $base_url);
}

// Helper: Format Rupiah
function format_rupiah($angka) {
    return "Rp " . number_format((float)$angka, 0, ',', '.');
}

// Helper: Format Tanggal Indonesia
function format_datetime($dt) {
    if (!$dt) return "-";
    $time = strtotime($dt);
    return date("d/m/Y H:i", $time);
}

// Helper: Flash Message
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Helper: Sanitasi Input
function escape($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data ?? ''));
}
