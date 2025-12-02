<?php
require_once __DIR__ . '/FoodDatabase.php';
require_once __DIR__ . '/MenuItem.php';
require_once __DIR__ . '/BreakfastItem.php';
require_once __DIR__ . '/LunchItem.php';
require_once __DIR__ . '/DinnerItem.php';

class MainController {

    private array $days = ["Senin","Selasa","Rabu","Kamis","Jumat","Sabtu","Minggu"];

    // Maksimum kemunculan per item dalam seminggu (MODE B)
    private int $MAX_PER_WEEK = 2;

    // -----------------------
    // Filter kandidat yang boleh dipakai
    // -----------------------
    private function filterAllowed(array $list, array $counts, array $forbiddenNames): array {
        $candidates = [];
        foreach ($list as $item) {
            $name = $item[0];
            $usedCount = $counts[$name] ?? 0;
            if (in_array($name, $forbiddenNames, true)) continue;
            if ($usedCount >= $this->MAX_PER_WEEK) continue;
            $candidates[] = $item;
        }
        return $candidates;
    }

    // -----------------------
    // Ambil kandidat terbaik tetapi beri variasi:
    // - ambil top N paling dekat, lalu random diantara top N
    // - kalau kosong -> fallback random (mengabaikan batas)
    // -----------------------
    private function pickClosestAllowed(array $list, int $target, array &$counts, array $forbiddenNames = [], int $topN = 3): array {
        $candidates = $this->filterAllowed($list, $counts, $forbiddenNames);

        if (!empty($candidates)) {
            // hitung perbedaan dan urutkan
            usort($candidates, function($a, $b) use ($target) {
                return abs($a[1] - $target) <=> abs($b[1] - $target);
            });

            // ambil top N (atau sebanyak available)
            $top = array_slice($candidates, 0, min($topN, count($candidates)));

            // pilih random di antara top untuk variasi
            $pick = $top[array_rand($top)];

            // update count
            $counts[$pick[0]] = ($counts[$pick[0]] ?? 0) + 1;
            return $pick;
        }

        // fallback: ambil random dari list yang tidak terlarang (jika ada)
        $fallback = [];
        foreach ($list as $item) {
            if (!in_array($item[0], $forbiddenNames, true)) $fallback[] = $item;
        }
        if (empty($fallback)) $fallback = $list;
        $pick = $fallback[array_rand($fallback)];
        $counts[$pick[0]] = ($counts[$pick[0]] ?? 0) + 1;
        return $pick;
    }

    // -----------------------
    // Helper: ambil total kalori dari array item [ [name,cal], ... ]
    // -----------------------
    private function totalCal(array $items): int {
        return array_sum(array_map(fn($x) => $x[1], $items));
    }

