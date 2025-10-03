<?php

namespace BookManagement\Domain;

use BookManagement\Domain\Exception\InvalidISBNException;

class BookIsbn {
    private string $value;

    public function __construct(string $value) {
        if (empty($value)) {
            throw new InvalidISBNException("ISBN cannot be empty.");
        }
        $this->value = $value;
    }

    public function value(): string {
        return $this->value;
    }

    public function equals(BookIsbn $other): bool {
        return $this->value === $other->value();
    }

    public function __toString(): string {
        return $this->value;
    }
}

