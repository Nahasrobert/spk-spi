<?php
require_once 'db.php';
error_reporting(0);
session_start();

// --- 1. Ambil data dari database (termasuk atribut)
$query = "
    SELECT 
        p.id_pengajuan,
        p.nama_lengkap,
        k.kode_kriteria,
        k.nama_kriteria,
        k.atribut,        -- ambil atribut benefit / cost
        np.nilai
    FROM nilai_pengajuan np
    JOIN pengajuan_bantuan p ON np.id_pengajuan = p.id_pengajuan
    JOIN subkriteria s ON np.id_subkriteria = s.id_subkriteria
    JOIN kriteria k ON s.id_kriteria = k.id_kriteria
    ORDER BY p.id_pengajuan, k.id_kriteria
";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

// --- 2. Susun matriks keputusan
$dataMatrix   = [];   // [id_pengajuan][kode_kriteria] = nilai
$namaAlt      = [];   // mapping id_pengajuan => nama
$kriteriaList = [];   // kode_kriteria => nama_kriteria
$kriteriaType = [];   // kode_kriteria => benefit/cost

while ($row = mysqli_fetch_assoc($result)) {
    $idp    = $row['id_pengajuan'];
    $nama   = $row['nama_lengkap'];
    $kode   = $row['kode_kriteria'];
    $label  = $row['nama_kriteria'];
    $nilai  = floatval($row['nilai']);
    $atribut = strtolower($row['atribut']); // benefit atau cost

    $namaAlt[$idp]       = $nama;
    $kriteriaList[$kode] = $label;
    $kriteriaType[$kode] = $atribut;

    // ambil nilai tertinggi kalau ada duplikat
    if (!isset($dataMatrix[$idp][$kode]) || $nilai > $dataMatrix[$idp][$kode]) {
        $dataMatrix[$idp][$kode] = $nilai;
    }
}

// Urutkan kriteria berdasarkan kode (C1, C2, C3 ...)
uksort($kriteriaList, function ($a, $b) {
    return intval(substr($a, 1)) - intval(substr($b, 1));
});

// --- 3. Normalisasi
$norm = [];
foreach ($kriteriaList as $k => $label) {
    $col = [];
    foreach ($dataMatrix as $idp => $vals) {
        $col[] = $vals[$k] ?? 0;
    }

    if ($kriteriaType[$k] == "benefit") {
        $max = max($col);
        foreach ($dataMatrix as $idp => $vals) {
            $norm[$idp][$k] = $max > 0 ? ($vals[$k] ?? 0) / $max : 0;
        }
    } else { // cost
        $min = min($col);
        foreach ($dataMatrix as $idp => $vals) {
            $norm[$idp][$k] = ($vals[$k] ?? 0) > 0 ? $min / ($vals[$k]) : 0;
        }
    }
}

// --- 4. Rata-rata normalisasi tiap kriteria
$mean = [];
foreach ($kriteriaList as $k => $label) {
    $col = array_column($norm, $k);
    $mean[$k] = array_sum($col) / count($col);
}

// --- 5. Deviasi & Preference Index (PI)
$PI = [];
foreach ($kriteriaList as $k => $label) {
    $col = array_column($norm, $k);
    $dev = 0;
    foreach ($col as $v) {
        $dev += abs($v - $mean[$k]);
    }
    $PI[$k] = 1 - ($dev / count($col));
}

// --- 6. Bobot Kriteria
$totalPI = array_sum($PI);
$W = [];
foreach ($PI as $k => $v) {
    $W[$k] = $totalPI > 0 ? $v / $totalPI : 0;
}

// --- 7. Nilai PSI tiap alternatif
$nilaiPSI = [];
foreach ($norm as $idp => $vals) {
    $sum = 0;
    foreach ($vals as $k => $v) {
        $sum += $v * $W[$k];
    }
    $nilaiPSI[$idp] = $sum;
}

// --- 8. Ranking (dense ranking, nilai sama => ranking sama)
arsort($nilaiPSI);

