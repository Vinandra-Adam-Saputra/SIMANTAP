<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'umum') {
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

// Mengambil jumlah data SPT
$sql_spt = "SELECT COUNT(*) as total_spt FROM data_spt";
$result_spt = $conn->query($sql_spt);
$total_spt = $result_spt->fetch_assoc()['total_spt'];

// Mengambil jumlah data Nota Dinas
$sql_nota = "SELECT COUNT(*) as total_nota FROM data_nota_dinas";
$result_nota = $conn->query($sql_nota);
$total_nota = $result_nota->fetch_assoc()['total_nota'];

// Mengambil jumlah data Surat Masuk
$sql_surat = "SELECT COUNT(*) as total_surat FROM data_surat_masuk";
$result_surat = $conn->query($sql_surat);
$total_surat = $result_surat->fetch_assoc()['total_surat'];

// Mengambil jumlah data Surat Keluar
$sql_surat_keluar = "SELECT COUNT(*) as total_surat_keluar FROM data_surat_keluar";
$result_surat_keluar = $conn->query($sql_surat_keluar);
$total_surat_keluar = $result_surat_keluar->fetch_assoc()['total_surat_keluar'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Umum</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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


        .card-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .card {
            background-color: #FFFFFF;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: 220px;
        }

        .card h4 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #FFA800;
        }

        .card p {
            font-size: 14px;
            color: #8F8F8F;
            margin-bottom: 10px;
        }

        .card canvas {
            flex-grow: 1;
            max-height: 120px;
        }

        .icon-dashboard, .icon-spt, .icon-nota-dinas, .icon-surat-masuk, .icon-surat-keluar {
            width: 40px;
            height: 40px;
            background-size: cover;
            margin-right: 15px;
        }

        .icon-dashboard {
            background-image: url('assets/img/home.png');
        }

        .icon-spt {
            background-image: url('assets/img/spt.png');
        }

        .icon-nota-dinas {
            background-image: url('assets/img/nota.png');
        }

        .icon-surat-masuk {
            background-image: url('assets/img/inbox.png');
        }

        .icon-surat-keluar {
            background-image: url('assets/img/letter.png');
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
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            text-align: center;
        }

        .modal-content button {
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
            <div class="logo">UMUM</div>
            <nav>
                <ul>
                    <li class="active"><a href="dashboard_umum.php"><i class="icon-dashboard"></i> Beranda</a></li>
                    <li><a href="spt_page.php"><i class="icon-spt"></i> Data SPT</a></li>
                    <li><a href="nota_dinas_page.php"><i class="icon-nota-dinas"></i> Data Nota Dinas</a></li>
                    <li><a href="surat_masuk_page.php"><i class="icon-surat-masuk"></i> Surat Masuk</a></li>
                    <li><a href="surat_keluar_page.php"><i class="icon-surat-keluar"></i> Surat Keluar</a></li>
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
        <header>
            <h1>Beranda</h1>
            <div class="user-profile"></div>
        </header>
        
        <div class="card-container">
    <div class="card">
        <h4>Jumlah Data SPT</h4>
        <canvas id="chartSPT"></canvas>
    </div>
    <div class="card">
        <h4>Jumlah Data Nota Dinas</h4>
        <canvas id="chartNotaDinas"></canvas>
    </div>
    <div class="card">
        <h4>Jumlah Data Surat Masuk</h4>
        <canvas id="chartSuratMasuk"></canvas>
    </div>
    <div class="card">
        <h4>Jumlah Data Surat Keluar</h4>
        <canvas id="chartSuratKeluar"></canvas>
    </div>
</div>

    </main>

    <div id="logoutModal" class="modal">
    <div class="modal-content">
        <h2>Konfirmasi</h2>
        <p>Apakah Anda yakin ingin keluar?</p>
        <button onclick="logout()">Ya</button>
        <button onclick="closeLogoutModal()">Tidak</button>
    </div>
</div>

    <script>
function createBarChart(elementId, label, data, backgroundColor, borderColor) {
    var ctx = document.getElementById(elementId).getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [label],
            datasets: [{
                label: label,
                data: [data],
                backgroundColor: [backgroundColor],
                borderColor: [borderColor],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true
                }
            }
        }
    });
}

createBarChart('chartSPT', 'Data SPT', <?php echo $total_spt; ?>, 'rgba(255, 168, 0, 0.8)', 'rgba(255, 168, 0, 1)');
createBarChart('chartNotaDinas', 'Data Nota Dinas', <?php echo $total_nota; ?>, 'rgba(8, 123, 221, 0.8)', 'rgba(8, 123, 221, 1)');
createBarChart('chartSuratMasuk', 'Data Surat Masuk', <?php echo $total_surat; ?>, 'rgba(255, 99, 71, 1)', 'rgba(255, 99, 71, 1)');
createBarChart('chartSuratKeluar', 'Data Surat Keluar', <?php echo $total_surat_keluar; ?>, 'rgba(75, 192, 192, 0.8)', 'rgba(75, 192, 192, 1)'); 



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