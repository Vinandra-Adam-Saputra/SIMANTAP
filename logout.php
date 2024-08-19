<?php
session_start();

if (isset($_POST['confirm_logout']) && $_POST['confirm_logout'] == 'yes') {
    session_destroy();
    echo "success"; // Mengirim respons sukses ke JavaScript
    exit();
} else {
    // Jika tidak ada konfirmasi, kembali ke halaman sebelumnya
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
