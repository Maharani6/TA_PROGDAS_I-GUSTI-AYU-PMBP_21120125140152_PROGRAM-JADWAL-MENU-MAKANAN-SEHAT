<?php
// index.php
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/MainController.php';

$errors = [];
$resultSchedule = null;

// Default input
$input = [
    'gender' => '',
    'age' => '',
    'weight' => '',
    'height' => '',
    'activity' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['gender'] = trim($_POST['gender'] ?? '');
    $input['age'] = intval($_POST['age'] ?? 0);
    $input['weight'] = floatval($_POST['weight'] ?? 0);
    $input['height'] = floatval($_POST['height'] ?? 0);
    $input['activity'] = intval($_POST['activity'] ?? 1);

    // Validasi
    if (!in_array(strtoupper($input['gender']), ['L', 'P'])) {
        $errors[] = "Masukkan jenis kelamin 'L' atau 'P'.";
    }
    if ($input['age'] <= 0) $errors[] = "Umur harus lebih dari 0.";
    if ($input['weight'] <= 0) $errors[] = "Berat badan harus lebih dari 0.";
    if ($input['height'] <= 0) $errors[] = "Tinggi badan harus lebih dari 0.";
    if ($input['activity'] < 1 || $input['activity'] > 5) {
        $errors[] = "Tingkat aktivitas harus 1–5.";
    }

    // Jika lolos validasi → hitung
    if (empty($errors)) {
        $user = new User(
            strtoupper($input['gender']),
            $input['age'],
            $input['weight'],
            $input['height'],
            $input['activity']
        );

        $main = new MainController();
        $resultSchedule = $main->generateWeeklySchedule($user);
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Jadwal Makanan Sehat 7 Hari</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background:#f8fafc; }
        .card { background:white; padding:16px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.1); margin-bottom:20px; }
        label { display:block; margin-top:10px; }
        input, select {
            width:100%; padding:8px; margin-top:4px;
            border:1px solid #d1d5db; border-radius:4px;
        }
        button {
            padding:10px 14px; margin-top:12px;
            background:#2563eb; color:white; border:none;
            border-radius:6px; cursor:pointer;
        }
        .error { color:#b91c1c; margin-bottom:10px; }
        .grid { display:grid; gap:14px; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); }
        .day { background:#fcfeff; border-left:4px solid #60a5fa; padding:12px; border-radius:6px; }
        .meal-card { padding:10px; border-radius:8px; margin:8px 0; }
        .breakfast { background:#dbeafe; border-left:5px solid #3b82f6; }
        .lunch { background:#dcfce7; border-left:5px solid #22c55e; }
        .dinner { background:#ffe4e6; border-left:5px solid #e11d48; }
        small { color:#6b7280; }
        table.snack { width:100%; border-collapse:collapse; margin-top:12px; }
        table.snack th, table.snack td { border:1px solid #e5e7eb; padding:8px; text-align:left; }
        .rem { background:#fffae6; padding:8px; border-radius:6px; display:inline-block; margin-top:6px; }
    </style>
</head>

<body>

<div class="card">
    <h1>Jadwal Makanan Sehat (7 Hari)</h1>
    <p>Masukkan data untuk menghitung kebutuhan kalori dan jadwal makanan otomatis.</p>

    <?php if (!empty($errors)): ?>
        <div class="card" style="background:#fee2e2;">
            <strong class="error">Input tidak valid:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <label>Jenis Kelamin (L/P)
            <input type="text" name="gender" value="<?= htmlspecialchars($input['gender']) ?>" maxlength="1">
        </label>

        <label>Umur (tahun)
            <input type="number" name="age" value="<?= htmlspecialchars($input['age']) ?>" min="1">
        </label>

        <label>Berat Badan (kg)
            <input type="number" name="weight" step="0.1" value="<?= htmlspecialchars($input['weight']) ?>">
        </label>

        <label>Tinggi Badan (cm)
            <input type="number" name="height" step="0.1" value="<?= htmlspecialchars($input['height']) ?>">
        </label>

        <label>Tingkat Aktivitas
            <select name="activity">
                <?php
                $labels = [
                    1 => "1 - Sangat sedentari",
                    2 => "2 - Ringan",
                    3 => "3 - Sedang",
                    4 => "4 - Aktif",
                    5 => "5 - Sangat aktif"
                ];
                foreach ($labels as $val => $label):
                ?>
                    <option value="<?= $val ?>" <?= ($input['activity']==$val?'selected':'') ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit">Buat Jadwal Mingguan</button>
    </form>
</div>

<?php if ($resultSchedule !== null): ?>
    <div class="card">
        <h2>Hasil Jadwal Mingguan</h2>
        <p><small>TDEE dihitung otomatis. Alokasi: Sarapan 30%, Siang 40%, Malam 30%.</small></p>

        <div class="grid">
            <?php foreach ($resultSchedule as $day => $info): ?>
                <div class="day">
                    <strong><?= htmlspecialchars($day) ?></strong><br>
                    <small>Total TDEE: <strong><?= htmlspecialchars($info['tdee']) ?> kkal</strong></small>

                    <div class="meal-card breakfast">
                        <strong>Sarapan</strong><br>
                        <?= htmlspecialchars($info['breakfast']['name']) ?><br>
                        <small><?= htmlspecialchars($info['breakfast']['cal']) ?> kkal</small>
                    </div>

                    <div class="meal-card lunch">
                        <strong>Makan Siang</strong><br>
                        <?= htmlspecialchars($info['lunch']['name']) ?><br>
                        <small><?= htmlspecialchars($info['lunch']['cal']) ?> kkal</small>
                    </div>

                    <div class="meal-card dinner">
                        <strong>Makan Malam</strong><br>
                        <?= htmlspecialchars($info['dinner']['name']) ?><br>
                        <small><?= htmlspecialchars($info['dinner']['cal']) ?> kkal</small>
                    </div>

                    <p><small>
                        Alokasi (sarapan / makan siang / malam): 
                        <?= $info['allocated']['breakfast'] ?> /
                        <?= $info['allocated']['lunch'] ?> /
                        <?= $info['allocated']['dinner'] ?> kkal
                    </small></p>

                    <!-- NEW: sisa kalori untuk snack -->
                    <div class="rem">
                        <strong>Sisa kalori untuk snack:</strong>
                        <?= htmlspecialchars($info['snack_remaining']) ?> kkal
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- TABEL SNACK REKOMENDASI -->
        <div style="margin-top:18px;">
            <h3>Rekomendasi Snack (pilih sesuai sisa kalori)</h3>
            <table class="snack">
                <thead>
                    <tr><th>Snack</th><th>Perkiraan Kalori</th><th>Catatan</th></tr>
                </thead>
                <tbody>
                    <tr><td>Yogurt rendah lemak (150g)</td><td>90–120 kkal</td><td>Protein + probiotik</td></tr>
                    <tr><td>Roti gandum 1 slice + selai</td><td>120–160 kkal</td><td>Karbo ringan</td></tr>
                    <tr><td>Es Krim (1 scoop)</td><td>140–250 kkal</td><td>Manis & dingin</td></tr>
                    <tr><td>Keripik Singkong (30–40g)</td><td>150–220 kkal</td><td>Gurih renyah</td></tr>
                    <tr><td>Keripik Kentang (30–40g)</td><td>180–260 kkal</td><td>Snack populer</td></tr>
                    <tr><td>Keripik Pisang (30–40g)</td><td>150–230 kkal</td><td>Manis/gurih</td></tr>
                    <tr><td>Puding Cup</td><td>100–180 kkal</td><td>Lembut & manis</td></tr>
                    <tr><td>Basreng (30g)</td><td>150–250 kkal</td><td>Pedas gurih</td></tr>
                    <tr><td>Telur Gulung</td><td>80–120 kkal</td><td>Jajanan sederhana</td></tr>
                    <tr><td>Coklat batang kecil</td><td>120–200 kkal</td><td>Sweet treat</td></tr>
                    <tr><td>Tteokbokki Cup</td><td>280–450 kkal</td><td>Pedas & kenyang</td></tr>
                    <tr><td>Mochi (1 pcs)</td><td>90–140 kkal</td><td>Kenyal manis</td></tr>
                    <tr><td>Susu UHT (250ml)</td><td>110–160 kkal</td><td>Kaya kalsium</td></tr>
                    <tr><td>Kopi susu (1 cup)</td><td>120–250 kkal</td><td>Ngemil + ngopi</td></tr>
                    <tr><td>Minuman sirup (1 gelas)</td><td>80–150 kkal</td><td>Manis segar</td></tr>
                    <tr><td>Minuman soda (1 can)</td><td>140–200 kkal</td><td>Karbonasi</td></tr>
                    <tr><td>Cookies (2 pcs)</td><td>120–200 kkal</td><td>Manis renyah</td></tr>
                    <tr><td>Brownies slice kecil</td><td>180–260 kkal</td><td>Coklat moist</td></tr>
                    <tr><td>Croissant</td><td>230–330 kkal</td><td>Roti butter</td></tr>
                    <tr><td>Donat</td><td>220–340 kkal</td><td>Gorengan manis</td></tr>
                    <tr><td>Pastel</td><td>150–220 kkal</td><td>Isi sayur/ayam</td></tr>
                    <tr><td>Risoles mayo</td><td>180–250 kkal</td><td>Gurih creamy</td></tr>
                    <tr><td>Lemper</td><td>140–190 kkal</td><td>Isi ayam</td></tr>
                    <tr><td>Piscok</td><td>180–260 kkal</td><td>Pisang + coklat</td></tr>
                    <tr><td>Kroket</td><td>160–230 kkal</td><td>Kentang isi</td></tr>
                    <tr><td>Churros (3 pcs)</td><td>180–260 kkal</td><td>Manis gurih</td></tr>
                    <tr><td>Bolu slice</td><td>160–250 kkal</td><td>Cake lembut</td></tr>
                    <tr><td>Bakpao</td><td>150–220 kkal</td><td>Isi kacang/ayam</td></tr>
                    <tr><td>Pisang molen</td><td>190–260 kkal</td><td>Camilan manis</td></tr>
                    <tr><td>Tempe goreng (2 pcs)</td><td>100–180 kkal</td><td>Protein nabati</td></tr>
                    <tr><td>Tahu goreng (2 pcs)</td><td>90–150 kkal</td><td>Ringan gurih</td></tr>
                    <tr><td>Singkong goreng</td><td>150–240 kkal</td><td>Gurih kenyang</td></tr>
                    <tr><td>Cireng</td><td>160–220 kkal</td><td>Kenyang & chewy</td></tr>
                    <tr><td>Pisang goreng</td><td>180–260 kkal</td><td>Favorit semua</td></tr>
                    <tr><td>Bakwan goreng</td><td>120–200 kkal</td><td>Sayur renyah</td></tr>
                    <tr><td>Macaron (1 pcs)</td><td>70–110 kkal</td><td>Snack premium</td></tr>
                    <tr><td>Muffin kecil</td><td>160–240 kkal</td><td>Manis lembut</td></tr>
                    <tr><td>Tahu isi</td><td>150–220 kkal</td><td>Pedas gurih</td></tr>
                    <tr><td>Tahu bakso</td><td>160–250 kkal</td><td>Gurih & padat</td></tr>
                    <tr><td>Cheese stick goreng (10–12 btg)</td><td>120–180 kkal</td><td>Asin renyah</td></tr>
                    <tr><td>Roti bakar</td><td>250–380 kkal</td><td>Isi coklat/keju</td></tr>
                    <tr><td>Martabak manis (slice)</td><td>280–450 kkal</td><td>Sangat manis</td></tr>
                    <tr><td>Martabak telur (slice)</td><td>300–450 kkal</td><td>Gurih & berlemak</td></tr>
                    <tr><td>Sayur-sayuran (salad kecil)</td><td>60–120 kkal</td><td>Segar sehat</td></tr>
                    <tr><td>Buah campur (bowl kecil)</td><td>70–130 kkal</td><td>Fresh & manis</td></tr>
                    <tr><td>Indomie</td><td>350–380 kkal</td><td>Gurih & berlemak</td></tr>
                </tbody>
                </table>
            <p style="margin-top:8px;"><small>Tip: pilih snack yang mendekati angka "Sisa kalori" — bila sisa kecil (<50 kkal), pilih buah atau sayur.</small></p>
            <p style="margin-top:8px;"><small>     Nasi Merah dan Nasi Jagung dapat diganti Nasi Putih dengan penambahan 35-45 kalori.</small></p>
            <p style="margin-top:8px;"><small>     Sambal akan menyumbang 30 kalori per sendok makan, tergantung jenis sambal.</small></p>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
