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
    public function it_can_create_a_book(): void {
        $bookData = [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbn' => '1234567890',
            'publication_year' => 2023
        ];

        $apiDetails = ['description' => 'Test description'];
        $this->apiServiceMock
            ->expects($this->once())
            ->method('fetchBookDetails')
            ->with('1234567890')
            ->willReturn($apiDetails);

        $expectedBook = new Book(
            null,
            'Test Book',
            'Test Author',
            new BookIsbn('1234567890'),
            2023,
            'Test description'
        );

        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function($book) use ($expectedBook) {
                $this->assertEquals($expectedBook->getTitle(), $book->getTitle());
                $this->assertEquals($expectedBook->getAuthor(), $book->getAuthor());
                $this->assertEquals($expectedBook->getIsbn()->value(), $book->getIsbn()->value());
                $this->assertEquals($expectedBook->getPublicationYear(), $book->getPublicationYear());
                $this->assertEquals($expectedBook->getDescription(), $book->getDescription());
                return true;
            }))
            ->willReturn($expectedBook);

        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with('Creating book: Test Book');

        $result = $this->bookService->createBook($bookData);
        $this->assertEquals($expectedBook, $result);
    }

    /** @test */
    public function it_can_create_a_book_without_description(): void {
        $bookData = [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbn' => '1234567890',
            'publication_year' => 2023
        ];

        $this->apiServiceMock
            ->expects($this->once())
            ->method('fetchBookDetails')
            ->with('1234567890')
            ->willReturn(null);

        $expectedBook = new Book(
            null,
            'Test Book',
            'Test Author',
            new BookIsbn('1234567890'),
            2023
        );

        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($expectedBook);

        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with('Creating book: Test Book');

        $result = $this->bookService->createBook($bookData);
        $this->assertEquals($expectedBook, $result);
    }

    /** @test */
    public function it_can_update_a_book(): void {
        $existingBook = new Book(
            1,
            'Old Title',
            'Old Author',
            new BookIsbn('0987654321'),
            2022
        );

        $updateData = [
            'title' => 'New Title',
            'author' => 'New Author',
            'isbn' => '1234567890',
            'publication_year' => 2023,
            'description' => 'New description'
        ];

        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($existingBook);

        $expectedUpdatedBook = new Book(
            1,
            'New Title',
            'New Author',
            new BookIsbn('1234567890'),
            2023,
            'New description'
        );

        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('update')
            ->with($this->equalTo($expectedUpdatedBook))
            ->willReturn($expectedUpdatedBook);

        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with('Updating book: New Title');

        $result = $this->bookService->updateBook(1, $updateData);
        $this->assertEquals($expectedUpdatedBook, $result);
    }

    /** @test */
    public function it_throws_exception_when_updating_non_existent_book(): void {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Book not found');

        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->bookService->updateBook(999, []);
    }

    /** @test */
    public function it_can_delete_a_book(): void {
        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        $this->loggerMock
            ->expects($this->once())
            ->method('info')
            ->with('Deleting book with ID: 1');

        $result = $this->bookService->deleteBook(1);
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_find_book_by_id(): void {
        $expectedBook = new Book(
            1,
            'Test Book',
            'Test Author',
            new BookIsbn('1234567890'),
            2023
        );

        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($expectedBook);

        $result = $this->bookService->findBookById(1);
        $this->assertEquals($expectedBook, $result);
    }

    /** @test */
    public function it_can_find_all_books(): void {
        $expectedBooks = [
            new Book(
                1,
                'Book 1',
                'Author 1',
                new BookIsbn('1111111111'),
                2021
            ),
            new Book(
                2,
                'Book 2',
                'Author 2',
                new BookIsbn('2222222222'),
                2022
            )
        ];

        $expectedCriteria = new Criteria([], null, 100, 0);

        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('findByCriteria')
            ->with($this->equalTo($expectedCriteria))
            ->willReturn($expectedBooks);

        $result = $this->bookService->findAllBooks();
        $this->assertEquals($expectedBooks, $result);
    }

    /** @test */
    public function it_can_search_books_by_title(): void {
        $expectedBooks = [
            new Book(
                1,
                'Clean Code',
                'Robert C. Martin',
                new BookIsbn('0132350882'),
                2008
            )
        ];

        $expectedCriteria = new Criteria([
            new Filter('title', FilterOperator::CONTAINS, 'Clean')
        ]);

        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('findByCriteria')
            ->with($this->equalTo($expectedCriteria))
            ->willReturn($expectedBooks);

        $result = $this->bookService->searchBooksByTitle('Clean');
        $this->assertEquals($expectedBooks, $result);
    }

    /** @test */
    public function it_can_search_books_by_author(): void {
        $expectedBooks = [
            new Book(
                1,
                'Clean Code',
                'Robert C. Martin',
                new BookIsbn('0132350882'),
                2008
            )
        ];

        $expectedCriteria = new Criteria([
            new Filter('author', FilterOperator::CONTAINS, 'Martin')
        ]);

        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('findByCriteria')
            ->with($this->equalTo($expectedCriteria))
            ->willReturn($expectedBooks);

        $result = $this->bookService->searchBooksByAuthor('Martin');
        $this->assertEquals($expectedBooks, $result);
    }
}
