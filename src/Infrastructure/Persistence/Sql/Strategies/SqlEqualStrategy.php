<?php

namespace BookManagement\Infrastructure\Persistence\Sql\Strategies;

use BookManagement\Domain\Criteria\Filter;
use BookManagement\Domain\Criteria\FilterOperator;
use BookManagement\Infrastructure\Persistence\OperatorStrategy;
use BookManagement\Infrastructure\Persistence\QueryBuilder;

class SqlEqualStrategy implements OperatorStrategy {
    public function supports(FilterOperator $operator): bool {
        return $operator === FilterOperator::EQUAL;
    }

    public function apply(Filter $filter, QueryBuilder $queryBuilder): void {
        $queryBuilder->addCondition($filter->field(), '=', $filter->value());
    }
}

