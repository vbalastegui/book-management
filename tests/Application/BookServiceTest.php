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
use BookManagement\Domain\Criteria\Order;
use BookManagement\Domain\Criteria\OrderType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use BookManagement\Domain\Exception\InvalidISBNException;

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
    public function creating_a_book_uses_isbn_correctly(): void {
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
                // Only verify that the ISBN is used correctly in the Book creation
                // Without directly testing BookIsbn's internal methods
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
    public function creating_a_book_with_empty_isbn_fails(): void {
        // Prepare book data with empty ISBN
        $bookData = [
            'title' => 'Invalid Book',
            'author' => 'Test Author',
            'isbn' => '',
            'publication_year' => 2023
        ];

        // Mock API service
        $this->apiServiceMock
            ->expects($this->never())
            ->method('fetchBookDetails');

        // Mock repository
        $this->bookRepositoryMock
            ->expects($this->never())
            ->method('create');

        // Expect InvalidISBNException from BookService
        $this->expectException(InvalidISBNException::class);
        $this->expectExceptionMessage('ISBN cannot be empty.');

        // Attempt to create book
        $this->bookService->createBook($bookData);
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
        $results = $this->bookService->searchBooks(['title' => 'Clean']);

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
        $results = $this->bookService->searchBooks(
            limit: 5, 
            offset: 10
        );

        // Verify results
        $this->assertCount(2, $results);
        $this->assertEquals('Clean Code', $results[0]->getTitle());
        $this->assertEquals('Refactoring', $results[1]->getTitle());
    }

    /** @test */
    public function order_type_methods_work_correctly(): void {
        // Test ASC order
        $ascOrder = new Order('title', OrderType::ASC);
        $this->assertTrue($ascOrder->isAsc());
        $this->assertFalse($ascOrder->isDesc());

        // Test DESC order
        $descOrder = new Order('publication_year', OrderType::DESC);
        $this->assertTrue($descOrder->isDesc());
        $this->assertFalse($descOrder->isAsc());
    }

    /** @test */
    public function book_isbn_validation_and_conversion(): void {
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
                // Verify ISBN object properties
                $isbn = $book->getIsbn();
                $this->assertInstanceOf(BookIsbn::class, $isbn);
                $this->assertEquals('0132350882', $isbn->value());
                $this->assertEquals('0132350882', (string)$isbn);

                // Test ISBN equality
                $sameIsbn = new BookIsbn('0132350882');
                $differentIsbn = new BookIsbn('1234567890');
                $this->assertTrue($isbn->equals($sameIsbn));
                $this->assertFalse($isbn->equals($differentIsbn));

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
    public function book_isbn_empty_validation(): void {
        // Prepare book data with empty ISBN
        $bookData = [
            'title' => 'Invalid Book',
            'author' => 'Test Author',
            'isbn' => '',
            'publication_year' => 2023
        ];

        // Mock API service
        $this->apiServiceMock
            ->expects($this->never())
            ->method('fetchBookDetails');

        // Mock repository
        $this->bookRepositoryMock
            ->expects($this->never())
            ->method('create');

        // Expect InvalidISBNException
        $this->expectException(InvalidISBNException::class);
        $this->expectExceptionMessage('ISBN cannot be empty.');

        // Attempt to create book
        $this->bookService->createBook($bookData);
    }

    /** @test */
    public function finding_books_with_order_works_correctly(): void {
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
                // Verify order is correctly applied
                $this->assertNotNull($criteria->order());
                $order = $criteria->order();
                
                $this->assertEquals('publication_year', $order->orderBy());
                $this->assertEquals(OrderType::DESC, $order->orderType());

                return true;
            }))
            ->willReturn($books);

        // Find books with order
        $results = $this->bookService->searchBooks(
            limit: 10, 
            offset: 0, 
            orderBy: 'publication_year', 
            orderType: 'DESC'
        );

        // Verify results
        $this->assertCount(2, $results);
    }

    /** @test */
    public function flexible_search_supports_multiple_filters(): void {
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
                // Verify filters are correctly constructed
                $this->assertCount(2, $criteria->filters());
                
                // Check first filter (title)
                $titleFilter = $criteria->filters()[0];
                $this->assertEquals('title', $titleFilter->field());
                $this->assertEquals(FilterOperator::CONTAINS, $titleFilter->operator());
                $this->assertEquals('Clean', $titleFilter->value());

                // Check second filter (publication year)
                $yearFilter = $criteria->filters()[1];
                $this->assertEquals('publication_year', $yearFilter->field());
                $this->assertEquals(FilterOperator::EQUAL, $yearFilter->operator());
                $this->assertEquals(2008, $yearFilter->value());

                // Verify order
                $this->assertNotNull($criteria->order());
                $order = $criteria->order();
                $this->assertEquals('title', $order->orderBy());
                $this->assertEquals(OrderType::DESC, $order->orderType());

                return true;
            }))
            ->willReturn($books);

        // Perform flexible search
        $results = $this->bookService->searchBooks(
            [
                'title' => 'Clean', 
                'publication_year' => 2008
            ], 
            orderBy: 'title', 
            orderType: 'DESC'
        );

        // Verify results
        $this->assertCount(2, $results);
    }

    /** @test */
    public function flexible_search_supports_single_filter(): void {
        // Prepare test books
        $books = [
            new Book(
                1,
                'Clean Code',
                'Robert C. Martin',
                new BookIsbn('0132350882'),
                2008,
                'Software craftsmanship guide'
            )
        ];

        // Mock repository to return books based on criteria
        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('findByCriteria')
            ->with($this->callback(function(Criteria $criteria) {
                // Verify filters are correctly constructed
                $this->assertCount(1, $criteria->filters());
                
                // Check author filter
                $authorFilter = $criteria->filters()[0];
                $this->assertEquals('author', $authorFilter->field());
                $this->assertEquals(FilterOperator::CONTAINS, $authorFilter->operator());
                $this->assertEquals('Martin', $authorFilter->value());

                // Verify no order
                $this->assertNull($criteria->order());

                return true;
            }))
            ->willReturn($books);

        // Perform flexible search with single filter
        $results = $this->bookService->searchBooks(['author' => 'Martin']);

        // Verify results
        $this->assertCount(1, $results);
    }

    /** @test */
    public function flexible_search_supports_new_fields_without_method_changes(): void {
        // Prepare test books
        $books = [
            new Book(
                1,
                'Clean Code',
                'Robert C. Martin',
                new BookIsbn('0132350882'),
                2008,
                'Software craftsmanship guide'
            )
        ];

        // Mock repository to return books based on criteria
        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('findByCriteria')
            ->with($this->callback(function(Criteria $criteria) {
                // Verify filters are correctly constructed
                $this->assertCount(2, $criteria->filters());
                
                // Check description filter
                $descriptionFilter = $criteria->filters()[1];
                $this->assertEquals('description', $descriptionFilter->field());
                $this->assertEquals(FilterOperator::CONTAINS, $descriptionFilter->operator());
                $this->assertEquals('craftsmanship', $descriptionFilter->value());

                return true;
            }))
            ->willReturn($books);

        // Perform flexible search with a new field that didn't exist before
        $results = $this->bookService->searchBooks([
            'author' => 'Martin', 
            'description' => 'craftsmanship'
        ]);

        // Verify results
        $this->assertCount(1, $results);
    }

    /** @test */
    public function creating_book_with_api_details_works_correctly(): void {
        // Prepare book data
        $bookData = [
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'isbn' => '0132350882',
            'publication_year' => 2008
        ];

        // Mock API service to return book details
        $this->apiServiceMock
            ->expects($this->once())
            ->method('fetchBookDetails')
            ->with('0132350882')
            ->willReturn([
                'description' => 'A handbook of software craftsmanship'
            ]);

        // Mock repository to capture created book
        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function(Book $book) {
                $this->assertEquals('Clean Code', $book->getTitle());
                $this->assertEquals('Robert C. Martin', $book->getAuthor());
                $this->assertEquals('0132350882', $book->getIsbn()->value());
                $this->assertEquals(2008, $book->getPublicationYear());
                $this->assertEquals('A handbook of software craftsmanship', $book->getDescription());
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

        // Mock logger to verify logging
        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with('Creating book: Clean Code');

        // Create book
        $createdBook = $this->bookService->createBook($bookData);

        // Verify book creation
        $this->assertEquals(1, $createdBook->getId());
        $this->assertEquals('Clean Code', $createdBook->getTitle());
    }

    /** @test */
    public function updating_book_works_correctly(): void {
        // Prepare existing book
        $existingBook = new Book(
            1,
            'Old Title',
            'Old Author',
            new BookIsbn('1234567890'),
            2000
        );

        // Prepare update data
        $updateData = [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'isbn' => '0987654321',
            'publication_year' => 2022
        ];

        // Mock repository to find existing book
        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($existingBook);

        // Mock repository to update book
        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('update')
            ->with($this->callback(function(Book $book) {
                $this->assertEquals(1, $book->getId());
                $this->assertEquals('Updated Title', $book->getTitle());
                $this->assertEquals('Updated Author', $book->getAuthor());
                $this->assertEquals('0987654321', $book->getIsbn()->value());
                $this->assertEquals(2022, $book->getPublicationYear());
                return true;
            }))
            ->willReturnCallback(function(Book $book) {
                return $book;
            });

        // Mock logger to verify logging
        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with('Updating book: Updated Title');

        // Update book
        $updatedBook = $this->bookService->updateBook(1, $updateData);

        // Verify book update
        $this->assertEquals('Updated Title', $updatedBook->getTitle());
        $this->assertEquals('Updated Author', $updatedBook->getAuthor());
    }

    /** @test */
    public function updating_book_with_partial_data_works_correctly(): void {
        // Prepare existing book
        $existingBook = new Book(
            1,
            'Old Title',
            'Old Author',
            new BookIsbn('1234567890'),
            2000
        );

        // Prepare partial update data
        $updateData = [
            'title' => 'Updated Title'
        ];

        // Mock repository to find existing book
        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($existingBook);

        // Mock repository to update book
        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('update')
            ->with($this->callback(function(Book $book) {
                $this->assertEquals(1, $book->getId());
                $this->assertEquals('Updated Title', $book->getTitle());
                $this->assertEquals('Old Author', $book->getAuthor());
                $this->assertEquals('1234567890', $book->getIsbn()->value());
                $this->assertEquals(2000, $book->getPublicationYear());
                return true;
            }))
            ->willReturnCallback(function(Book $book) {
                return $book;
            });

        // Update book
        $updatedBook = $this->bookService->updateBook(1, $updateData);

        // Verify book update
        $this->assertEquals('Updated Title', $updatedBook->getTitle());
        $this->assertEquals('Old Author', $updatedBook->getAuthor());
    }

    /** @test */
    public function deleting_book_works_correctly(): void {
        // Mock repository to delete book
        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        // Mock logger to verify logging
        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with('Deleting book with ID: 1');

        // Delete book
        $result = $this->bookService->deleteBook(1);

        // Verify deletion
        $this->assertTrue($result);
    }

    /** @test */
    public function finding_book_by_id_works_correctly(): void {
        // Prepare book
        $book = new Book(
            1,
            'Clean Code',
            'Robert C. Martin',
            new BookIsbn('0132350882'),
            2008
        );

        // Mock repository to find book
        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($book);

        // Find book
        $foundBook = $this->bookService->findBookById(1);

        // Verify found book
        $this->assertNotNull($foundBook);
        $this->assertEquals('Clean Code', $foundBook->getTitle());
    }
}
