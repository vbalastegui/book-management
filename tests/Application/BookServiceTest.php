<?php

namespace BookManagement\Tests\Application;

use BookManagement\Application\BookService;
use BookManagement\Application\BookApiServiceInterface;
use BookManagement\Domain\Book;
use BookManagement\Domain\BookIsbn;
use BookManagement\Domain\BookRepositoryInterface;
use BookManagement\Domain\Criteria\Criteria;
use BookManagement\Domain\Criteria\Filter;
use BookManagement\Domain\Criteria\FilterOperator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class BookServiceTest extends TestCase {
    private MockObject $bookRepositoryMock;
    private MockObject $apiServiceMock;
    private MockObject $loggerMock;
    private BookService $bookService;

    protected function setUp(): void {
        $this->bookRepositoryMock = $this->createMock(BookRepositoryInterface::class);
        $this->apiServiceMock = $this->createMock(BookApiServiceInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->bookService = new BookService(
            $this->bookRepositoryMock,
            $this->apiServiceMock,
            $this->loggerMock
        );
    }

    /** @test */
    public function creating_a_book_validates_isbn(): void {
        // Prepare book data with valid ISBN
        $bookData = [
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'isbn' => '0132350882',
            'publication_year' => 2008
        ];

        // Mock API service to return details
        $this->apiServiceMock
            ->expects($this->once())
            ->method('fetchBookDetails')
            ->with('0132350882')
            ->willReturn(['description' => 'A handbook of software craftsmanship']);

        // Mock repository to capture the created book
        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function(Book $book) {
                // Verify that ISBN is converted to BookIsbn
                $this->assertInstanceOf(BookIsbn::class, $book->getIsbn());
                $this->assertEquals('0132350882', $book->getIsbn()->value());
                return true;
            }))
            ->willReturnCallback(function(Book $book) {
                // Simulate repository assigning an ID
                return new Book(
                    1,
                    $book->getTitle(),
                    $book->getAuthor(),
                    $book->getIsbn(),
                    $book->getPublicationYear(),
                    $book->getDescription()
                );
            });

        // Create book
        $createdBook = $this->bookService->createBook($bookData);

        // Verify book creation
        $this->assertEquals('Clean Code', $createdBook->getTitle());
        $this->assertEquals('0132350882', $createdBook->getIsbn()->value());
    }

    /** @test */
    public function searching_books_applies_domain_filters(): void {
        // Prepare test books
        $books = [
            new Book(
                1,
                'Clean Code',
                'Robert C. Martin',
                new BookIsbn('0132350882'),
                2008,
                'Software craftsmanship guide'
            ),
            new Book(
                2,
                'Refactoring',
                'Martin Fowler',
                new BookIsbn('0201485672'),
                1999,
                'Improving the design of existing code'
            )
        ];

        // Mock repository to return books based on criteria
        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('findByCriteria')
            ->with($this->callback(function(Criteria $criteria) {
                // Verify filter is correctly constructed
                $this->assertTrue($criteria->hasFilters());
                $this->assertCount(1, $criteria->filters());
                
                $filter = $criteria->filters()[0];
                $this->assertEquals('title', $filter->field());
                $this->assertEquals(FilterOperator::CONTAINS, $filter->operator());
                $this->assertEquals('Clean', $filter->value());

                return true;
            }))
            ->willReturn([$books[0]]);

        // Search books by title
        $results = $this->bookService->searchBooksByTitle('Clean');

        // Verify results
        $this->assertCount(1, $results);
        $this->assertEquals('Clean Code', $results[0]->getTitle());
    }

    /** @test */
    public function finding_all_books_applies_domain_pagination(): void {
        // Prepare test books
        $books = [
            new Book(
                1,
                'Clean Code',
                'Robert C. Martin',
                new BookIsbn('0132350882'),
                2008
            ),
            new Book(
                2,
                'Refactoring',
                'Martin Fowler',
                new BookIsbn('0201485672'),
                1999
            )
        ];

        // Mock repository to return books based on criteria
        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('findByCriteria')
            ->with($this->callback(function(Criteria $criteria) {
                // Verify pagination parameters
                $this->assertEquals(5, $criteria->limit());
                $this->assertEquals(10, $criteria->offset());
                $this->assertNull($criteria->order());
                $this->assertFalse($criteria->hasFilters());

                return true;
            }))
            ->willReturn($books);

        // Find books with custom pagination
        $results = $this->bookService->findAllBooks(5, 10);

        // Verify results
        $this->assertCount(2, $results);
        $this->assertEquals('Clean Code', $results[0]->getTitle());
        $this->assertEquals('Refactoring', $results[1]->getTitle());
    }
}
