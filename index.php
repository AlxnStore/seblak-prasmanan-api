<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Seblak Prasmanan</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; }
        h1 { color: #D44000; }
        h2 { color: #333; margin-top: 30px; }
        a { color: #D44000; }
        ul { line-height: 2; }
    </style>
</head>
<body>
    <h1>API Seblak Prasmanan</h1>
    <p><a href="/">Home</a></p>
    <p>Selamat Datang Di Rest API Servis Data Dari Aplikasi <strong>Seblak Prasmanan</strong></p>

    <h2>Di Bawah Link API Authentifikasi</h2>
    <ul>
        <li><a href="auth.php?action=login">POST UNTUK LOGIN</a> - defaulting to JSON</li>
        <li><a href="auth.php?action=register">POST UNTUK REGISTER</a> - defaulting to JSON</li>
    </ul>

    <h2>Di Bawah Link API Menu</h2>
    <ul>
        <li><a href="menu.php">GET SEMUA MENU</a> - defaulting to JSON</li>
        <li><a href="menu.php?action=add">POST UNTUK TAMBAH MENU</a> - defaulting to JSON</li>
        <li><a href="menu.php?action=update">POST UNTUK UPDATE MENU</a> - defaulting to JSON</li>
        <li><a href="menu.php?action=delete">POST UNTUK HAPUS MENU</a> - defaulting to JSON</li>
    </ul>

    <h2>Di Bawah Link API Orders</h2>
    <ul>
        <li><a href="orders.php">GET SEMUA PESANAN (Admin)</a> - defaulting to JSON</li>
        <li><a href="orders.php?action=place">POST UNTUK BUAT PESANAN</a> - defaulting to JSON</li>
        <li><a href="orders.php?action=update_status">POST UNTUK UPDATE STATUS</a> - defaulting to JSON</li>
        <li><a href="orders.php?action=cancel">POST UNTUK BATALKAN PESANAN</a> - defaulting to JSON</li>
    </ul>

    <h2>Cek Koneksi</h2>
    <ul>
        <li><a href="test.php">TEST KONEKSI DATABASE</a> - defaulting to JSON</li>
    </ul>
</body>
</html>
