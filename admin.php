<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Pastikan folder uploads ada
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Flash message helper
function set_flash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}
function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// Handle CRUD + file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tambah buku
    if (isset($_POST['add'])) {
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $year = (int) $_POST['year'];
        $description = trim($_POST['description']);

        // Handle file upload (optional)
        $coverFilename = null;
        if (!empty($_FILES['cover']['name'])) {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if (in_array($_FILES['cover']['type'], $allowed) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
                // generate unique name
                $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
                $coverFilename = uniqid('cover_', true) . '.' . $ext;
                $target = $uploadDir . $coverFilename;
                if (!move_uploaded_file($_FILES['cover']['tmp_name'], $target)) {
                    set_flash('Gagal mengunggah gambar cover.', 'danger');
                    header("Location: admin.php");
                    exit();
                }
            } else {
                set_flash('File cover harus berupa gambar (jpg/png/gif/webp).', 'danger');
                header("Location: admin.php");
                exit();
            }
        }

        $stmt = $conn->prepare("INSERT INTO books (title, author, year, description, cover) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $title, $author, $year, $description, $coverFilename);
        if ($stmt->execute()) {
            set_flash('Buku berhasil ditambahkan.');
        } else {
            set_flash('Gagal menambahkan buku: ' . $conn->error, 'danger');
        }
        header("Location: admin.php");
        exit();
    }

    // Update buku
    if (isset($_POST['update'])) {
        $id = (int) $_POST['id'];
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $year = (int) $_POST['year'];
        $description = trim($_POST['description']);

        // Ambil cover lama
        $oldCover = null;
        $q = $conn->prepare("SELECT cover FROM books WHERE id=?");
        $q->bind_param("i", $id);
        $q->execute();
        $res = $q->get_result();
        if ($row = $res->fetch_assoc()) {
            $oldCover = $row['cover'];
        }

        // Jika ada file baru diupload, replace
        $coverFilename = $oldCover;
        if (!empty($_FILES['cover']['name'])) {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if (in_array($_FILES['cover']['type'], $allowed) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
                $coverFilename = uniqid('cover_', true) . '.' . $ext;
                $target = $uploadDir . $coverFilename;
                if (!move_uploaded_file($_FILES['cover']['tmp_name'], $target)) {
                    set_flash('Gagal mengunggah gambar cover.', 'danger');
                    header("Location: admin.php");
                    exit();
                }
                // hapus file lama kalau ada
                if (!empty($oldCover) && file_exists($uploadDir . $oldCover)) {
                    @unlink($uploadDir . $oldCover);
                }
            } else {
                set_flash('File cover harus berupa gambar (jpg/png/gif/webp).', 'danger');
                header("Location: admin.php");
                exit();
            }
        }

        $stmt = $conn->prepare("UPDATE books SET title=?, author=?, year=?, description=?, cover=? WHERE id=?");
        $stmt->bind_param("ssissi", $title, $author, $year, $description, $coverFilename, $id);
        if ($stmt->execute()) {
            set_flash('Buku berhasil diperbarui.');
        } else {
            set_flash('Gagal memperbarui buku: ' . $conn->error, 'danger');
        }
        header("Location: admin.php");
        exit();
    }

    // Delete buku
    if (isset($_POST['delete'])) {
        $id = (int) $_POST['id'];
        // ambil nama cover untuk dihapus
        $q = $conn->prepare("SELECT cover FROM books WHERE id=?");
        $q->bind_param("i", $id);
        $q->execute();
        $res = $q->get_result();
        $cover = null;
        if ($r = $res->fetch_assoc()) {
            $cover = $r['cover'];
        }

        $stmt = $conn->prepare("DELETE FROM books WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if (!empty($cover) && file_exists($uploadDir . $cover)) {
                @unlink($uploadDir . $cover);
            }
            set_flash('Buku berhasil dihapus.');
        } else {
            set_flash('Gagal menghapus buku: ' . $conn->error, 'danger');
        }
        header("Location: admin.php");
        exit();
    }
}

