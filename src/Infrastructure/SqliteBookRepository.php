<?php

namespace BookManagement\Infrastructure;

use BookManagement\Domain\Book;
use BookManagement\Domain\BookRepositoryInterface;
use BookManagement\Domain\Criteria\Criteria;
use BookManagement\Infrastructure\Persistence\OperatorStrategy;
use BookManagement\Infrastructure\Persistence\Sql\SqlQueryBuilder;
use BookManagement\Infrastructure\Persistence\Sql\Strategies\SqlEqualStrategy;
use BookManagement\Infrastructure\Persistence\Sql\Strategies\SqlContainsStrategy;
use BookManagement\Infrastructure\Persistence\Sql\Strategies\SqlGreaterThanStrategy;
use BookManagement\Infrastructure\Persistence\Sql\Strategies\SqlLessThanStrategy;
use PDO;
use PDOException;

class SqliteBookRepository implements BookRepositoryInterface {
    private PDO $connection;
    private array $strategies;

    public function __construct(string $dbPath = '/var/www/html/data/books.sqlite') {
        $this->initializeStrategies();
        try {
            $this->connection = new PDO("sqlite:$dbPath");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->createTable();
        } catch (PDOException $e) {
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    private function createTable(): void {
        $query = "CREATE TABLE IF NOT EXISTS books (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            author TEXT NOT NULL,
            isbn TEXT UNIQUE NOT NULL,
            publication_year INTEGER NOT NULL,
            description TEXT
        )";
        $this->connection->exec($query);
    }

    private function initializeStrategies(): void {
        $this->strategies = [
            new SqlEqualStrategy(),
            new SqlContainsStrategy(),
            new SqlGreaterThanStrategy(),
            new SqlLessThanStrategy(),
        ];
    }

    public function findById(int $id): ?Book {
        $stmt = $this->connection->prepare("SELECT * FROM books WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? $this->mapToBook($data) : null;
    }

    public function findByCriteria(Criteria $criteria): array {
        $queryBuilder = new SqlQueryBuilder("SELECT * FROM books");
        
        foreach ($criteria->filters() as $filter) {
            $this->applyFilter($filter, $queryBuilder);
        }
        
        if ($criteria->order()) {
            $queryBuilder->setOrder(
                $criteria->order()->orderBy(),
                $criteria->order()->orderType()->value
            );
        }
        
        if ($criteria->limit()) {
            $queryBuilder->setLimit($criteria->limit());
        }
        
        if ($criteria->offset()) {
            $queryBuilder->setOffset($criteria->offset());
        }
        
        $result = $queryBuilder->build();
        $stmt = $this->connection->prepare($result['sql']);
        $stmt->execute($result['params']);
        
        return array_map([$this, 'mapToBook'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function applyFilter($filter, $queryBuilder): void {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($filter->operator())) {
                $strategy->apply($filter, $queryBuilder);
                return;
            }
        }
        
        throw new \RuntimeException("No strategy found for operator: {$filter->operator()->value}");
    }

    public function create(Book $book): Book {
        $stmt = $this->connection->prepare(
            "INSERT INTO books (title, author, isbn, publication_year, description) 
             VALUES (:title, :author, :isbn, :publication_year, :description)"
        );
        
        $stmt->execute([
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'isbn' => $book->getIsbn(),
            'publication_year' => $book->getPublicationYear(),
            'description' => $book->getDescription()
        ]);

        $id = (int)$this->connection->lastInsertId();
        return new Book(
            $id,
            $book->getTitle(),
            $book->getAuthor(),
            $book->getIsbn(),
            $book->getPublicationYear(),
            $book->getDescription()
        );
    }

    public function update(Book $book): Book {
        $stmt = $this->connection->prepare(
            "UPDATE books SET 
             title = :title, 
             author = :author, 
             isbn = :isbn, 
             publication_year = :publication_year, 
             description = :description 
             WHERE id = :id"
        );
        
        $stmt->execute([
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'isbn' => $book->getIsbn(),
            'publication_year' => $book->getPublicationYear(),
            'description' => $book->getDescription()
        ]);

        return $book;
    }

    public function delete(int $id): bool {
        $stmt = $this->connection->prepare("DELETE FROM books WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    private function mapToBook(array $data): Book {
        return new Book(
            (int)$data['id'],
            $data['title'],
            $data['author'],
            $data['isbn'],
            (int)$data['publication_year'],
            $data['description'] ?? null
        );
    }
}
