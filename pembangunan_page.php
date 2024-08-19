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

// Function to get table structure
function getTableStructure($conn, $table) {
    $columns = [];
    $result = $conn->query("DESCRIBE $table");
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    return $columns;
}

// Function to get available years
function getAvailableYears($conn) {
    $years = [];
    $result = $conn->query("SHOW TABLES LIKE 'pembangunan_%'");
    while ($row = $result->fetch_array()) {
        $tableName = $row[0];
        if ($tableName == 'pembangunan_2018_2019') {
            if (!in_array('2018', $years)) $years[] = '2018';
            if (!in_array('2019', $years)) $years[] = '2019';
        } else {
            $year = substr($tableName, strrpos($tableName, '_') + 1);
            if (is_numeric($year) && !in_array($year, $years)) {
                $years[] = $year;
            }
        }
    }
    rsort($years); // Sort years in descending order
    return $years;
}

// Get valid years
$validYears = getAvailableYears($conn);
$year = isset($_GET['year']) && in_array($_GET['year'], $validYears) ? $_GET['year'] : (empty($validYears) ? date('Y') : $validYears[0]);

// Determine the table name
if ($year == '2018' || $year == '2019') {
    $table = 'pembangunan_2018_2019';
} else {
    $table = 'pembangunan_' . $year;
}

// Initialize variables
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

function ensureColumnsExist($conn, $table, $columns) {
    $existingColumns = [];
    $result = $conn->query("DESCRIBE $table");
    while ($row = $result->fetch_assoc()) {
        $existingColumns[] = $row['Field'];
    }

    $columnsToAdd = array_diff($columns, $existingColumns);
    foreach ($columnsToAdd as $column) {
        if ($column != 'id' && $column != 'no' && $column != 'action' && $column != 'year') {
            $sql = "ALTER TABLE $table ADD COLUMN $column VARCHAR(255)";
            $conn->query($sql);
        }
    }
}


// Get table structure
$tableStructure = getTableStructure($conn, $table);

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $year = $_POST['year'];
    
    if ($year == '2018' || $year == '2019') {
        $table = 'pembangunan_2018_2019';
    } else {
        $table = 'pembangunan_' . $year;
    }
    
    $values = [];
    foreach ($_POST as $key => $value) {
        if ($key != 'action' && $key != 'id' && $key != 'year') {
            $values[$key] = $conn->real_escape_string($value);
        }
    }

    if ($action == 'tambah') {
        $newNo = $conn->query("SELECT MAX(no) AS max_no FROM $table")->fetch_assoc()['max_no'] + 1;
        $values['no'] = $newNo;
        
        $columns = implode(", ", array_keys($values));
        $valueStrings = "'" . implode("', '", $values) . "'";
        $sql = "INSERT INTO $table ($columns) VALUES ($valueStrings)";
    } elseif ($action == 'edit') {
        $id = $conn->real_escape_string($_POST['id']);
        $updates = [];
        foreach ($values as $key => $value) {
            $updates[] = "$key='$value'";
        }
        if (!empty($updates)) {
            $sql = "UPDATE $table SET " . implode(", ", $updates) . " WHERE id=$id";
        } else {
            echo "Error: No fields to update";
            exit;
        }
    }

    if (isset($sql) && !empty($sql)) {
        if ($conn->query($sql) === TRUE) {
            echo "success";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error: SQL query is empty";
    }
    
    exit;
}


// Handle GET requests for editing
if (isset($_GET['id']) && isset($_GET['year'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $year = $_GET['year'];
    $sql = "SELECT * FROM $table WHERE id = $id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Data not found']);
    }
    exit;
}

// Handle delete requests
if (isset($_GET['hapus']) && isset($_GET['year'])) {
    $id = $conn->real_escape_string($_GET['hapus']);
    $year = $_GET['year'];
    $sql = "DELETE FROM $table WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: pembangunan_page.php?year=$year");
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Create search query
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$columns = getTableStructure($conn, $table);
$searchConditions = [];
foreach ($columns as $column) {
    if ($column != 'id') {
        $searchConditions[] = "$column LIKE '%$search%'";
    }
}
$searchQuery = !empty($searchConditions) ? implode(' OR ', $searchConditions) : '1';

// Main query
$sql = "SELECT * FROM $table WHERE $searchQuery";
$countSql = "SELECT COUNT(*) as total FROM $table WHERE $searchQuery";

// Count total data for pagination
$totalResult = $conn->query($countSql);
if ($totalResult === false) {
    die("Error executing query: " . $conn->error);
}
$totalData = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalData / $limit);