// Ambil daftar buku
$result = $conn->query("SELECT * FROM books ORDER BY id DESC");
$flash = get_flash();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Kelola Buku</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        :root{
            --ungu-dark: #0080ffff;
            --ungu: #0492ffff;
            --ungu-light: #003ba1ff;
            --bg: #f7f4fb;
            --white: #ffffff;
            --danger: #d63384;
        }
        *{box-sizing:border-box}
        body{
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            margin:0;
            padding:0 16px 50px;
            color: #0361f7ff;
        }
        nav{
            background: var(--ungu);
            color: white;
            padding: 14px 20px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            border-bottom: 4px solid rgba(0, 102, 255, 0.15);
            border-radius: 0 0 10px 10px;
            margin-bottom: 18px;
        }
        nav h1{font-size:1.05rem;margin:0}
        nav .nav-actions{display:flex; gap:10px; align-items:center;}
        .btn{
            display:inline-block;
            background: var(--ungu);
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            cursor:pointer;
            text-decoration:none;
            font-weight:600;
        }
        .btn.secondary{background:#580d9e}
        .btn.ghost{background:transparent; border:1px solid rgba(255,255,255,0.18)}
        .container{max-width:1100px;margin:18px auto;padding:18px;background:var(--white);border-radius:12px;box-shadow:0 8px 28px rgba(106,13,173,0.06)}
        .flex{display:flex;gap:12px;align-items:center}
        .actions-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px}
        .searchbox{display:flex; gap:8px; align-items:center}
        input[type="text"], input[type="number"], textarea{
            padding:8px 10px;border-radius:8px;border:1px solid #e6d8f5;background:#fff;min-width:160px;
        }
        table{width:100%;border-collapse:collapse;margin-top:8px}
        th, td{padding:10px;border:1px solid #e6d8f5;text-align:left;vertical-align:middle}
        th{background:var(--ungu);color:white}
        tr:nth-child(even){background:var(--ungu-light)}
        img.thumb{width:48px;height:64px;object-fit:cover;border-radius:4px;border:1px solid #eee}
        .small-muted{font-size:0.9rem;color:#7b4a9f}
        /* modal */
        .modal-backdrop{position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.45);display:none;align-items:center;justify-content:center;z-index:999}
        .modal{background:var(--white);padding:20px;border-radius:12px;max-width:520px;width:95%;box-shadow:0 12px 40px rgba(0,0,0,0.2)}
        .modal h3{margin-top:0;color:var(--ungu-dark)}
        .form-row{display:flex;gap:8px;margin-bottom:10px}
        .form-row .col{flex:1}
        .modal .actions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px}
        .alert{padding:10px;border-radius:8px;margin-bottom:12px}
        .alert.success{background:linear-gradient(90deg,#fff,#f6f0ff);border-left:4px solid var(--ungu);color:var(--ungu-dark)}
        .alert.danger{background:#fff0f6;border-left:4px solid var(--danger);color:var(--danger)}
        .tiny{font-size:0.85rem;color:#6a3e86}
        .no-data{padding:18px;text-align:center;color:#7b4a9f}
        /* responsive */
        @media(max-width:720px){
            .form-row{flex-direction:column}
            img.thumb{width:40px;height:56px}
            nav h1{font-size:0.95rem}
        }
    </style>
</head>
<body>

<nav>
    <h1>Admin Panel • Kelola Katalog Buku</h1>
    <div class="nav-actions">
        <a href="ManageAdmin.php" class="btn ghost">Kelola Admin</a>
        <a href="logout.php" class="btn secondary">Logout</a>
    </div>
</nav>

<div class="container">
    <?php if ($flash): ?>
        <div class="alert <?= $flash['type'] === 'danger' ? 'danger' : 'success' ?>">
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <div class="actions-top">
        <div class="flex">
            <button id="btnAdd" class="btn">+ Tambah Buku</button>
            <div class="tiny" style="margin-left:10px">Klik Edit untuk mengubah, Hapus untuk menghapus</div>
        </div>

        <div class="searchbox">
            <input id="searchInput" type="text" placeholder="Cari judul atau penulis..." oninput="filterTable()">
        </div>
    </div>

    <?php if ($result->num_rows == 0): ?>
        <div class="no-data">Belum ada data buku.</div>
    <?php else: ?>
        <table id="booksTable">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Tahun</th>
                    <th>Deskripsi</th>
                    <th style="width:160px;text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-id="<?= $row['id'] ?>">
                        <td>
                            <?php if (!empty($row['cover']) && file_exists($uploadDir . $row['cover'])): ?>
                                <img src="uploads/<?= htmlspecialchars($row['cover']) ?>" alt="cover" class="thumb">
                            <?php else: ?>
                                <div class="small-muted">No cover</div>
                            <?php endif; ?>
                        </td>
                        <td class="col-title"><?= htmlspecialchars($row['title']) ?></td>
                        <td class="col-author"><?= htmlspecialchars($row['author']) ?></td>
                        <td class="col-year"><?= htmlspecialchars($row['year']) ?></td>
                        <td class="col-desc"><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                        <td style="text-align:center">
                            <button class="btn editBtn" data-id="<?= $row['id'] ?>">Edit</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button class="btn secondary" name="delete" onclick="return confirm('Hapus buku ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Modal Backdrop -->
<div id="modalBackdrop" class="modal-backdrop">
    <!-- Add / Edit Modal content will be injected or toggled -->
    <div id="modal" class="modal" role="dialog" aria-modal="true">
        <h3 id="modalTitle">Tambah Buku</h3>
        <form id="modalForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="bookId">
            <div class="form-row">
                <div class="col">
                    <label col="12" class="tiny">Judul</label>
                    <input col="12" type="text" name="title" id="title" required>
                </div>
                <div class="col">
                    <label class="tiny">Penulis</label>
                    <input type="text" name="author" id="author" required>
                </div>
            </div>

            <div class="form-row">
                <div class="col">
                    <label class="tiny">Tahun</label>
                    <input type="number" name="year" id="year" required min="1000" max="9999">
                </div>
                <div class="col">
                    <label class="tiny">Cover (jpg/png/gif/webp)</label>
                    <input type="file" name="cover" id="cover" accept="image/*">
                </div>
            </div>

            <div class="form-row">
                <div style="flex:1">
                    <label class="tiny">Deskripsi</label>
                    <textarea name="description" id="description" rows="4"></textarea>
                </div>
            </div>

            <div class="actions">
                <button type="button" class="btn ghost" id="modalClose">Batal</button>
                <button type="submit" class="btn" id="modalSubmit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Helpers
    const modalBackdrop = document.getElementById('modalBackdrop');
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modalTitle');
    const modalForm = document.getElementById('modalForm');
    const modalSubmit = document.getElementById('modalSubmit');
    const modalClose = document.getElementById('modalClose');

    // Buttons
    document.getElementById('btnAdd').addEventListener('click', () => openAddModal());
    modalClose.addEventListener('click', closeModal);
    modalBackdrop.addEventListener('click', (e) => { if (e.target === modalBackdrop) closeModal(); });

    function openAddModal() {
        modalBackdrop.style.display = 'flex';
        modalTitle.textContent = 'Tambah Buku';
        modalForm.reset();
        document.getElementById('bookId').value = '';
        // set form to post add
        clearFormAction();
        addHiddenAction('add');
        modalSubmit.textContent = 'Tambah';
    }

    // Attach edit buttons
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = this.closest('tr');
            const id = this.getAttribute('data-id');
            const title = tr.querySelector('.col-title').innerText.trim();
            const author = tr.querySelector('.col-author').innerText.trim();
            const year = tr.querySelector('.col-year').innerText.trim();
            const desc = tr.querySelector('.col-desc').innerText.trim().replaceAll('\n','\n');

            modalBackdrop.style.display = 'flex';
            modalTitle.textContent = 'Edit Buku';
            document.getElementById('bookId').value = id;
            document.getElementById('title').value = title;
            document.getElementById('author').value = author;
            document.getElementById('year').value = year;
            document.getElementById('description').value = desc;
            // clear file input
            document.getElementById('cover').value = '';

            // set form to update
            clearFormAction();
            addHiddenAction('update');
            modalSubmit.textContent = 'Simpan Perubahan';
        });
    });

    function closeModal() {
        modalBackdrop.style.display = 'none';
    }

    function addHiddenAction(name) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = '1';
        input.id = 'action_' + name;
        modalForm.appendChild(input);
    }

    function clearFormAction() {
        ['add','update'].forEach(n=>{
            const el = document.getElementById('action_' + n);
            if (el) el.remove();
        });
    }

    // Search/filter simple
    function filterTable() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        const trs = document.querySelectorAll('#booksTable tbody tr');
        trs.forEach(tr => {
            const title = tr.querySelector('.col-title').innerText.toLowerCase();
            const author = tr.querySelector('.col-author').innerText.toLowerCase();
            if (title.includes(q) || author.includes(q)) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        });
    }

    // On submit: decide if add or update — form already has hidden input
    modalForm.addEventListener('submit', function(e) {
        // Let form submit normally (POST) to server; the PHP will handle add/update
        // But we can optionally show loading state
        modalSubmit.disabled = true;
        modalSubmit.textContent = 'Menyimpan...';
    });

    // Accessibility: close on Esc
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
</script>

</body>
</html>