<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "simantap";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$login_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $conn->real_escape_string($_POST['username']);
        $password = $_POST['password'];

        $sql = "SELECT id, username, password, role FROM admin WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if ($password === $row['password']) { // Menggunakan perbandingan langsung
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                
                // Redirect based on role
                if ($row['role'] == 'pmd') {
                    header("Location: dashboard_pmd.php");
                } else {
                    header("Location: dashboard_umum.php");
                }
                exit();
            } else {
                $login_error = "Invalid username or password";
            }
        } else {
            $login_error = "Invalid username or password";
        }
        
        $stmt->close();
    } else {
        $login_error = "Username and password are required";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SIMANTAP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            position: relative;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .logo {
            width: 100px;
            margin-bottom: 20px;
        }
        h2 {
            color: #333;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .login-btn {
            background-color: #087BDD;
            color: white;
            padding: 14px 20px;
            margin: 10px 0;
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 1em;
            border-radius: 4px;
        }
        .login-btn:hover {
            background-color: #FFA800;
        }
        .back-arrow {
            position: absolute;
            top: 10px;
            left: 15px;
            font-size: 24px;
            color: #087BDD;
            text-decoration: none;
            line-height: 1;
        }
        .back-arrow:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
    
    <div class="login-container">
        <a href="index.php" class="back-arrow">←</a>
        <img src="assets/img/logo-bintan.png" alt="Logo" class="logo">
        <h2>LOGIN ADMIN</h2>
        <?php if (!empty($login_error)) { ?>
            <p class="error"><?php echo $login_error; ?></p>
        <?php } ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="login-btn">LOGIN</button>
        </form>
    </div>
</body>
</html>