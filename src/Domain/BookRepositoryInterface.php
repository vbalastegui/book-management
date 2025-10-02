<?php

namespace BookManagement\Domain;

interface BookRepositoryInterface {
    public function findById(int $id): ?Book;
    public function findAll(): array;
    public function findByTitle(string $title): array;
    public function findByAuthor(string $author): array;
    public function create(Book $book): Book;
    public function update(Book $book): Book;
    public function delete(int $id): bool;
}
