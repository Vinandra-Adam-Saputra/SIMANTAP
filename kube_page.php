<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "simantap";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if(isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'get') {
    $id = $conn->real_escape_string($_GET['id']);
    $sql = "SELECT * FROM kube WHERE id = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Data not found']);
    }
    exit;
}

// Fungsi untuk menambah atau mengedit data
if(isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = isset($_POST['id']) ? $conn->real_escape_string($_POST['id']) : null;
    $kecamatan = $conn->real_escape_string($_POST['kecamatan']);
    $kelurahan_desa = $conn->real_escape_string($_POST['kelurahan_desa']);
    $nama_kube = $conn->real_escape_string($_POST['nama_kube']);
    $nama_produk = $conn->real_escape_string($_POST['nama_produk']);
    $pkb = isset($_POST['pkb']) ? 1 : 0;
    $nib = isset($_POST['nib']) ? 1 : 0;
    $pirt = isset($_POST['pirt']) ? 1 : 0;
    $halal = isset($_POST['halal']) ? 1 : 0;
    $izin_lainnya = isset($_POST['izin_lainnya']) ? 1 : 0;
    $fb = isset($_POST['fb']) ? 1 : 0;
    $ig = isset($_POST['ig']) ? 1 : 0;
    $digital_marketing_lainnya = isset($_POST['digital_marketing_lainnya']) ? 1 : 0;
    $wilayah_pemasaran = $conn->real_escape_string($_POST['wilayah_pemasaran']);
    $ket = $conn->real_escape_string($_POST['ket']);

    if($action == 'tambah') {
        // Dapatkan nomor terakhir
        $sql = "SELECT MAX(no) as max_no FROM kube";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $new_no = $row['max_no'] + 1;

        $sql = "INSERT INTO kube (no, kecamatan, kelurahan_desa, nama_kube, nama_produk, pkb, nib, pirt, halal, izin_lainnya, fb, ig, digital_marketing_lainnya, wilayah_pemasaran, ket) VALUES ($new_no, '$kecamatan', '$kelurahan_desa', '$nama_kube', '$nama_produk', $pkb, $nib, $pirt, $halal, $izin_lainnya, $fb, $ig, $digital_marketing_lainnya, '$wilayah_pemasaran', '$ket')";
    } elseif($action == 'edit') {
        $sql = "UPDATE kube SET kecamatan='$kecamatan', kelurahan_desa='$kelurahan_desa', nama_kube='$nama_kube', nama_produk='$nama_produk', pkb=$pkb, nib=$nib, pirt=$pirt, halal=$halal, izin_lainnya=$izin_lainnya, fb=$fb, ig=$ig, digital_marketing_lainnya=$digital_marketing_lainnya, wilayah_pemasaran='$wilayah_pemasaran', ket='$ket' WHERE id=$id";
    }

    if($conn->query($sql) === TRUE) {
        echo "success";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    exit;
}


// Fungsi untuk menghapus data
if(isset($_GET['hapus'])) {
    $id = $conn->real_escape_string($_GET['hapus']);
    $sql = "DELETE FROM kube WHERE id=$id";
    $conn->query($sql);
}

// Fungsi pencarian dan pagination
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

$sql = "SELECT * FROM kube WHERE 
        kecamatan LIKE '%$search%' OR 
        kelurahan_desa LIKE '%$search%' OR 
        nama_kube LIKE '%$search%' OR 
        nama_produk LIKE '%$search%'";

// Hitung total data untuk pagination
$total_results = $conn->query($sql)->num_rows;
$total_pages = ceil($total_results / $limit);

// Tambahkan LIMIT untuk pagination
$sql .= " LIMIT $start, $limit";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data KUBE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 340px;
            background-color: #087BDD;
            color: #FFFFFF;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            font-style: italic;
            margin-bottom: 40px;
            text-align: center;
            color: #FFFFFF;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            letter-spacing: 2px;
        }

        nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        nav ul li {
            margin-bottom: 15px;
        }

        nav ul li a {
            color: #FFFFFF;
            text-decoration: none;
            font-size: 18px;
            display: flex;
            align-items: center;
            padding: 10px;
        }

        nav ul li.active a {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }

        .logout-btn {
    background-color: #FFA800;
    color: #FFFFFF;
    border: none;
    padding: 10px 15px;
    font-size: 16px;
    font-weight: bold;
    border-radius: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: auto;
}

