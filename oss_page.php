<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit();
}

// Koneksi Database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "simantap";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB dalam bytes

if(isset($_POST['action'])) {
    $nama = $conn->real_escape_string($_POST['nama']);
    $nama_usaha = $conn->real_escape_string($_POST['nama_usaha']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $alamat_usaha = $conn->real_escape_string($_POST['alamat_usaha']);
    $nib = $conn->real_escape_string($_POST['nib']);

    // Proses upload file
    $file_dokumen = '';
    if(isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] == 0) {
        $target_dir = "uploads/";
        $file_dokumen = basename($_FILES["file_dokumen"]["name"]);
        $target_file = $target_dir . $file_dokumen;
        $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // Cek apakah file sudah ada
        if (file_exists($target_file)) {
            $file_dokumen = time() . '_' . $file_dokumen;
            $target_file = $target_dir . $file_dokumen;
        }

        // Cek ukuran file
        if ($_FILES['file_dokumen']['size'] > MAX_FILE_SIZE) {
            echo "Ukuran file terlalu besar. Maksimum 5 MB.";
            exit;
        }
        
        // Validasi tipe file
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf');
        if(!in_array($fileType, $allowed_types)) {
            echo "Maaf, hanya file JPG, JPEG, PNG, GIF, dan PDF yang diizinkan.";
            exit;
        }
        
        if (move_uploaded_file($_FILES["file_dokumen"]["tmp_name"], $target_file)) {
            echo "File ". $file_dokumen . " berhasil diupload.";
        } else {
            echo "Maaf, terjadi kesalahan saat mengupload file.";
        }
    }

    if($_POST['action'] == 'tambah') {
        $sql = "INSERT INTO data_oss (NAMA, NAMA_USAHA, ALAMAT, ALAMAT_USAHA, NIB, file_dokumen) VALUES ('$nama', '$nama_usaha', '$alamat', '$alamat_usaha', '$nib', '$file_dokumen')";
    } elseif($_POST['action'] == 'edit') {
        $id = $conn->real_escape_string($_POST['id']);
        if ($file_dokumen) {
            $sql = "UPDATE data_oss SET NAMA='$nama', NAMA_USAHA='$nama_usaha', ALAMAT='$alamat', ALAMAT_USAHA='$alamat_usaha', NIB='$nib', file_dokumen='$file_dokumen' WHERE ID=$id";
        } else {
            $sql = "UPDATE data_oss SET NAMA='$nama', NAMA_USAHA='$nama_usaha', ALAMAT='$alamat', ALAMAT_USAHA='$alamat_usaha', NIB='$nib' WHERE ID=$id";
        }
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
    $sql = "DELETE FROM data_oss WHERE ID=$id";
    $conn->query($sql);
}

// Fungsi pencarian dan pagination
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

$sql = "SELECT * FROM data_oss WHERE 
        NAMA LIKE '%$search%' OR 
        NAMA_USAHA LIKE '%$search%' OR 
        ALAMAT LIKE '%$search%' OR 
        ALAMAT_USAHA LIKE '%$search%' OR 
        NIB LIKE '%$search%'";

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
    <title>Data OSS</title>
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
            width: 422px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #FFFFFF;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        td:nth-child(5) { 
            max-width: 500px; 
            word-wrap: break-word;
            white-space: normal;
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

        .modal-preview {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content-preview {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
        }

        #downloadBtn {
            display: block;
            margin: 20px auto 0;
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
                    <li class="active"><a href="oss_page.php"><i class="icon-oss"></i> Data OSS</a></li>
                    <li><a href="pembangunan_page.php"><i class="icon-pembangunan"></i> Data Pembangunan</a></li>
                    <li><a href="kube_page.php"><i class="icon-kube"></i> Data KUBE</a></li>
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
        <h1>Data OSS</h1>
        
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

        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama</th>
                    <th>Nama Usaha</th>
                    <th>Alamat</th>
                    <th>Alamat Usaha</th>
                    <th>NIB</th>
                    <th>Opsi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $no = $start + 1;
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>".$no."</td>
                                <td>".$row["NAMA"]."</td>
                                <td>".$row["NAMA_USAHA"]."</td>
                                <td>".$row["ALAMAT"]."</td>
                                <td>".$row["ALAMAT_USAHA"]."</td>
                                <td>".$row["NIB"]."</td>
                                <td class='opsi'>
                                    <a href='#' class='edit-btn' data-id='".$row["ID"]."' data-nama='".$row["NAMA"]."' data-nama_usaha='".$row["NAMA_USAHA"]."' data-alamat='".$row["ALAMAT"]."' data-alamat_usaha='".$row["ALAMAT_USAHA"]."' data-nib='".$row["NIB"]."' data-file_dokumen='".$row["file_dokumen"]."'>✏️</a>
                                    <a href='#' class='delete-btn' data-id='".$row["ID"]."'>🗑️</a>";
                                    if (!empty($row["file_dokumen"])) {
                                        echo "<a href='#' class='preview-btn' data-file='".$row["file_dokumen"]."'>👁️</a>";
                                    }
                        
                        echo "</td></tr>";
                                $no++;
                            }
                } else {
                    echo "<tr><td colspan='7'>Tidak ada data yang ditemukan</td></tr>";
                }
                ?>
            </tbody>
        </table>
        

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