$ranking = 0;
$lastValue = null;
$rankMap = [];

foreach ($nilaiPSI as $idp => $nilai) {
    if ($lastValue === null || $nilai < $lastValue) {
        $ranking++;
    }
    $rankMap[$idp] = $ranking;
    $lastValue = $nilai;
}

// --- 9. Simpan ke DB
foreach ($rankMap as $idp => $rank) {
    $nilai = $nilaiPSI[$idp];
    $cek = mysqli_query($conn, "SELECT 1 FROM hasil_psi WHERE id_pengajuan = $idp LIMIT 1");
    if (mysqli_num_rows($cek) > 0) {
        $sql = "UPDATE hasil_psi SET nilai_psi = $nilai, ranking = $rank WHERE id_pengajuan = $idp";
    } else {
        $sql = "INSERT INTO hasil_psi (id_pengajuan, nilai_psi, ranking)
                VALUES ($idp, $nilai, $rank)";
    }
    mysqli_query($conn, $sql);
}
?>

<?php include('header.php'); ?>
<div class="main-panel">
    <div class="content-wrapper">
        <h3>Hasil Perhitungan Metode PSI</h3>

        <!-- 1. Matriks Keputusan -->
        <h4>1. Matriks Keputusan</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Alternatif</th>
                    <?php foreach ($kriteriaList as $k => $label): ?>
                        <th><?= htmlspecialchars($k) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataMatrix as $idp => $vals): ?>
                    <tr>
                        <td><?= htmlspecialchars($namaAlt[$idp]) ?></td>
                        <?php foreach ($kriteriaList as $k => $label): ?>
                            <td><?= $vals[$k] ?? 0 ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- 2. Normalisasi -->
        <h4>2. Normalisasi</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Alternatif</th>
                    <?php foreach ($kriteriaList as $k => $label): ?>
                        <th><?= htmlspecialchars($k) ?> (<?= ucfirst($kriteriaType[$k]) ?>)</th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($norm as $idp => $vals): ?>
                    <tr>
                        <td><?= htmlspecialchars($namaAlt[$idp]) ?></td>
                        <?php foreach ($kriteriaList as $k => $label): ?>
                            <td><?= number_format($vals[$k], 4) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- 3. Mean & PI -->
        <h4>3. Mean dan Preference Index (PI)</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kriteria</th>
                    <th>Mean</th>
                    <th>PI</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kriteriaList as $k => $label): ?>
                    <tr>
                        <td><?= htmlspecialchars($k) ?> - <?= htmlspecialchars($label) ?></td>
                        <td><?= number_format($mean[$k], 4) ?></td>
                        <td><?= number_format($PI[$k], 4) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- 4. Bobot Kriteria -->
        <h4>4. Bobot Kriteria (W)</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kriteria</th>
                    <th>Bobot</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($W as $k => $val): ?>
                    <tr>
                        <td><?= htmlspecialchars($k) ?> - <?= htmlspecialchars($kriteriaList[$k]) ?></td>
                        <td><?= number_format($val, 4) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- 5. Nilai PSI tiap Alternatif -->
        <h4>5. Nilai PSI Alternatif</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Alternatif</th>
                    <th>Nilai PSI</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nilaiPSI as $idp => $val): ?>
                    <tr>
                        <td><?= htmlspecialchars($namaAlt[$idp]) ?></td>
                        <td><?= number_format($val, 4) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- 6. Ranking -->
        <h4>6. Hasil Ranking Akhir</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Ranking</th>
                    <th>Alternatif</th>
                    <th>Nilai PSI</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rankMap as $idp => $rank): ?>
                    <tr <?= $rank == 1 ? 'style="background-color:#d4edda;"' : '' ?>>
                        <td><?= $rank ?></td>
                        <td><?= htmlspecialchars($namaAlt[$idp]) ?></td>
                        <td><?= number_format($nilaiPSI[$idp], 4) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include('footer.php'); ?>
</div>