.logout-btn .icon-logout {
    margin-right: 10px;
    width: 20px;
    height: 20px;
}
        .main-content {
            flex-grow: 1;
            padding: 40px;
            overflow-y: auto;
        }

        h1 {
            font-size: 32px;
            margin-bottom: 20px;
            background-color: #D9D9D9;
        }

        .data-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .tambah-data-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
        }

        .table-container {
            overflow-x: auto;
        }
        table {
            min-width: 100%;
        }
        th, td {
            white-space: nowrap;
            padding: 10px;
        }

        td:nth-child(5) { 
            max-width: 120px; 
            word-wrap: break-word;
            white-space: normal;
        }

        .freeze-column {
            position: sticky;
            left: 0;
            background-color: #f2f2f2;
            z-index: 1;
        }
        .checkbox-cell {
            text-align: center;
        }
        .checkbox-cell input[type="checkbox"] {
            transform: scale(1.5);
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .opsi a {
            margin-right: 10px;
            text-decoration: none;
            color: #1F1F1F;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .icon-dashboard, .icon-oss, .icon-pembangunan, .icon-kube {
            width: 40px;
            height: 40px;
            background-size: cover;
            margin-right: 15px;
        }

        .icon-dashboard {
            background-image: url('assets/img/home.png');
        }

        .icon-oss {
            background-image: url('assets/img/data.png');
        }

        .icon-pembangunan {
            background-image: url('assets/img/building.png');
        }

        .icon-kube {
            background-image: url('assets/img/kube.png');
        }

        .icon-logout {
            background-image: url('assets/img/inner_plugin_iframe_x2.svg');
            width: 32px;
            height: 35px;
            background-size: cover;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #f8f9fa;
            margin: 5% auto;
            padding: 30px;
            border: none;
            width: 50%;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
            color: #087BDD;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            transition: 0.3s;
        }

        .close:hover,
        .close:focus {
            color: #087BDD;
            text-decoration: none;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group input[type="text"]:focus {
            outline: none;
            border-color: #087BDD;
            box-shadow: 0 0 0 2px rgba(8, 123, 221, 0.2);
        }

        .btn-submit {
            background-color: #087BDD;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 90%;
                margin: 10% auto;
            }
        }

        .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        }

        .checkbox-group {
            margin-bottom: 20px;
        }

        .checkbox-group h3 {
            margin-bottom: 10px;
            font-size: 16px;
        }

        .checkbox-group label {
            display: inline-block;
            margin-right: 15px;
        }

        textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            resize: vertical;
        }

        .modal-logout {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content-logout {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            text-align: center;
        }

        .modal-content-logout button {
            margin: 10px;
            padding: 5px 10px;
        }

        .Btn {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        width: 45px;
        height: 45px;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition-duration: .3s;
        box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.199);
        background-color: rgb(255, 65, 65);
        }

        /* plus sign */
        .sign {
        width: 100%;
        transition-duration: .3s;
        display: flex;
        align-items: center;
        justify-content: center;
        }

        .sign svg {
        width: 17px;
        }

        .sign svg path {
        fill: white;
        }
        /* text */
        .text {
        position: absolute;
        right: 0%;
        width: 0%;
        opacity: 0;
        color: white;
        font-size: 1.2em;
        font-weight: 600;
        transition-duration: .3s;
        }
        /* hover effect on button width */
        .Btn:hover {
        width: 125px;
        border-radius: 40px;
        transition-duration: .3s;
        }

        .Btn:hover .sign {
        width: 30%;
        transition-duration: .3s;
        padding-left: 10px;
        }
        /* hover effect button's text */
        .Btn:hover .text {
        opacity: 1;
        width: 70%;
        transition-duration: .3s;
        padding-right: 5px;
        }
        /* button click effect*/
        .Btn:active {
        transform: translate(2px ,2px);
        }


    </style>
    </head>
<body>
    <aside class="sidebar">
        <div>
            <div class="logo">PMD</div>
            <nav>
                <ul>
                    <li><a href="dashboard_pmd.php"><i class="icon-dashboard"></i> Beranda</a></li>
                    <li><a href="oss_page.php"><i class="icon-oss"></i> Data OSS</a></li>
                    <li><a href="pembangunan_page.php"><i class="icon-pembangunan"></i> Data Pembangunan</a></li>
                    <li class="active"><a href="kube_page.php"><i class="icon-kube"></i> Data KUBE</a></li>
                </ul>
            </nav>
        </div>
        <form action="login_page.php" method="post">
    <button type="button" class="Btn" onclick="confirmLogout()">
        <div class="sign">
            <svg viewBox="0 0 512 512">
                <path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"></path>
            </svg>
        </div>
        <div class="text">Logout</div>
    </button>
