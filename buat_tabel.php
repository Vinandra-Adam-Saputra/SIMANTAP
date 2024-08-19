<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pmd_bintim";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tahun = $_POST['tahun'];
    $kolom = $_POST['kolom'];

    // Validasi tahun
    if ($tahun < 2024) {
        echo "Error: Tahun harus 2024 atau lebih besar.";
        exit;
    }

    // Buat nama tabel
    $tableName = "pembangunan_" . $tahun;

    // Buat query SQL untuk membuat tabel
    $sql = "CREATE TABLE $tableName (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        no INT(11) NOT NULL,
        ";

    foreach ($kolom as $index => $namaKolom) {
        $namaKolom = preg_replace('/[^a-zA-Z0-9_]/', '_', $namaKolom);
        $sql .= "`$namaKolom` VARCHAR(255) NOT NULL";
        if ($index < count($kolom) - 1) {
            $sql .= ",";
        }
    }

    $sql .= ")";

    if ($conn->query($sql) === TRUE) {
        echo "success";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    $conn->close();
    exit;
}
?>
