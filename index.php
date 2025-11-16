<?php
include 'db.php';

$result = $conn->query("SELECT * FROM books");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" 
          crossorigin="anonymous">
    <style>
        body {
            background-color: #fff9fc;
            font-family: 'Poppins', sans-serif;
            padding: 40px;
        }

        h1 {
            text-align: center;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
        }

        .container-table {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 30px;
            max-width: 1000px;
            margin: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #0066ffff;
            color: #fff;
        }

        th, td {
            padding: 12px;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid #ddd;
        }



        .btn-custom {
            background-color: #0066ffff;
            color: #ffffffff;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .btn-custom:hover {
            background-color: #0051ffff;
        }

        .btn-custom a {
            color: white;
            text-decoration: none;
        }

        .footer {
            text-align: center;
            color: #888;
            font-size: 0.9rem;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="container-table">
        <h1>Katalog Buku</h1>
        <div class="text-end mb-3">
            <button class="btn-custom"><a href="login.php">Login Admin</a></button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Judul Buku</th>
                    <th>Penulis</th>
                    <th>Tahun Rilis</th>
                    <th>Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                        <?php if (!empty($row['cover'])): ?>
                    <img src="uploads/<?= htmlspecialchars($row['cover']) ?>" 
                         alt="Cover Buku" 
                         width="80" 
                         style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <?php else: ?>
                    <span style="color:#888;">Tidak ada cover</span>
                <?php endif; ?>
            </td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['author']) ?></td>
                        <td><?= htmlspecialchars($row['year']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">
        © <?= date("Y") ?> Perpustakaan Online — Semua Hak Dilindungi
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" 
            crossorigin="anonymous"></script>
</body>
</html>