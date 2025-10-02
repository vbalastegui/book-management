<?php

namespace BookManagement\Infrastructure\Persistence\Sql;

use BookManagement\Infrastructure\Persistence\QueryBuilder;

class SqlQueryBuilder implements QueryBuilder {
    private array $conditions = [];
    private array $params = [];
    private ?string $orderClause = null;
    private ?int $limit = null;
    private ?int $offset = null;
    private string $baseQuery;

    public function __construct(string $baseQuery) {
        $this->baseQuery = $baseQuery;
    }

    public function addCondition(string $field, string $operator, mixed $value): void {
        $paramName = $field . '_' . count($this->params);
        $this->conditions[] = "$field $operator :$paramName";
        $this->params[$paramName] = $value;
    }

    public function setOrder(string $field, string $direction): void {
        $this->orderClause = "$field $direction";
    }

    public function setLimit(int $limit): void {
        $this->limit = $limit;
    }

    public function setOffset(int $offset): void {
        $this->offset = $offset;
    }

    public function build(): array {
        $query = $this->baseQuery;

        if (!empty($this->conditions)) {
            $query .= " WHERE " . implode(" AND ", $this->conditions);
        }

        if ($this->orderClause) {
            $query .= " ORDER BY " . $this->orderClause;
        }

        if ($this->limit !== null) {
            $query .= " LIMIT " . $this->limit;
        }

        if ($this->offset !== null) {
            $query .= " OFFSET " . $this->offset;
        }

        return [
            'sql' => $query,
            'params' => $this->params
        ];
    }
}

