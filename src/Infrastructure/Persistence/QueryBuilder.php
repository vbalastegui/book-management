<?php

namespace BookManagement\Infrastructure\Persistence;

interface QueryBuilder {
    public function addCondition(string $field, string $operator, mixed $value): void;
    public function setOrder(string $field, string $direction): void;
    public function setLimit(int $limit): void;
    public function setOffset(int $offset): void;
    public function build(): mixed;
}

