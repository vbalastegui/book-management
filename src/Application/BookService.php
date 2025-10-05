<?php

namespace BookManagement\Application;

use BookManagement\Domain\Book;
use BookManagement\Domain\BookIsbn;
use BookManagement\Domain\BookRepositoryInterface;
use BookManagement\Domain\Criteria\Criteria;
use BookManagement\Domain\Criteria\Filter;
use BookManagement\Domain\Criteria\FilterOperator;
use BookManagement\Domain\Criteria\Order;
use BookManagement\Domain\Criteria\OrderType;
use Psr\Log\LoggerInterface;

class BookService {
    private BookRepositoryInterface $bookRepository;
    private BookApiServiceInterface $apiService;
    private LoggerInterface $logger;

    public function __construct(
        BookRepositoryInterface $bookRepository, 
        BookApiServiceInterface $apiService,
        LoggerInterface $logger
    ) {
        $this->bookRepository = $bookRepository;
        $this->apiService = $apiService;
        $this->logger = $logger;
    }

    public function createBook(array $bookData): Book {
        $isbn = new BookIsbn($bookData['isbn']);
        
        // Fetch additional details from Open Library API
        $apiDetails = $this->apiService->fetchBookDetails($isbn->value());

        $book = new Book(
            null,
            $bookData['title'],
            $bookData['author'],
            $isbn,
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
            isset($bookData['isbn']) ? new BookIsbn($bookData['isbn']) : $book->getIsbn(),
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

    /**
     * Search books with optional filtering, ordering, and pagination
     * 
     * @param array $filters Associative array of field => value filters
     * @param int|null $limit Maximum number of books to return
     * @param int|null $offset Number of books to skip
     * @param string|null $orderBy Field to order by (optional)
     * @param string|null $orderType Order direction (ASC/DESC, optional)
     * @return array List of books matching the search criteria
     */
    public function searchBooks(
        array $filters = [], 
        ?int $limit = 100, 
        ?int $offset = 0, 
        ?string $orderBy = null, 
        ?string $orderType = null
    ): array {
        // Convert input filters to Domain Filter objects
        $domainFilters = array_map(function($field, $value) {
            // Handle more complex filter scenarios
            if (is_array($value)) {
                // If value is an array, assume it's a comparison
                $operator = key($value);
                $filterValue = current($value);
                
                // Map comparison operators
                $operatorMap = [
                    '=' => FilterOperator::EQUAL,
                    '>' => FilterOperator::GREATER_THAN,
                    '<' => FilterOperator::LESS_THAN,
                    '>=' => FilterOperator::GREATER_THAN_OR_EQUAL,
                    '<=' => FilterOperator::LESS_THAN_OR_EQUAL,
                    'LIKE' => FilterOperator::CONTAINS
                ];
                
                $mappedOperator = $operatorMap[$operator] ?? FilterOperator::CONTAINS;
                return new Filter($field, $mappedOperator, $filterValue);
            }
            
            // Default to CONTAINS for string fields, EQUAL for others
            $operator = is_string($value) 
                ? FilterOperator::CONTAINS 
                : FilterOperator::EQUAL;
            
            return new Filter($field, $operator, $value);
        }, array_keys($filters), $filters);

        // Create order if specified
        $order = null;
        if ($orderBy !== null) {
            $orderType = $orderType ?? 'ASC';
            $order = new Order($orderBy, OrderType::from(strtoupper($orderType)));
        }

        // Create criteria with filters, optional order, and pagination
        $criteria = new Criteria($domainFilters, $order, $limit, $offset);

        // Perform search
        return $this->bookRepository->findByCriteria($criteria);
    }
}
