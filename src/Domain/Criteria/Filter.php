<?php

namespace BookManagement\Domain\Criteria;

class Filter {
    private string $field;
    private FilterOperator $operator;
    private mixed $value;

    public function __construct(string $field, FilterOperator $operator, mixed $value) {
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function field(): string {
        return $this->field;
    }

    public function operator(): FilterOperator {
        return $this->operator;
    }

    public function value(): mixed {
        return $this->value;
    }
}

