<?php

namespace BookManagement\Domain\Criteria;

class Order {
    private string $orderBy;
    private OrderType $orderType;

    public function __construct(string $orderBy, OrderType $orderType) {
        $this->orderBy = $orderBy;
        $this->orderType = $orderType;
    }

    public function orderBy(): string {
        return $this->orderBy;
    }

    public function orderType(): OrderType {
        return $this->orderType;
    }

    public function isAsc(): bool {
        return $this->orderType === OrderType::ASC;
    }

    public function isDesc(): bool {
        return $this->orderType === OrderType::DESC;
    }
}

