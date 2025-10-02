<?php

namespace BookManagement\Domain;

use BookManagement\Domain\Criteria\Criteria;

interface BookRepositoryInterface {
    public function findById(int $id): ?Book;
    public function findByCriteria(Criteria $criteria): array;
    public function create(Book $book): Book;
    public function update(Book $book): Book;
    public function delete(int $id): bool;
}
