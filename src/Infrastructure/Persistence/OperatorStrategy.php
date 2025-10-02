<?php

namespace BookManagement\Infrastructure\Persistence;

use BookManagement\Domain\Criteria\Filter;
use BookManagement\Domain\Criteria\FilterOperator;

interface OperatorStrategy {
    public function supports(FilterOperator $operator): bool;
    public function apply(Filter $filter, QueryBuilder $queryBuilder): void;
}