    // -----------------------
    // Generate weekly schedule (mode B)
    // -----------------------
    public function generateWeeklySchedule(User $user): array {

        $tdee = $user->calculateTDEE();
        // alokasi B/L/D
        $allocB = (int) round($tdee * 0.30);
        $allocL = (int) round($tdee * 0.40);
        $allocD = (int) round($tdee * 0.30);

        // Ambil data makanan
        $carbs    = FoodDatabase::carbs();
        $vegs     = FoodDatabase::vegetables();
        $breads   = FoodDatabase::breads();
        $eggs     = FoodDatabase::eggs();
        $proteins = FoodDatabase::proteins();
        $fruits   = FoodDatabase::fruits();

        // Counters: berapa kali item sudah dipakai selama minggu berjalan
        $usedCounts = [];

        // prev per sesi untuk mencegah consecutive per menu/session
        $prev = [
            'breakfast' => ['fruit' => null, 'bread' => null, 'egg' => null],
            'lunch'     => ['carb' => null, 'veg' => null, 'protein' => null, 'fruit' => null],
            'dinner'    => ['carb' => null, 'veg' => null, 'protein' => null]
        ];

        $schedule = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $this->days[$i];

            // Shuffle source lists tiap hari agar variasi urutan kandidat (membantu fallback)
            shuffle($carbs);
            shuffle($vegs);
            shuffle($breads);
            shuffle($eggs);
            shuffle($proteins);
            shuffle($fruits);

           // ---------- BREAKFAST: fruit + fruit + bread + egg ----------
            $forbidFruit1 = $prev['breakfast']['fruit'] ? [$prev['breakfast']['fruit']] : [];
            $pickFruit1 = $this->pickClosestAllowed($fruits, (int) round($allocB * 0.25), $usedCounts, $forbidFruit1);

            // Buah kedua: larang buah yg sama dgn buah pertama
            $forbidFruit2 = [$pickFruit1[0]];
            $pickFruit2 = $this->pickClosestAllowed($fruits, (int) round($allocB * 0.25), $usedCounts, $forbidFruit2);

            $forbidBread = $prev['breakfast']['bread'] ? [$prev['breakfast']['bread']] : [];
            $pickBread = $this->pickClosestAllowed($breads, (int) round($allocB * 0.30), $usedCounts, $forbidBread);

            $forbidEgg = $prev['breakfast']['egg'] ? [$prev['breakfast']['egg']] : [];
            $pickEgg = $this->pickClosestAllowed($eggs, (int) round($allocB * 0.20), $usedCounts, $forbidEgg);

            // susun items
            $breakItems = [$pickFruit1, $pickFruit2, $pickBread, $pickEgg];
            $breakTotal = $this->totalCal($breakItems);

            // prev untuk anti consecutive
            $prev['breakfast']['fruit'] = $pickFruit2[0];   // terakhir dipakai buah kedua
            $prev['breakfast']['bread'] = $pickBread[0];
            $prev['breakfast']['egg']   = $pickEgg[0];

            // ---------- LUNCH: carbs + veg + protein + fruit ----------
            $forbidCarb = $prev['lunch']['carb'] ? [$prev['lunch']['carb']] : [];
            $forbidVegL = $prev['lunch']['veg'] ? [$prev['lunch']['veg']] : [];
            $forbidProt = $prev['lunch']['protein'] ? [$prev['lunch']['protein']] : [];
            $forbidFruit = $prev['lunch']['fruit'] ? [$prev['lunch']['fruit']] : [];

            $pickCarbL  = $this->pickClosestAllowed($carbs,   (int) round($allocL * 0.35), $usedCounts, $forbidCarb);
            $pickVegL   = $this->pickClosestAllowed($vegs,    (int) round($allocL * 0.20), $usedCounts, $forbidVegL);
            $pickProtL  = $this->pickClosestAllowed($proteins,(int) round($allocL * 0.30), $usedCounts, $forbidProt);
            $pickFruitL = $this->pickClosestAllowed($fruits,  (int) round($allocL * 0.15), $usedCounts, $forbidFruit);

            $lunchItems = [$pickCarbL, $pickVegL, $pickProtL, $pickFruitL];
            $lCal = $this->totalCal($lunchItems);

            $prev['lunch']['carb'] = $pickCarbL[0];
            $prev['lunch']['veg'] = $pickVegL[0];
            $prev['lunch']['protein'] = $pickProtL[0];
            $prev['lunch']['fruit'] = $pickFruitL[0];

            // ---------- DINNER: carbs + veg + protein ----------
            $forbidCarbD = $prev['dinner']['carb'] ? [$prev['dinner']['carb']] : [];
            $forbidVegD  = $prev['dinner']['veg'] ? [$prev['dinner']['veg']] : [];
            $forbidProtD = $prev['dinner']['protein'] ? [$prev['dinner']['protein']] : [];

            $pickCarbD = $this->pickClosestAllowed($carbs,   (int) round($allocD * 0.40), $usedCounts, $forbidCarbD);
            $pickVegD  = $this->pickClosestAllowed($vegs,    (int) round($allocD * 0.20), $usedCounts, $forbidVegD);
            $pickProtD = $this->pickClosestAllowed($proteins,(int) round($allocD * 0.40), $usedCounts, $forbidProtD);

            $dinnerItems = [$pickCarbD, $pickVegD, $pickProtD];
            $dCal = $this->totalCal($dinnerItems);

            $prev['dinner']['carb'] = $pickCarbD[0];
            $prev['dinner']['veg']  = $pickVegD[0];
            $prev['dinner']['protein'] = $pickProtD[0];

            // Build strings & calories for UI (index.php expects name+cal)
            $bNames = array_map(fn($x) => $x[0], $breakItems);
            $bCal = $this->totalCal($breakItems);

            $lNames = array_map(fn($x) => $x[0], $lunchItems);

            $dNames = array_map(fn($x) => $x[0], $dinnerItems);

            // ===== calculate remaining snack calories (per hari) =====
            $totalDay = $bCal + $lCal + $dCal;
            // round tdee to integer for user-friendly math
            $tdeeInt = (int) round($tdee);
            $remaining = max(0, $tdeeInt - $totalDay);

            // store as in index.php expected format
            $schedule[$day] = [
                'tdee' => round($tdee, 3),
                'breakfast' => ['name' => implode(", ", $bNames), 'cal' => $bCal, 'note' => implode(", ", $bNames)],
                'lunch'     => ['name' => implode(", ", $lNames), 'cal' => $lCal, 'note' => implode(", ", $lNames)],
                'dinner'    => ['name' => implode(", ", $dNames), 'cal' => $dCal, 'note' => implode(", ", $dNames)],
                'allocated' => ['breakfast' => $allocB, 'lunch' => $allocL, 'dinner' => $allocD],
                'snack_remaining' => $remaining
            ];
        }

        return $schedule;
    }
}
