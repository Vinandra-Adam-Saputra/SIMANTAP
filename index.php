<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMANTAP - Sistem Informasi Manajemen Data Terpadu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@700&family=Archivo+Black&family=Hind:wght@700&family=Goldman&display=swap" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Inter', sans-serif;
        }
        .landing-page {
            width: 100%;
            height: 100vh;
            position: relative;
            overflow: hidden;
        }
        .background-image {
            background-image: url('assets/img/kantor-camat.jpeg');
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            position: absolute;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
        }
        .overlay {
            background-color: rgba(0,0,0,0.7);
            position: absolute;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
        }
        .content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .welcome-text {
            font-family: 'Archivo Black', sans-serif;
            font-size: 3vw;
            color: #FFFFFF;
            text-align: center;
            margin-bottom: 10px;
        }
        .simantap-text {
            font-family: 'Archivo Black', sans-serif;
            font-size: 5vw;
            color: #FFFFFF;
            text-align: center;
            margin-bottom: 100px;
        }
        .description {
            font-family: 'Hind', sans-serif;
            font-weight: bold;
            font-size: 4vw;
            color: #FFFFFF;
            text-align: center;
            margin-bottom: 70px;
            max-width: 80%;
        }
        .tagline {
            font-family: 'Goldman', sans-serif;
            font-size: 2vw;
            color: #FFFFFF;
            text-align: center;
            margin-bottom: 40px;
        }
        @media (max-width: 768px) {
            .welcome-text { font-size: 5vw; }
            .simantap-text { font-size: 7vw; }
            .description { font-size: 3vw; }
            .tagline { font-size: 2.5vw; }
        }

        .button-container {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }
        .button {
            background-color: rgba(0,123,255,0.5);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #FFA800;
        }
    </style>
</head>
<body>
    <div class="landing-page">
        <div class="background-image"></div>
        <div class="overlay"></div>
        <div class="content">
        <div class="button-container">
                <a href="about.php" class="button">ABOUT</a>
                <a href="login_page.php" class="button">LOGIN</a>
            </div>
            <div class="welcome-text">SELAMAT DATANG DI</div>
            <div class="simantap-text">SIMANTAP</div>
            <div class="description">
                Sistem Informasi Manajemen Data Terpadu Kecamatan Bintan Timur
            </div>
            <div class="tagline">
                Mendigitalkan, Mengorganisir, Mengefisienkan.
            </div>
        </div>
    </div>
</body>
</html>