// Add LIMIT for pagination
$start = ($page - 1) * $limit;
$sql .= " LIMIT $start, $limit";
$result = $conn->query($sql);
if ($result === false) {
    die("Error executing query: " . $conn->error);
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pembangunan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        /* Gunakan style yang sama seperti pada halaman data OSS */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
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

        .buat-tabel-btn {
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

        td:nth-child(2) { 
            max-width: 300px; 
            word-wrap: break-word;
            white-space: normal;
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

        .tambahKolom {
            background-color: #087BDD;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .tambahKolom:hover {
            background-color: #0056b3;
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


        .item-hints {
            position: absolute;
            bottom: 85px;
            right: 15px;
        }

        .item-hints {
            position: relative;
            display: inline-block;
            margin-left: 30px;
        }

        .item-hints .hint {
            position: relative;
        }

        .item-hints .hint-dot {
            background-color: #087BDD;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            cursor: pointer;
        }

        .item-hints .hint-content {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            background-color: #087BDD;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
            z-index: 1000;
        }

        .item-hints .hint:hover .hint-content {
            opacity: 1;
            visibility: visible;
        }

        .item-hints .hint-content::after {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 0 10px 10px 10px;
            border-style: solid;
            border-color: transparent transparent #087BDD transparent;
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
                    <li class="active"><a href="pembangunan_page.php"><i class="icon-pembangunan"></i> Data Pembangunan</a></li>
                    <li><a href="kube_page.php"><i class="icon-kube"></i> Data KUBE</a></li>
            </ul>
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
        <h1>Data Pembangunan</h1>
        
        <div class="data-controls">
            <div>
                <label for="year-select">Pilih Tahun:</label>
                <select id="year-select" onchange="changeYear(this.value)">
                    <?php foreach ($validYears as $validYear): ?>
                        <option value="<?php echo $validYear; ?>" <?php echo $year == $validYear ? 'selected' : ''; ?>><?php echo $validYear; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
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
                <input type="hidden" name="year" value="<?php echo $year; ?>">
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
            <a href="#" class="buat-tabel-btn">+ Tambah Tabel</a>
            <a href="#" class="tambah-data-btn">+ Tambah Data</a>
        </div>

        <table>
            <thead>
                <tr>
                    <?php 
                    foreach ($tableStructure as $column): 
                        if ($column !== 'id'): 
                    ?>
                        <th><?php echo ucfirst(str_replace('_', ' ', $column)); ?></th>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                    <th>Opsi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        foreach ($tableStructure as $column) {
                            if ($column !== 'id') {
                                echo "<td>" . htmlspecialchars($row[$column]) . "</td>";
                            }
                        }
                        echo "<td class='opsi'>
                        <a href='#' class='edit-btn' data-id='".$row['id']."' data-year='".$year."'>✏️</a>
                        <a href='#' class='delete-btn' data-id='".$row['id']."' data-year='".$year."'>🗑️</a>
                    </td>";
                echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='".(count($tableStructure) - 1)."'>Tidak ada data yang ditemukan</td></tr>";
                }
                ?>
            </tbody>
        </table>

<div class="pagination">
    <?php
    $showing_start = $start + 1;
    $showing_end = min($start + $limit, $totalData);
    echo "<p>Menunjukkan $showing_start-$showing_end dari $totalData</p>";
    
    // Pagination controls
    if ($totalPages > 1):
        $range = 2; // Jumlah halaman yang ditampilkan di kiri dan kanan halaman aktif
    ?>
        <div class="pagination-controls">
            <?php if ($page > 1): ?>
                <a href="?year=<?= $year ?>&page=1&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">First</a>
                <a href="?year=<?= $year ?>&page=<?= $page - 1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">Previous</a>
            <?php endif; ?>

            <?php
            for ($i = 1; $i <= $totalPages; $i++):
                if ($i == 1 || $i == $totalPages || ($i >= $page - $range && $i <= $page + $range)):
                    ?>
                    <a href="?year=<?= $year ?>&page=<?= $i ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>" 
                       <?= $page == $i ? 'class="active"' : '' ?>><?= $i ?></a>
                    <?php
                elseif ($i == $page - $range - 1 || $i == $page + $range + 1):
                    echo "<span>...</span>";
                endif;
            endfor;
            ?>

            <?php if ($page < $totalPages): ?>
                <a href="?year=<?= $year ?>&page=<?= $page + 1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">Next</a>
                <a href="?year=<?= $year ?>&page=<?= $totalPages ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">Last</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal untuk Tambah/Edit Data -->
<div id="dataModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Tambah Data Pembangunan</h2>
            <span class="close">&times;</span>
        </div>
        <form id="dataForm">
            <input type="hidden" id="action" name="action" value="tambah">
            <input type="hidden" id="id" name="id">
            <input type="hidden" id="year" name="year" value="<?php echo $year; ?>">
            <?php
            foreach (getTableStructure($conn, $table) as $column) {
                if ($column != 'id' && $column != 'no') {
                    echo "<div class='form-group'>
                            <label for='$column'>".ucfirst(str_replace('_', ' ', $column)).":</label>
                            <input type='text' id='$column' name='$column' required>
                        </div>";
                }
            }
            ?>
            <button type="submit" class="btn-submit">Tambah Data</button>
        </form>
        </div>
    </div>

    <!-- Modal untuk Buat Tabel Baru -->
<div id="buatTabelModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Tambah Tabel</h2>
            <span class="close">&times;</span>
        </div>
        <form id="buatTabelForm">
            <div class="form-group">
                <label for="tahun">Tahun:</label>
                <input type="number" id="tahun" name="tahun" required min="2024">
                              <!-- Tooltip -->
                              <div class="item-hints">
                    <div class="hint" data-position="4">
                        <span class="hint-dot">i</span>
                        <div class="hint-content">
                            <p>Kolom No. tidak perlu ditambahkan</p>
                        </div>
                    </div>
                </div>
            </div>
            <div id="kolom-container">
                <div class="form-group">
                    <label for="kolom1">Nama Kolom 1:</label>
                    <input type="text" name="kolom[]" required>
                </div>
            </div>
            <button type="button" class="tambahKolom">Tambah Kolom</button>
            <button type="submit" class="btn-submit">Buat Tabel</button>
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

    </main>
    

    <script>
    $(document).ready(function() {
        var modal = document.getElementById("dataModal");
        var tambahBtn = document.querySelector(".tambah-data-btn");
        var span = document.getElementsByClassName("close")[0];

        tambahBtn.onclick = function() {
            $("#modalTitle").text("Tambah Data Pembangunan");
            $("#action").val("tambah");
            $("#id").val("");
            $("#dataForm")[0].reset();
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        $("#dataForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'pembangunan_page.php',
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
    var year = $(this).data('year');
    $("#modalTitle").text("Edit Data Pembangunan");
    $("#action").val("edit");
    $("#id").val(id);
    $("#year").val(year);
    
    // Ambil data dari server
    $.ajax({
        url: 'pembangunan_page.php',
        type: 'get',
        data: {id: id, year: year},
        dataType: 'json',
        success: function(data) {
            for (var key in data) {
                if (key !== 'id' && key !== 'no') {
                    $("#" + key).val(data[key]);
                }
            }
            modal.style.display = "block";
        }
    });
});

$(".delete-btn").click(function() {
    var id = $(this).data('id');
    if (id === '') {
        alert('Tidak dapat menghapus data ini. ID tidak ditemukan.');
        return;
    }
    if(confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        var year = $(this).data('year');
        window.location.href = 'pembangunan_page.php?hapus=' + id + '&year=' + year;
    }
});


    });
    var buatTabelModal = document.getElementById("buatTabelModal");
    var buatTabelBtn = document.querySelector(".buat-tabel-btn");
    var spanBuatTabel = buatTabelModal.getElementsByClassName("close")[0];

    buatTabelBtn.onclick = function() {
        buatTabelModal.style.display = "block";
    }

    spanBuatTabel.onclick = function() {
        buatTabelModal.style.display = "none";
    }

    $(".tambahKolom").click(function() {
        var kolomCount = $("#kolom-container .form-group").length + 1;
        $("#kolom-container").append(
            '<div class="form-group">' +
            '<label for="kolom' + kolomCount + '">Nama Kolom ' + kolomCount + ':</label>' +
            '<input type="text" name="kolom[]" required>' +
            '</div>'
        );
    });

    $("#buatTabelForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'buat_tabel.php',
                type: 'post',
                data: $(this).serialize(),
                success: function(response) {
                    if(response === "success") {
                        alert("Tabel berhasil dibuat!");
                        location.reload();
                    } else {
                        alert("Error: " + response);
                    }
                }
            });
        });
 

    function changeYear(year) {
        window.location.href = '?year=' + year + '&limit=<?php echo $limit; ?>&search=<?php echo $search; ?>';
    }

    function changeLimit(limit) {
        window.location.href = '?year=<?php echo $year; ?>&limit=' + limit + '&search=<?php echo $search; ?>';
    }
    
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