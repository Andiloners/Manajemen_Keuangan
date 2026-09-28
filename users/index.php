<?php
include "../config/database.php";
include "../includes/auth.php";
include "../includes/upload.php";
requireAdmin();

$pageTitle = "Kelola Pengguna";
$base = getBaseUrl();

$totalAdmin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM users WHERE level='admin'"))['jml'];
$totalKasir = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM users WHERE level='kasir'"))['jml'];

$filter = $_GET['level'] ?? '';
$sql = "SELECT id, username, level, foto FROM users";
if ($filter === 'admin' || $filter === 'kasir') {
    $sql .= " WHERE level='$filter'";
}
$sql .= " ORDER BY level, username";
$data = mysqli_query($conn, $sql);

include "../includes/header.php";

$sukses = '';
if (isset($_GET['sukses'])) {
    $tipeBaru = $_GET['tipe'] ?? 'pengguna';
    $sukses = "Akun " . ucfirst($tipeBaru) . " berhasil ditambahkan.";
}
?>

<?php if ($sukses): ?>
<div class="alert alert-info"><?= $sukses ?></div>
<?php endif; ?>

<div class="alert alert-info">
    <strong>Hanya Admin</strong> yang dapat menambah, mengubah, dan menghapus akun pengguna.
</div>

<div class="card-grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-bottom: 20px;">
    <div class="stat-card-img red">
        <img src="<?= $base ?>assets/img/icons/user.svg" alt="Admin" class="stat-icon">
        <div>
            <h3>Total Admin</h3>
            <div class="value"><?= $totalAdmin ?></div>
        </div>
    </div>
    <div class="stat-card-img blue">
        <img src="<?= $base ?>assets/img/icons/pelanggan.svg" alt="Kasir" class="stat-icon">
        <div>
            <h3>Total Kasir</h3>
            <div class="value"><?= $totalKasir ?></div>
        </div>
    </div>
</div>

<div class="actions">
    <a href="tambah.php?tipe=admin" class="btn btn-danger">+ Tambah Admin</a>
    <a href="tambah.php?tipe=kasir" class="btn btn-primary">+ Tambah Kasir (User)</a>
</div>

<div class="filter-bar">
    <a href="index.php" class="btn btn-sm <?= $filter === '' ? 'btn-primary' : 'btn-warning' ?>">Semua</a>
    <a href="index.php?level=admin" class="btn btn-sm <?= $filter === 'admin' ? 'btn-primary' : 'btn-warning' ?>">Admin Saja</a>
    <a href="index.php?level=kasir" class="btn btn-sm <?= $filter === 'kasir' ? 'btn-primary' : 'btn-warning' ?>">Kasir Saja</a>
</div>

<div class="card">
    <table>
        <tr>
            <th>No</th>
            <th>Foto</th>
            <th>Username</th>
            <th>Level Akses</th>
            <th>Hak Akses</th>
            <th>Aksi</th>
        </tr>
        <?php $no = 1; while ($d = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td>
                <img src="<?= getProfilePhotoUrl($d['foto'] ?? null, $base) ?>" alt="Foto" class="profile-avatar-sm">
            </td>
            <td><?= htmlspecialchars($d['username']) ?></td>
            <td>
                <span class="badge badge-<?= $d['level'] ?>"><?= ucfirst($d['level']) ?></span>
            </td>
            <td style="font-size:0.85rem;color:#666;">
                <?php if ($d['level'] === 'admin'): ?>
                    Kelola semua data + pengguna
                <?php else: ?>
                    Hanya transaksi
                <?php endif; ?>
            </td>
            <td>
                <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <?php if ($d['id'] != $_SESSION['id']): ?>
                <a href="hapus.php?id=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus user <?= htmlspecialchars($d['username']) ?>?')">Hapus</a>
                <?php else: ?>
                <span style="font-size:0.75rem;color:#999;">(Anda)</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