</form>
    </aside>
    
    <main class="main-content">
        <h1>Data KUBE</h1>
        
        <div class="data-controls">
            <div>
                <label for="show-entries">Menunjukkan</label>
                <select id="show-entries" onchange="changeLimit(this.value)">
                    <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                </select>
            </div>
            <form action="" method="GET" id="search-form">
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
            <a href="#" class="tambah-data-btn">+ Tambah Data</a>
        </div>

        <div class="table-container">
            <table>
            <thead>
    <tr>
        <th class="freeze-column" rowspan="2">No</th>
        <th rowspan="2">Kecamatan</th>
        <th rowspan="2">Kelurahan/Desa</th>
        <th rowspan="2">Nama KUBE</th>
        <th rowspan="2">Nama Produk</th>
        <th colspan="5">Perizinan</th>
        <th colspan="3">Digital Marketing</th>
        <th rowspan="2">Wilayah Pemasaran</th>
        <th rowspan="2">Keterangan</th>
        <th rowspan="2">Opsi</th>
    </tr>
    <tr>
        <th>PKP</th>
        <th>NIB</th>
        <th>PIRT</th>
        <th>Halal</th>
        <th>Izin Lainnya</th>
        <th>FB</th>
        <th>IG</th>
        <th>Lainnya</th>
    </tr>
</thead>

                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td class='freeze-column'>".$row["no"]."</td>
                                    <td>".$row["kecamatan"]."</td>
                                    <td>".$row["kelurahan_desa"]."</td>
                                    <td>".$row["nama_kube"]."</td>
                                    <td>".$row["nama_produk"]."</td>
                                    <td class='checkbox-cell'><input type='checkbox' ".($row["pkb"] ? 'checked' : '')." disabled></td>
                                    <td class='checkbox-cell'><input type='checkbox' ".($row["nib"] ? 'checked' : '')." disabled></td>
                                    <td class='checkbox-cell'><input type='checkbox' ".($row["pirt"] ? 'checked' : '')." disabled></td>
                                    <td class='checkbox-cell'><input type='checkbox' ".($row["halal"] ? 'checked' : '')." disabled></td>
                                    <td class='checkbox-cell'><input type='checkbox' ".($row["izin_lainnya"] ? 'checked' : '')." disabled></td>
                                    <td class='checkbox-cell'><input type='checkbox' ".($row["fb"] ? 'checked' : '')." disabled></td>
                                    <td class='checkbox-cell'><input type='checkbox' ".($row["ig"] ? 'checked' : '')." disabled></td>
                                    <td class='checkbox-cell'><input type='checkbox' ".($row["digital_marketing_lainnya"] ? 'checked' : '')." disabled></td>
                                    <td>".$row["wilayah_pemasaran"]."</td>
                                    <td>".$row["ket"]."</td>
                                    <td class='opsi'>
                                        <a href='#' class='edit-btn' data-id='".$row["id"]."'>✏️</a>
                                        <a href='#' class='delete-btn' data-id='".$row["id"]."'>🗑️</a>
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='16'>Tidak ada data yang ditemukan</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <?php
            $showing_start = $start + 1;
            $showing_end = min($start + $limit, $total_results);
            echo "<p>Menunjukkan $showing_start-$showing_end dari $total_results</p>";
            
 // Pagination controls
if ($total_pages > 1):
    $range = 2; // Jumlah halaman yang ditampilkan di kiri dan kanan halaman aktif
?>
    <div class="pagination-controls">
        <?php if ($page > 1): ?>
            <a href="?page=1&limit=<?= $limit ?>&search=<?= $search ?>">First</a>
            <a href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>&search=<?= $search ?>">Previous</a>
        <?php endif; ?>

        <?php
        for ($i = 1; $i <= $total_pages; $i++):
            if ($i == 1 || $i == $total_pages || ($i >= $page - $range && $i <= $page + $range)):
                ?>
                <a href="?page=<?= $i ?>&limit=<?= $limit ?>&search=<?= $search ?>" 
                   <?= $page == $i ? 'class="active"' : '' ?>><?= $i ?></a>
                <?php
            elseif ($i == $page - $range - 1 || $i == $page + $range + 1):
                echo "<span>...</span>";
            endif;
        endfor;
        ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>&search=<?= $search ?>">Next</a>
            <a href="?page=<?= $total_pages ?>&limit=<?= $limit ?>&search=<?= $search ?>">Last</a>
        <?php endif; ?>
    </div>
<?php endif; 
    ?>
            
        </div>
    </main>

    <script>
    function changeLimit(limit) {
        window.location.href = '?limit=' + limit + '&search=<?php echo $search; ?>';
    }
    </script>