<div id="tambahModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Tambah Data OSS</h2>
            <span class="close">&times;</span>
        </div>
        <form id="tambahForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="tambah">
            <div class="form-group">
                <label for="nama">Nama:</label>
                <input type="text" id="nama" name="nama" required>
            </div>
            <div class="form-group">
                <label for="nama_usaha">Nama Usaha:</label>
                <input type="text" id="nama_usaha" name="nama_usaha" required>
            </div>
            <div class="form-group">
                <label for="alamat">Alamat:</label>
                <input type="text" id="alamat" name="alamat" required>
            </div>
            <div class="form-group">
                <label for="alamat_usaha">Alamat Usaha:</label>
                <input type="text" id="alamat_usaha" name="alamat_usaha" required>
            </div>
            <div class="form-group">
                <label for="nib">NIB:</label>
                <input type="text" id="nib" name="nib" required>
            </div>
            <div class="form-group">
                <label for="file_dokumen">Upload Dokumen (JPG, JPEG, PNG, GIF, PDF):</label>
                <input type="file" id="file_dokumen" name="file_dokumen" accept=".jpg,.jpeg,.png,.gif,.pdf" onchange="validateFileSize(this)">
            </div>
            <button type="submit" class="btn-submit">Tambah Data</button>
        </form>
    </div>
</div>


<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Data OSS</h2>
            <span class="close">&times;</span>
        </div>
        <form id="editForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit_id" name="id">
            <div class="form-group">
                <label for="edit_nama">Nama:</label>
                <input type="text" id="edit_nama" name="nama" required>
            </div>
            <div class="form-group">
                <label for="edit_nama_usaha">Nama Usaha:</label>
                <input type="text" id="edit_nama_usaha" name="nama_usaha" required>
            </div>
            <div class="form-group">
                <label for="edit_alamat">Alamat:</label>
                <input type="text" id="edit_alamat" name="alamat" required>
            </div>
            <div class="form-group">
                <label for="edit_alamat_usaha">Alamat Usaha:</label>
                <input type="text" id="edit_alamat_usaha" name="alamat_usaha" required>
            </div>
            <div class="form-group">
                <label for="edit_nib">NIB:</label>
                <input type="text" id="edit_nib" name="nib" required>
            </div>
            <div class="form-group">
                <label for="edit_file_dokumen">Upload Dokumen:</label>
                <div id="existing_file" style="display:none;"></div>
                <input type="file" id="edit_file_dokumen" name="file_dokumen" onchange="validateFileSize(this)">
            </div>
            <button type="submit" class="btn-submit">Update Data</button>
        </form>
    </div>
</div>

<div id="previewModal" class="modal-preview">
    <div class="modal-content-preview">
        <span class="close">&times;</span>
        <h2>Preview Dokumen</h2>
        <div id="previewContent"></div>
        <button id="downloadBtn" class="btn-submit">Download</button>
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
        var tambahModal = document.getElementById("tambahModal");
        var editModal = document.getElementById("editModal");
        var tambahBtn = document.querySelector(".tambah-data-btn");
        var spans = document.getElementsByClassName("close");

        tambahBtn.onclick = function() {
            tambahModal.style.display = "block";
        }

        for (let span of spans) {
            span.onclick = function() {
                tambahModal.style.display = "none";
                editModal.style.display = "none";
            }
        }

        window.onclick = function(event) {
            if (event.target == tambahModal) {
                tambahModal.style.display = "none";
            }
            if (event.target == editModal) {
                editModal.style.display = "none";
            }
        }

    $("#tambahForm, #editForm").submit(function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: 'oss_page.php',
        type: 'post',
        data: formData,
        contentType: false,
        processData: false,
        success: function() {
            location.reload();
        }
    });
});

$(".edit-btn").click(function() {
    var id = $(this).data('id');
    var nama = $(this).data('nama');
    var nama_usaha = $(this).data('nama_usaha');
    var alamat = $(this).data('alamat');
    var alamat_usaha = $(this).data('alamat_usaha');
    var nib = $(this).data('nib');
    var file_dokumen = $(this).data('file_dokumen');

    $("#edit_id").val(id);
    $("#edit_nama").val(nama);
    $("#edit_nama_usaha").val(nama_usaha);
    $("#edit_alamat").val(alamat);
    $("#edit_alamat_usaha").val(alamat_usaha);
    $("#edit_nib").val(nib);
    
    if (file_dokumen) {
        $("#existing_file").text("File saat ini: " + file_dokumen);
        $("#existing_file").show();
    } else {
        $("#existing_file").hide();
    }

    editModal.style.display = "block";
});

        $(".delete-btn").click(function() {
            if(confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                var id = $(this).data('id');
                $.get('oss_page.php', {hapus: id}, function() {
                    location.reload();
                });
            }
        });
    });

    
    function validateFileSize(input) {
    if (input.files && input.files[0]) {
        if (input.files[0].size > <?php echo MAX_FILE_SIZE; ?>) {
            alert("File size is too large. Maximum 5 MB allowed.");
            input.value = "";
        }
    }
}

// Proses Preview Dokumen
$(".preview-btn").click(function(e) {
    e.preventDefault();
    var file = $(this).data('file');
    var fileType = file.split('.').pop().toLowerCase();
    var previewContent = "";

    if (fileType === 'pdf') {
        previewContent = "<embed src='uploads/" + file + "' type='application/pdf' width='100%' height='600px' />";
    } else {
        previewContent = "<img src='uploads/" + file + "' alt='Preview Dokumen' style='max-width: 100%; height: auto;'>";
    }

    $("#previewContent").html(previewContent);
    $("#downloadBtn").data("file", file);
    $("#previewModal").css("display", "block");
});

// Tutup modal ketika klik (x)
$(".close").click(function() {
    $("#previewModal").css("display", "none");
});

// Tutup modal ketika klik diluar modal
$(window).click(function(e) {
    if ($(e.target).is('#previewModal')) {
        $("#previewModal").css("display", "none");
    }
});

// Proses tombol download pada modal preview
$("#downloadBtn").click(function() {
    var file = $(this).data('file');
    window.location.href = "download.php?file=" + file;
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