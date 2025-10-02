<?php

namespace BookManagement\Domain\Criteria;

class Criteria {
    private array $filters;
    private ?Order $order;
    private ?int $limit;
    private ?int $offset;

    public function __construct(
        array $filters = [],
        ?Order $order = null,
        ?int $limit = null,
        ?int $offset = null
    ) {
        $this->filters = $filters;
        $this->order = $order;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function hasFilters(): bool {
        return count($this->filters) > 0;
    }

    public function filters(): array {
        return $this->filters;
    }

    public function order(): ?Order {
        return $this->order;
    }

    public function limit(): ?int {
        return $this->limit;
    }

    public function offset(): ?int {
        return $this->offset;
    }
}

