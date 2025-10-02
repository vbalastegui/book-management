<?php

namespace BookManagement\Domain\Criteria;

enum FilterOperator: string {
    case EQUAL = 'EQUAL';
    case NOT_EQUAL = 'NOT_EQUAL';
    case GREATER_THAN = 'GREATER_THAN';
    case GREATER_THAN_OR_EQUAL = 'GREATER_THAN_OR_EQUAL';
    case LESS_THAN = 'LESS_THAN';
    case LESS_THAN_OR_EQUAL = 'LESS_THAN_OR_EQUAL';
    case CONTAINS = 'CONTAINS';
    case NOT_CONTAINS = 'NOT_CONTAINS';
    case IN = 'IN';
    case NOT_IN = 'NOT_IN';
}

