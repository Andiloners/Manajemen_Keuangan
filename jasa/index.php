<?php
include "../config/database.php";
include "../includes/auth.php"; // pastikan login
$pageTitle = "Pendapatan Mekanik";

// Filter tanggal (optional)
$filter = "";
if (!empty($_GET['dari']) && !empty($_GET['sampai'])) {
    $dari   = mysqli_real_escape_string($conn, $_GET['dari']);
    $sampai = mysqli_real_escape_string($conn, $_GET['sampai']);
    $filter = "WHERE s.tanggal BETWEEN '$dari' AND '$sampai'";
}

$sql = "
    SELECT u.id, u.username, SUM(s.biaya_jasa) AS total_jasa
    FROM servis s
    JOIN users u ON s.id_user = u.id
    $filter
    GROUP BY u.id
    ORDER BY total_jasa DESC
";
$result = mysqli_query($conn, $sql);
?>
<?php include "../includes/header.php"; ?>
<div class="card">
    <h2>Pendapatan Mekanik</h2>
    <form method="GET" class="form-inline" style="margin-bottom:16px;">
        <label for="dari">Dari:</label>
        <input type="date" id="dari" name="dari" value="<?= htmlspecialchars($_GET['dari'] ?? '') ?>">
        <label for="sampai">Sampai:</label>
        <input type="date" id="sampai" name="sampai" value="<?= htmlspecialchars($_GET['sampai'] ?? '') ?>">
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
    <table>
        <tr>
            <th>No</th>
            <th>Nama Mekanik</th>
            <th>Total Pendapatan (Rp)</th>
        </tr>
        <?php $no = 1; while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><a href="detail.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['username']) ?></a></td>
            <td><?= number_format($row['total_jasa'], 0, ',', '.') ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<?php include "../includes/footer.php"; ?>
