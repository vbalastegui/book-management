<?php

namespace BookManagement\Domain\Criteria;

enum FilterOperator: string {
    case EQUAL = '=';
    case NOT_EQUAL = '!=';
    case GREATER_THAN = '>';
    case GREATER_THAN_OR_EQUAL = '>=';
    case LESS_THAN = '<';
    case LESS_THAN_OR_EQUAL = '<=';
    case CONTAINS = 'LIKE';
    case NOT_CONTAINS = 'NOT LIKE';
    case IN = 'IN';
    case NOT_IN = 'NOT IN';
}

