<?php
require_once __DIR__ . '/MenuItem.php';
class BreakfastItem extends MenuItem {
    public function getDetails(): string {
        return $this->getName() . " (" . $this->getCategory() . ") - " . $this->getCalories() . " kkal";
    }
}
