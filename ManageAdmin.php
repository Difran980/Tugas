<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Tambah admin baru
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Admin</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f4fb;
            color: #02bdfcff;
            margin: 0;
            padding: 0;
        }

        nav ul {
            list-style-type: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #00b3ffff;
            color: white;
            padding: 15px 40px;
            margin: 0;
        }

        nav a {
            text-decoration: none;
            color: white;
            font-weight: 500;
        }

        nav a:hover {
            text-decoration: underline;
        }

        h1, h2 {
            text-align: center;
        }

        .container {
            width: 90%;
            margin: 20px auto;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 140, 255, 0.2);
        }

        form {
            text-align: center;
            margin-bottom: 25px;
        }

        input[type="text"], input[type="password"] {
            padding: 10px;
            border: 1px solid #0077ffff;
            border-radius: 8px;
            width: 200px;
            margin-right: 10px;
        }

        button {
            background-color: #009dffff;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #00a2ffff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #d1c4e9;
            text-align: center;
        }

        th {
            background-color: #009dffff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f3e8ff;
        }

        .back-btn {
            display: inline-block;
            background-color: #0088ffff;
            color: white;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            margin-left: 40px;
        }

        .back-btn:hover {
            background-color: #059fffff;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><h2>Kelola Admin</h2></li>
            <li>
                <a href="admin.php" class="back-btn">← Kembali ke Admin Panel</a>
                <a href="logout.php">Logout</a>
            </li>
        </ul>
    </nav>

    <div class="container">
        <h2>Tambah Admin Baru</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username baru" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="add">Tambah</button>
        </form>

        <h2>Daftar Admin</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']); ?></td>
                <td><?= htmlspecialchars($row['username']); ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $row['id']; ?>">
                        <button type="submit" name="delete" onclick="return confirm('Hapus admin ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>