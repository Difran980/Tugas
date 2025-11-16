<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Cek username dan password di database
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['admin'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" 
          crossorigin="anonymous">
    <style>
        body {
            background-color: #fff9fc;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            background-color: #fff;
            padding: 40px 35px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 98, 255, 1);
            width: 100%;
            max-width: 380px;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
            color: #0099ffff;
        }

        label {
            font-weight: 500;
            color: #444;
            margin-top: 10px;
        }

        input[type="text"], 
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-top: 5px;
            transition: 0.3s;
        }

        input[type="text"]:focus, 
        input[type="password"]:focus {
            border-color: #0066ffff;
            box-shadow: 0 0 4px #f7c2eb;
            outline: none;
        }

        button {
            background-color: #0084ffff;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 50px;
            margin-top: 20px;
            font-weight: 600;
            transition: 0.3s;
        }

        button:hover {
            background-color: #003cffff;
        }

        .error {
            background: #ffe5ec;
            color: #0062ffff;
            border-left: 4px solid #001effff;
            padding: 8px 12px;
            margin-top: 15px;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .footer {
            text-align: center;
            color: #aaa;
            font-size: 0.85rem;
            margin-top: 20px;
        }

        a.back-link {
            display: inline-block;
            margin-top: 15px;
            color: #0026ffff;
            text-decoration: none;
        }

        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login Admin</h2>
        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" placeholder="Masukkan username" required>

            <label>Password:</label>
            <input type="password" name="password" placeholder="Masukkan sandi" required>

            <button type="submit">Login</button>

            <?php if (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </form>

        <div class="footer">
            <a href="index.php" class="back-link">← Kembali ke Katalog Buku</a>
        </div>
    </div>
</body>
</html>