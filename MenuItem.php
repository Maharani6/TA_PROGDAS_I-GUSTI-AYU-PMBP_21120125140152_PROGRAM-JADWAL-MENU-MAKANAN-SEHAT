<?php
abstract class MenuItem {
    private string $name;
    private int $calories;
    private string $category;

    public function __construct(string $name, int $calories, string $category) {
        $this->name = $name;
        $this->calories = $calories;
        $this->category = $category;
    }

    public function getName(): string { return $this->name; }
    public function getCalories(): int { return $this->calories; }
    public function getCategory(): string { return $this->category; }

    abstract public function getDetails(): string;
}

