<?php

namespace BookManagement\Tests\Acceptance;

use PHPUnit\Framework\TestCase;
use BookManagement\Application\BookService;
use BookManagement\Application\BookApiServiceInterface;
use BookManagement\Infrastructure\SqliteBookRepository;
use BookManagement\Infrastructure\OpenLibraryApiService;
use Monolog\Logger;
use Monolog\Handler\NullHandler;

/**
 * Acceptance Test: Book Management
 * 
 * These tests validate complete use cases from the user's perspective
 */
class BookManagementTest extends TestCase {
    private BookService $bookService;
    private string $testDbPath;

    protected function setUp(): void {
        $this->testDbPath = '/tmp/test_books_' . uniqid() . '.sqlite';
        
        $repository = new SqliteBookRepository($this->testDbPath);
        $apiService = new OpenLibraryApiService();
        $logger = new Logger('Test');
        $logger->pushHandler(new NullHandler());
        $this->bookService = new BookService($repository, $apiService, $logger);
    }

    protected function tearDown(): void {
        if (file_exists($this->testDbPath)) {
            unlink($this->testDbPath);
        }
    }

    /** @test */
    public function a_user_can_create_a_book(): void {
        $bookData = [
            'title' => '1984',
            'author' => 'George Orwell',
            'isbn' => '0451524934',
            'publication_year' => 1949
        ];

        $book = $this->bookService->createBook($bookData);

        $this->assertNotNull($book->getId());
        $this->assertEquals('1984', $book->getTitle());
        $this->assertEquals('George Orwell', $book->getAuthor());
        $this->assertEquals('0451524934', $book->getIsbn());
        $this->assertEquals(1949, $book->getPublicationYear());
    }

    /** @test */
    public function a_user_can_list_all_books(): void {
        $this->bookService->createBook([
            'title' => '1984',
            'author' => 'George Orwell',
            'isbn' => '0451524934',
            'publication_year' => 1949
        ]);

        $this->bookService->createBook([
            'title' => 'Don Quijote',
            'author' => 'Miguel de Cervantes',
            'isbn' => '9788491051367',
            'publication_year' => 1605
        ]);

        $books = $this->bookService->findAllBooks();

        $this->assertCount(2, $books);
        $this->assertEquals('1984', $books[0]->getTitle());
        $this->assertEquals('Don Quijote', $books[1]->getTitle());
    }

    /** @test */
    public function a_user_can_find_a_book_by_id(): void {
        $createdBook = $this->bookService->createBook([
            'title' => 'The Great Gatsby',
            'author' => 'F. Scott Fitzgerald',
            'isbn' => '0743273567',
            'publication_year' => 1925
        ]);

        $foundBook = $this->bookService->findBookById($createdBook->getId());

        $this->assertNotNull($foundBook);
        $this->assertEquals($createdBook->getId(), $foundBook->getId());
        $this->assertEquals('The Great Gatsby', $foundBook->getTitle());
    }

    /** @test */
    public function a_user_can_search_books_by_title(): void {
        $this->bookService->createBook([
            'title' => 'Harry Potter and the Philosopher\'s Stone',
            'author' => 'J.K. Rowling',
            'isbn' => '0439708184',
            'publication_year' => 1997
        ]);

        $this->bookService->createBook([
            'title' => 'The Lord of the Rings',
            'author' => 'J.R.R. Tolkien',
            'isbn' => '0544003411',
            'publication_year' => 1954
        ]);

        $books = $this->bookService->searchBooksByTitle('Harry Potter');

        $this->assertCount(1, $books);
        $this->assertEquals('Harry Potter and the Philosopher\'s Stone', $books[0]->getTitle());
    }

    /** @test */
    public function a_user_can_search_books_by_author(): void {
        $this->bookService->createBook([
            'title' => '1984',
            'author' => 'George Orwell',
            'isbn' => '0451524934',
            'publication_year' => 1949
        ]);

        $this->bookService->createBook([
            'title' => 'Animal Farm',
            'author' => 'George Orwell',
            'isbn' => '0452284244',
            'publication_year' => 1945
        ]);

        $this->bookService->createBook([
            'title' => 'The Great Gatsby',
            'author' => 'F. Scott Fitzgerald',
            'isbn' => '0743273567',
            'publication_year' => 1925
        ]);

        $books = $this->bookService->searchBooksByAuthor('Orwell');

        $this->assertCount(2, $books);
        $this->assertEquals('George Orwell', $books[0]->getAuthor());
        $this->assertEquals('George Orwell', $books[1]->getAuthor());
    }

    /** @test */
    public function a_user_can_update_a_book(): void {
        $book = $this->bookService->createBook([
            'title' => 'Original Title',
            'author' => 'Original Author',
            'isbn' => '1234567890',
            'publication_year' => 2000
        ]);

        $updatedBook = $this->bookService->updateBook($book->getId(), [
            'title' => 'Updated Title',
            'publication_year' => 2001
        ]);

        $this->assertEquals('Updated Title', $updatedBook->getTitle());
        $this->assertEquals('Original Author', $updatedBook->getAuthor());
        $this->assertEquals(2001, $updatedBook->getPublicationYear());
    }

    /** @test */
    public function a_user_can_delete_a_book(): void {
        $book = $this->bookService->createBook([
            'title' => 'Book to Delete',
            'author' => 'Some Author',
            'isbn' => '9999999999',
            'publication_year' => 2020
        ]);

        $result = $this->bookService->deleteBook($book->getId());

        $this->assertTrue($result);
        $this->assertNull($this->bookService->findBookById($book->getId()));
    }

    /** @test */
    public function the_system_logs_book_creation(): void {
        $logFile = '/var/www/html/logs/bookservice.log';
        
        $this->bookService->createBook([
            'title' => 'Test Book for Logging',
            'author' => 'Test Author',
            'isbn' => '1111111111',
            'publication_year' => 2023
        ]);

        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            $this->assertStringContainsString('Creating book: Test Book for Logging', $logContent);
        } else {
            $this->markTestSkipped('Log file not accessible - run this test inside Docker');
        }
    }
}