<div id="dataModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Tambah Data KUBE</h2>
            <span class="close">&times;</span>
        </div>
        <form id="dataForm">
    <input type="hidden" id="action" name="action" value="tambah">
    <input type="hidden" id="id" name="id">
    <div class="form-group">
        <label for="kecamatan">Kecamatan:</label>
        <input type="text" id="kecamatan" name="kecamatan" required>
    </div>
    <div class="form-group">
        <label for="kelurahan_desa">Kelurahan/Desa:</label>
        <input type="text" id="kelurahan_desa" name="kelurahan_desa" required>
    </div>
    <div class="form-group">
        <label for="nama_kube">Nama KUBE:</label>
        <input type="text" id="nama_kube" name="nama_kube" required>
    </div>
    <div class="form-group">
        <label for="nama_produk">Nama Produk:</label>
        <input type="text" id="nama_produk" name="nama_produk" required>
    </div>
    <div class="checkbox-group">
        <h3>Perizinan:</h3>
        <label><input type="checkbox" id="pkb" name="pkb"> PKB</label>
        <label><input type="checkbox" id="nib" name="nib"> NIB</label>
        <label><input type="checkbox" id="pirt" name="pirt"> PIRT</label>
        <label><input type="checkbox" id="halal" name="halal"> Halal</label>
        <label><input type="checkbox" id="izin_lainnya" name="izin_lainnya"> Izin Lainnya</label>
    </div>
    <div class="checkbox-group">
        <h3>Digital Marketing:</h3>
        <label><input type="checkbox" id="fb" name="fb"> FB</label>
        <label><input type="checkbox" id="ig" name="ig"> IG</label>
        <label><input type="checkbox" id="digital_marketing_lainnya" name="digital_marketing_lainnya"> Lainnya</label>
    </div>
    <div class="form-group">
        <label for="wilayah_pemasaran">Wilayah Pemasaran:</label>
        <textarea id="wilayah_pemasaran" name="wilayah_pemasaran"></textarea>
    </div>
    <div class="form-group">
        <label for="ket">Keterangan:</label>
        <textarea id="ket" name="ket"></textarea>
    </div>
    <button type="submit" class="btn-submit">Simpan Data</button>
    </form>
</div>
</div>

<div id="logoutModal" class="modal-logout">
    <div class="modal-content-logout">
        <h2>Konfirmasi</h2>
        <p>Apakah Anda yakin ingin keluar?</p>
        <button onclick="logout()">Ya</button>
        <button onclick="closeLogoutModal()">Tidak</button>
    </div>
</div>

    <script>
$(document).ready(function() {
    var dataModal = document.getElementById("dataModal");
    var tambahBtn = document.querySelector(".tambah-data-btn");
    var spans = document.getElementsByClassName("close");

    tambahBtn.onclick = function() {
        $("#modalTitle").text("Tambah Data KUBE");
        $("#action").val("tambah");
        $("#id").val("");
        $("#dataForm")[0].reset();
        dataModal.style.display = "block";
    }

    for (let span of spans) {
        span.onclick = function() {
            dataModal.style.display = "none";
        }
    }

    window.onclick = function(event) {
        if (event.target == dataModal) {
            dataModal.style.display = "none";
        }
    }

    $("#dataForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: 'kube_page.php',
            type: 'post',
            data: $(this).serialize(),
            success: function(response) {
                if(response === "success") {
                    location.reload();
                } else {
                    alert("Error: " + response);
                }
            }
        });
    });

    $(".edit-btn").click(function() {
    var id = $(this).data('id');
    $("#modalTitle").text("Edit Data KUBE");
    $("#action").val("edit");
    $("#id").val(id);
    
    // Ambil data dari server
    $.ajax({
        url: 'kube_page.php',
        type: 'get',
        data: {id: id, action: 'get'},
        dataType: 'json',
        success: function(data) {
            $("#kecamatan").val(data.kecamatan);
            $("#kelurahan_desa").val(data.kelurahan_desa);
            $("#nama_kube").val(data.nama_kube);
            $("#nama_produk").val(data.nama_produk);
            $("#pkb").prop('checked', data.pkb == 1);
            $("#nib").prop('checked', data.nib == 1);
            $("#pirt").prop('checked', data.pirt == 1);
            $("#halal").prop('checked', data.halal == 1);
            $("#izin_lainnya").prop('checked', data.izin_lainnya == 1);
            $("#fb").prop('checked', data.fb == 1);
            $("#ig").prop('checked', data.ig == 1);
            $("#digital_marketing_lainnya").prop('checked', data.digital_marketing_lainnya == 1);
            $("#wilayah_pemasaran").val(data.wilayah_pemasaran);
            $("#ket").val(data.ket);
            
            dataModal.style.display = "block";
        }
    });
});

        $(".delete-btn").click(function() {
            if(confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                var id = $(this).data('id');
                $.get('kube_page.php', {hapus: id}, function(response) {
                    if(response === "success") {
                        location.reload();
                    } else {
                        alert("Error: " + response);
                    }
                });
            }
        });
    });

    function logout() {
    // Kirim permintaan POST ke logout.php
    fetch('logout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'confirm_logout=yes'
    })
    .then(response => {
        if (response.ok) {
            // Redirect ke halaman login setelah logout berhasil
            window.location.href = "login_page.php";
        } else {
            console.error('Logout failed');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function confirmLogout() {
    document.getElementById('logoutModal').style.display = 'block';
}
function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
}



    </script>
</body>
</html>

<?php
$conn->close();
?>