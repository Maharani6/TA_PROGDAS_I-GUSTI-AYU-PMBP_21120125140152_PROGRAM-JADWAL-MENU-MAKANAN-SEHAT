<?php
// classes/SimpleQueue.php

class SimpleQueue {
    private array $items = [];

    public function enqueue($item): void {
        $this->items[] = $item;
    }

    public function dequeue() {
        return array_shift($this->items);
    }

    public function peek() {
        return $this->items[0] ?? null;
    }

    public function isEmpty(): bool {
        return empty($this->items);
    }

    public function size(): int {
        return count($this->items);
    }

    public function toArray(): array {
        return $this->items;
    }
}