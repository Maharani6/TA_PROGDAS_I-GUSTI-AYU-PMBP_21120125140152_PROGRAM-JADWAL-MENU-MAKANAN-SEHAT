<?php
class User {
    private string $gender; // 'L' or 'P'
    private int $age;
    private float $weight; // kg
    private float $height; // cm
    private int $activityLevel; // 1..5

    public function __construct(string $gender, int $age, float $weight, float $height, int $activityLevel) {
        $this->gender = strtoupper($gender);
        $this->age = $age;
        $this->weight = $weight;
        $this->height = $height;
        $this->activityLevel = $activityLevel;
    }

    public function getGender(): string { return $this->gender; }
    public function getAge(): int { return $this->age; }
    public function getWeight(): float { return $this->weight; }
    public function getHeight(): float { return $this->height; }
    public function getActivityLevel(): int { return $this->activityLevel; }

    public function calculateBMR(): float {
        if ($this->gender === 'L') {
            return 88.36 + (13.4 * $this->weight) + (4.8 * $this->height) - (5.7 * $this->age);
        }
        return 447.6 + (9.2 * $this->weight) + (3.1 * $this->height) - (4.3 * $this->age);
    }

    public function activityFactor(): float {
        switch ($this->activityLevel) {
            case 1: return 1.2;
            case 2: return 1.375;
            case 3: return 1.55;
            case 4: return 1.725;
            case 5: return 1.9;
            default: return 1.2;
        }
    }

    public function calculateTDEE(): float {
        return $this->calculateBMR() * $this->activityFactor();
    }
}