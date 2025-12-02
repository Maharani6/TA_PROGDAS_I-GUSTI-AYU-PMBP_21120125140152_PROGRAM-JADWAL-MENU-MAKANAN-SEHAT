<?php
// classes/FoodDatabase.php
class FoodDatabase {
    public static function carbs(): array {
        return [
            ['Nasi Merah', 165], ['Nasi Putih', 195], ['Nasi Jagung', 150],

        ];
    }

    public static function eggs(): array {
        return [
            ['Telur Rebus',80], ['Omelet',120], ['Telur Ceplok',92], ['scrambled egg', 148]
        ];
    }

    public static function breads(): array {
        return [
            ['Sereal',124], ['Roti Tawar + Selai',200], ['Roti Gandum',120], ['Jagung Rebus',106], ['Ubi Rebus',115],
            ['Talas Rebus',130],  ['Kentang Rebus',120],
        ];
    }

    public static function vegetables(): array {
        return [
            ['Sayur Sop',90], ['Tumis Kangkung',100], ['Tumis Jamur', 80],
            ['Tumis Sawi Tahu',200], ['Capcay',170], 
            ['Urap Sayur',230],  ['Sayur Bening Bayam',45]
        ];
    }

    public static function proteins(): array {
        return [
            ['Ayam Geprek',360], ['Ayam Goreng ungkep',245], ['Ayam Goreng Tepung',400], 
            ['Sate Ayam + Bumbu Kacang',405], ['Tahu Goreng',90], ['Tempe Goreng',90], ['Ikan Goreng',220], 
            ['Ikan Lele',105], ['Tempe Orek', 75], ['Ayam Bali Crispy', 190], ['Ayam Balap Crispy', 190], 
            ['Ayam Bali Biasa', 160]
        ];
    }

    public static function fruits(): array {
        return [
            ['Pisang',90], ['Apel',95], ['Mangga',130],
            ['Salad Sayur',140], ['Alpukat',240], ['Jeruk',60],
            ['Salak',77], ['Pir',100], ['Nanas',85], ['Anggur',70]
        ];
    }
    
    // public static function vegfruits(): array {
    // return array_merge(self::vegetables(), self::fruits());
    // }
}

