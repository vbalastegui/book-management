<?php

namespace BookManagement\Application;

use BookManagement\Domain\Book;
use BookManagement\Domain\BookRepositoryInterface;
use BookManagement\Domain\Criteria\Criteria;
use BookManagement\Domain\Criteria\Filter;
use BookManagement\Domain\Criteria\FilterOperator;
use BookManagement\Infrastructure\OpenLibraryApiService;
use Psr\Log\LoggerInterface;

class BookService {
    private BookRepositoryInterface $bookRepository;
    private OpenLibraryApiService $apiService;
    private LoggerInterface $logger;

    public function __construct(
        BookRepositoryInterface $bookRepository, 
        OpenLibraryApiService $apiService,
        LoggerInterface $logger
    ) {
        $this->bookRepository = $bookRepository;
        $this->apiService = $apiService;
        $this->logger = $logger;
    }

    public function createBook(array $bookData): Book {
        // Fetch additional details from Open Library API
        $apiDetails = $this->apiService->fetchBookDetails($bookData['isbn']);

        $book = new Book(
            null,
            $bookData['title'],
            $bookData['author'],
            $bookData['isbn'],
            $bookData['publication_year'],
            $apiDetails['description'] ?? null
        );

        $this->logger->info("Creating book: {$book->getTitle()}");
        return $this->bookRepository->create($book);
    }

    public function updateBook(int $id, array $bookData): Book {
        $book = $this->bookRepository->findById($id);
        
        if (!$book) {
            throw new \RuntimeException("Book not found");
        }

        // Update book details
        $updatedBook = new Book(
            $id,
            $bookData['title'] ?? $book->getTitle(),
            $bookData['author'] ?? $book->getAuthor(),
            $bookData['isbn'] ?? $book->getIsbn(),
            $bookData['publication_year'] ?? $book->getPublicationYear(),
            $bookData['description'] ?? $book->getDescription()
        );

        $this->logger->info("Updating book: {$updatedBook->getTitle()}");
        return $this->bookRepository->update($updatedBook);
    }

    public function deleteBook(int $id): bool {
        $this->logger->info("Deleting book with ID: $id");
        return $this->bookRepository->delete($id);
    }

    public function findBookById(int $id): ?Book {
        return $this->bookRepository->findById($id);
    }

    public function findAllBooks(?int $limit = 100, ?int $offset = 0): array {
        $criteria = new Criteria([], null, $limit, $offset);
        return $this->bookRepository->findByCriteria($criteria);
    }

    public function searchBooksByTitle(string $title): array {
        $criteria = new Criteria([
            new Filter('title', FilterOperator::CONTAINS, $title)
        ]);
        return $this->bookRepository->findByCriteria($criteria);
    }

    public function searchBooksByAuthor(string $author): array {
        $criteria = new Criteria([
            new Filter('author', FilterOperator::CONTAINS, $author)
        ]);
        return $this->bookRepository->findByCriteria($criteria);
    }
}
